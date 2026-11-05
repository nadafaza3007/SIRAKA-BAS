<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\NotaTransaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotaTransaksiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat data nota beserta seluruh relasi wajib.
     */
    private function buatNotaPengujian(): NotaTransaksi
    {
        $now = now();

        // ==========================================
        // 1. MEMBUAT DATA KONSUMEN
        // ==========================================

        $konsumenId = DB::table('konsumens')->insertGetId([
            'nama_lengkap' => 'Konsumen Integration Test',
            'no_hp' => '081234567890',
            'status' => 'aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ==========================================
        // 2. MEMBUAT DATA KENDARAAN
        // ==========================================

        $kendaraanId = DB::table('kendaraans')->insertGetId([
            'konsumen_id' => $konsumenId,
            'plat_nomor' => 'P 1234 TEST',
            'merk' => 'Toyota',
            'tipe_model' => 'Avanza',
            'tahun' => 2022,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ==========================================
        // 3. MEMBUAT DATA REKAM SERVIS
        // ==========================================

        $rekamServisId = DB::table('rekam_servis')->insertGetId([
            'kendaraan_id' => $kendaraanId,
            'user_id' => null,
            'tanggal_servis' => $now->toDateString(),
            'km_masuk' => 25000,
            'km_keluar' => 25000,
            'keluhan_awal' => 'Pengujian transaksi servis',
            'diagnosa_awal' => 'Pemeriksaan rutin',
            'diagnosa_akhir' => 'Servis rutin',
            'tindakan_servis' => 'Pengujian sistem nota',
            'status' => 'selesai',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ==========================================
        // 4. MEMBUAT DATA NOTA TRANSAKSI
        // ==========================================

        $notaId = DB::table('nota_transaksis')->insertGetId([
            'no_nota' => 'TEST-NOTA-' . strtoupper(Str::random(12)),
            'rekam_servis_id' => $rekamServisId,
            'tanggal_cetak' => $now->toDateString(),
            'tanggal_terbit_tagihan' => $now,
            'subtotal' => 500000,
            'diskon' => 0,
            'total_biaya' => 500000,
            'metode_cetak' => 'standar',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_lunas' => null,
            'catatan_nota' => 'Integration Test',
            'status_rekomendasi' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return NotaTransaksi::findOrFail($notaId);
    }

    /**
     * Membuat data pembayaran untuk pengujian.
     */
    private function buatPembayaran(
        int $notaId,
        float $nominal,
        string $metode = 'tunai'
    ): void {
        $now = now();

        DB::table('pembayaran_notas')->insert([
            'nota_transaksi_id' => $notaId,
            'no_bukti' => 'TEST-BAYAR-' . strtoupper(Str::random(12)),
            'user_id' => null,
            'jumlah_diterima' => $nominal,
            'jumlah_tagihan' => $nominal,
            'nominal_dialokasikan' => $nominal,
            'tips' => 0,
            'kembalian' => 0,
            'metode_pembayaran' => $metode,
            'tanggal_bayar' => $now,
            'catatan' => 'Pembayaran Integration Test',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * NT-12
     * Menguji relasi pembayaran pada nota.
     */
    public function test_nota_memiliki_relasi_pembayaran(): void
    {
        $nota = $this->buatNotaPengujian();

        $this->buatPembayaran($nota->id, 100000);
        $this->buatPembayaran($nota->id, 150000, 'transfer');

        $this->assertCount(2, $nota->fresh()->pembayaran);
    }

    /**
     * NT-13
     * Menguji total pembayaran dari database.
     */
    public function test_total_dibayar_dihitung_dari_database(): void
    {
        $nota = $this->buatNotaPengujian();

        $this->buatPembayaran($nota->id, 100000);
        $this->buatPembayaran($nota->id, 150000, 'transfer');

        $nota->refresh();

        $this->assertEquals(
            250000.00,
            (float) $nota->total_dibayar
        );
    }

    /**
     * NT-14
     * Menguji sisa tagihan dari database.
     */
    public function test_sisa_tagihan_dihitung_dengan_benar(): void
    {
        $nota = $this->buatNotaPengujian();

        $this->buatPembayaran($nota->id, 100000);
        $this->buatPembayaran($nota->id, 150000, 'transfer');

        $nota->refresh();

        $this->assertEquals(
            250000.00,
            (float) $nota->sisa_tagihan
        );
    }

    /**
     * NT-15
     * Menguji relasi rekam servis.
     */
    public function test_nota_memiliki_relasi_rekam_servis(): void
    {
        $nota = $this->buatNotaPengujian();

        $this->assertNotNull($nota->rekamServis);

        $this->assertSame(
            'Pengujian transaksi servis',
            $nota->rekamServis->keluhan_awal
        );

        $this->assertEquals(
            25000,
            $nota->rekamServis->km_masuk
        );
    }

    /**
     * NT-16
     * Menguji relasi riwayat pengiriman WhatsApp.
     */
    public function test_nota_memiliki_relasi_whatsapp_deliveries(): void
    {
        $nota = $this->buatNotaPengujian();

        $now = now();

        DB::table('nota_whatsapp_deliveries')->insert([
            'nota_transaksi_id' => $nota->id,
            'jenis' => 'tagihan_awal',
            'status' => 'sent',
            'attempts' => 1,
            'message_id' => 'TEST-WA-' . strtoupper(Str::random(8)),
            'error_message' => null,
            'sent_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->assertCount(
            1,
            $nota->fresh()->whatsappDeliveries
        );

        $this->assertSame(
            'tagihan_awal',
            $nota->fresh()->whatsappDeliveries->first()->jenis
        );

        $this->assertSame(
            'sent',
            $nota->fresh()->whatsappDeliveries->first()->status
        );
    }
}