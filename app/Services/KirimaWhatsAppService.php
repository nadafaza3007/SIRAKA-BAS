<?php

namespace App\Services;

use App\Models\RekamServis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use App\Models\NotaWhatsappDelivery;

class KirimaWhatsAppService
{
    /**
     * Mengirim PDF tagihan awal setelah tagihan diterbitkan.
     */
    public function kirimEstimasi(RekamServis $servis): void
    {
        $servis->loadMissing([
            'kendaraan.konsumen',
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
            'nota.pembayaran.penerima',
        ]);

        $nomor = $servis->nota?->no_nota
            ?? 'EST-RS-' . str_pad(
                (string) $servis->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $caption = "Berikut rincian biaya servis kendaraan Anda.\n"
            . "Nomor nota: {$nomor}\n"
            . "Silakan periksa rincian tagihan pada PDF terlampir.\n"
            . "Terima kasih - Baba Auto Service.";

        $this->kirimPdf(
            $servis,
            $caption,
            'estimasi-servis'
        );
    }

    /**
     * Mengirim nota akhir hanya jika pembayaran lunas
     * DAN data akhir pekerjaan sudah lengkap.
     */
    public function kirimNotaAkhirJikaSiap(
        RekamServis $servis
    ): bool {

        $servis->loadMissing(['nota']);

        if (
            ! $servis->nota ||
            $servis->nota->status_pembayaran !== 'lunas' ||
            $servis->status !== 'selesai' ||
            $servis->km_keluar === null
        ) {
            return false;
        }

        $servis->loadMissing([
            'kendaraan.konsumen',
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
            'nota.pembayaran.penerima',
        ]);

        $nomor = $servis->nota->no_nota;

        $caption = "Pembayaran servis kendaraan Anda telah LUNAS.\n"
            . "Nomor nota: {$nomor}\n"
            . "Terlampir nota akhir sebagai bukti pembayaran.\n"
            . "Terima kasih telah mempercayakan kendaraan Anda "
            . "kepada Baba Auto Service.";

        $this->kirimPdf(
            $servis,
            $caption,
            'nota-lunas'
        );

        return true;
    }

    /**
     * Membuat PDF dan mengirimkannya melalui Kirima API.
     * Setiap percobaan pengiriman dicatat ke audit_logs.
     */
    private function kirimPdf(
    RekamServis $servis,
    string $caption,
    string $prefix
    ): void {

        $jenis = $prefix === 'nota-lunas'
            ? 'Nota akhir (lunas)'
            : 'Estimasi tagihan';

        $jenisRecord = $prefix === 'nota-lunas'
            ? 'nota_lunas'
            : 'tagihan_awal';

        $nomorNota = $servis->nota?->no_nota
            ?? 'EST-RS-' . str_pad(
                (string) $servis->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $phone = null;
        $delivery = null;

        try {

            $servis->loadMissing([
                'kendaraan.konsumen',
                'detailSpareparts.sparepart',
                'detailJasas.jasa',
                'nota.pembayaran.penerima',
            ]);

            $nota = $servis->nota;

            if (! $nota) {
                throw new RuntimeException(
                    'Data nota tidak ditemukan untuk mencatat pengiriman WhatsApp.'
                );
            }

            /*
            * Ambil atau buat catatan pengiriman.
            * Satu jenis pengiriman hanya mempunyai satu baris.
            */
            $delivery = DB::transaction(function () use (
                $nota,
                $jenisRecord
            ) {

                $record = NotaWhatsappDelivery::where(
                    'nota_transaksi_id',
                    $nota->id
                )
                    ->where('jenis', $jenisRecord)
                    ->lockForUpdate()
                    ->first();

                if (! $record) {
                    $record = NotaWhatsappDelivery::create([
                        'nota_transaksi_id' => $nota->id,
                        'jenis' => $jenisRecord,
                        'status' => 'pending',
                        'attempts' => 0,
                    ]);
                }

                // Hindari pengiriman ulang jika sudah berhasil.
                if ($record->status === 'sent') {
                    return $record;
                }

                $record->update([
                    'status' => 'sending',
                    'attempts' => (int) $record->attempts + 1,
                    'error_message' => null,
                ]);

                return $record;

            }, 3);

            if ($delivery->status === 'sent') {
                return;
            }

            $konsumen = $servis->kendaraan?->konsumen;

            if (! $konsumen || ! $konsumen->no_hp) {
                throw new RuntimeException(
                    'Nomor WhatsApp pelanggan tidak tersedia.'
                );
            }

            $baseUrl = rtrim(
                (string) config('services.kirima.base_url'),
                '/'
            );

            $clientId = config('services.kirima.client_id');
            $clientSecret = config('services.kirima.client_secret');
            $deviceJid = config('services.kirima.device_jid');

            if (
                ! $baseUrl ||
                ! $clientId ||
                ! $clientSecret ||
                ! $deviceJid
            ) {
                throw new RuntimeException(
                    'Konfigurasi Kirima belum lengkap. Periksa file .env.'
                );
            }

            $phone = $this->normalisasiNomor(
                $konsumen->no_hp
            );

            $nomorAman = preg_replace(
                '/[^A-Za-z0-9_-]/',
                '-',
                $nomorNota
            );

            $filename = $prefix . '-' . $nomorAman . '.pdf';

            // Menggunakan desain PDF yang sudah ada.
            $pdf = Pdf::loadView(
                'admin.rekam-servis.nota-pdf',
                compact('servis')
            )
                ->setPaper('a4', 'portrait')
                ->output();

            // Mengirim PDF melalui Kirima API.
            $response = Http::connectTimeout(10)
                ->timeout(45)
                ->withHeaders([
                    'X-Client-Id' => $clientId,
                    'X-Client-Secret' => $clientSecret,
                ])
                ->attach(
                    'file',
                    $pdf,
                    $filename,
                    ['Content-Type' => 'application/pdf']
                )
                ->post(
                    $baseUrl . '/api/v1/wa/media',
                    [
                        'device_jid' => $deviceJid,
                        'phone' => $phone,
                        'caption' => $caption,
                    ]
                );

            if (! $response->successful()) {
                throw new RuntimeException(
                    'Kirima gagal mengirim PDF. HTTP '
                    . $response->status()
                );
            }

            /*
            * Catat keberhasilan permintaan pengiriman.
            */
            $responseData = $response->json();

            $messageId = data_get($responseData, 'message_id')
                ?? data_get($responseData, 'data.message_id');

            $delivery->update([
                'status' => 'sent',
                'message_id' => $messageId,
                'error_message' => null,
                'sent_at' => now(),
            ]);

            // Pertahankan audit log yang sudah ada.
            $this->catatAuditWhatsApp(
                $servis,
                $jenis,
                $nomorNota,
                $phone,
                'berhasil',
                'API Kirima memberikan respons HTTP '
                    . $response->status()
                    . '. Permintaan pengiriman berhasil diterima.'
            );

        } catch (Throwable $e) {

            /*
            * Catat kegagalan ke tabel riwayat WhatsApp.
            */
            if ($delivery) {
                try {
                    $delivery->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_at' => null,
                    ]);
                } catch (Throwable $dbError) {
                    Log::error(
                        'Gagal memperbarui status pengiriman WhatsApp.',
                        [
                            'error' => $dbError->getMessage(),
                        ]
                    );
                }
            }

            // Pertahankan audit log yang sudah ada.
            $this->catatAuditWhatsApp(
                $servis,
                $jenis,
                $nomorNota,
                $phone,
                'gagal',
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Menyimpan aktivitas WhatsApp ke tabel audit_logs.
     * Menggunakan kolom yang tersedia pada database aktual.
     */
    private function catatAuditWhatsApp(
        RekamServis $servis,
        string $jenis,
        string $nomorNota,
        ?string $phone,
        string $status,
        string $keterangan
    ): void {

        try {

            $tujuan = $phone
                ? substr($phone, 0, 4)
                    . str_repeat('*', max(0, strlen($phone) - 7))
                    . substr($phone, -3)
                : 'Tidak tersedia';

            $aksi = sprintf(
                'Pengiriman WhatsApp PDF %s | Nota: %s | '
                . 'ID Servis: %s | Tujuan: %s | Status: %s | '
                . 'Keterangan: %s',
                $jenis,
                $nomorNota,
                $servis->id,
                $tujuan,
                strtoupper($status),
                $keterangan
            );

            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'entitas' => 'WhatsApp Kirima',
                'aksi' => $aksi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (Throwable $e) {

            // Kegagalan pencatatan tidak boleh membatalkan
            // proses pengiriman WhatsApp.
            Log::error(
                'Gagal mencatat aktivitas WhatsApp ke audit_logs.',
                [
                    'rekam_servis_id' => $servis->id,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Mengubah nomor Indonesia menjadi format 62xxxxxxxx.
     */
    private function normalisasiNomor(string $nomor): string
    {
        $nomor = preg_replace('/\D+/', '', $nomor);

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        } elseif (str_starts_with($nomor, '8')) {
            $nomor = '62' . $nomor;
        }

        if (
            ! str_starts_with($nomor, '62') ||
            strlen($nomor) < 10 ||
            strlen($nomor) > 15
        ) {
            throw new RuntimeException(
                'Format nomor WhatsApp pelanggan tidak valid.'
            );
        }

        return $nomor;
    }
}