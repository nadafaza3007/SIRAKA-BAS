<?php

namespace App\Services;

use App\Models\LaporanInspeksi;
use App\Models\InspeksiWhatsappDelivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class InspeksiWhatsAppService
{
    /**
     * Mengirim PDF laporan inspeksi melalui Kirima API.
     */
    public function kirimPdf(LaporanInspeksi $inspeksi): void
    {
        $delivery = null;

        try {

            // Ambil seluruh relasi yang diperlukan.
            $inspeksi->loadMissing([
                'konsumen',
                'kendaraan',
                'user',
                'kelistrikan',
                'interior',
                'kakiKaki',
                'exterior',
                'mesin',
                'dokumen',
                'estimasi.sparepart',
                'estimasi.jasa',
            ]);

            // Ambil nomor WhatsApp dari accessor model.
            $nomorAsli = $inspeksi->nomor_wa_pelanggan_inspeksi;

            if (!$nomorAsli) {
                throw new RuntimeException(
                    'Nomor WhatsApp pelanggan tidak tersedia.'
                );
            }

            $phone = $this->normalisasiNomor($nomorAsli);

            // Periksa konfigurasi Kirima.
            $baseUrl = rtrim(
                (string) config('services.kirima.base_url'),
                '/'
            );

            $clientId = config('services.kirima.client_id');
            $clientSecret = config('services.kirima.client_secret');
            $deviceJid = config('services.kirima.device_jid');

            if (
                !$baseUrl ||
                !$clientId ||
                !$clientSecret ||
                !$deviceJid
            ) {
                throw new RuntimeException(
                    'Konfigurasi Kirima belum lengkap. Periksa file .env.'
                );
            }

            // Buat atau perbarui riwayat pengiriman.
            $delivery = DB::transaction(function () use ($inspeksi) {

                $record = InspeksiWhatsappDelivery::where(
                    'laporan_inspeksi_id',
                    $inspeksi->id
                )
                    ->lockForUpdate()
                    ->first();

                if (!$record) {
                    $record = InspeksiWhatsappDelivery::create([
                        'laporan_inspeksi_id' => $inspeksi->id,
                        'status' => 'pending',
                        'attempts' => 0,
                    ]);
                }

                $record->update([
                    'status' => 'sending',
                    'attempts' => (int) $record->attempts + 1,
                    'error_message' => null,
                    'sent_at' => null,
                ]);

                return $record;

            }, 3);

            // Nama file PDF.
            $nomorAman = preg_replace(
                '/[^A-Za-z0-9_-]/',
                '-',
                $inspeksi->nomor_inspeksi
            );

            $filename = 'Laporan-Inspeksi-' . $nomorAman . '.pdf';

            // Generate PDF menggunakan template khusus inspeksi.
            $pdf = Pdf::loadView(
                'admin.inspeksi.pdf',
                compact('inspeksi')
            )
                ->setPaper('a4', 'portrait')
                ->output();

            // Caption WhatsApp.
            $nama = $inspeksi->nama_pelanggan_inspeksi;

            $caption = "Halo {$nama},\n\n"
                . "Berikut kami lampirkan laporan hasil inspeksi "
                . "kendaraan Anda.\n\n"
                . "Nomor inspeksi: {$inspeksi->nomor_inspeksi}\n"
                . "Silakan periksa dokumen PDF terlampir.\n\n"
                . "Terima kasih telah mempercayakan "
                . "pemeriksaan kendaraan kepada Baba Auto Service.";

            // Kirim PDF melalui Kirima API.
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

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Kirima gagal mengirim PDF. HTTP '
                    . $response->status()
                );
            }

            // Ambil message_id dari respons API.
            $responseData = $response->json();

            $messageId = data_get($responseData, 'message_id')
                ?? data_get($responseData, 'data.message_id');

            // Simpan status berhasil.
            $delivery->update([
                'status' => 'sent',
                'message_id' => $messageId,
                'error_message' => null,
                'sent_at' => now(),
            ]);

        } catch (Throwable $e) {

            // Simpan status gagal jika riwayat sudah dibuat.
            if ($delivery) {
                try {
                    $delivery->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_at' => null,
                    ]);
                } catch (Throwable $dbError) {
                    Log::error(
                        'Gagal memperbarui riwayat WhatsApp inspeksi.',
                        [
                            'error' => $dbError->getMessage(),
                            'inspeksi_id' => $inspeksi->id,
                        ]
                    );
                }
            }

            Log::error(
                'Pengiriman PDF inspeksi melalui Kirima gagal.',
                [
                    'inspeksi_id' => $inspeksi->id,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Normalisasi nomor telepon Indonesia.
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
            !preg_match('/^628[0-9]{7,12}$/', $nomor)
        ) {
            throw new RuntimeException(
                'Format nomor WhatsApp pelanggan tidak valid.'
            );
        }

        return $nomor;
    }
}