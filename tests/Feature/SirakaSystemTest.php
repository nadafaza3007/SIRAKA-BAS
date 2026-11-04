<?php

namespace Tests\Feature;

use App\Models\Jasa;
use App\Models\User;
use App\Models\Kendaraan;
use App\Models\RekamServis;
use App\Models\NotaTransaksi;
use App\Models\PembayaranNota;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SirakaSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    /**
     * Membuat transaksi servis baru untuk pengujian.
     */
    private function buatServisBaru(): RekamServis
    {
        $kendaraan = Kendaraan::firstOrFail();

        $this->actingAs($this->admin())
            ->post('/admin/rekam-servis', [
                'konsumen_id' => $kendaraan->konsumen_id,
                'kendaraan_id' => $kendaraan->id,
                'km_masuk' => 60000,
                'keluhan_awal' => 'Rem bunyi decit',
            ])
            ->assertSessionHasNoErrors();

        $servis = RekamServis::where('kendaraan_id', $kendaraan->id)
            ->where('km_masuk', 60000)
            ->firstOrFail();

        $this->assertEquals(
            'menunggu_pengerjaan',
            $servis->status
        );

        return $servis;
    }

    /**
     * Menambahkan jasa dan menerbitkan nota tagihan V3.
     */
    private function terbitkanNota(RekamServis $servis): NotaTransaksi
    {
        $jasa = Jasa::firstOrFail();

        // 1. Tambahkan jasa ke transaksi servis.
        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/jasa", [
                'jasa_id' => $jasa->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        // 2. Pastikan jasa tersimpan.
        $this->assertDatabaseHas('detail_jasas', [
            'rekam_servis_id' => $servis->id,
            'jasa_id' => $jasa->id,
        ]);

        // 3. Terbitkan nota menggunakan parameter V3.
        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/nota/konfirmasi", [
                'diskon_persen_total' => 0,
                'diskon_nominal_total' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        // 4. Pastikan nota diterbitkan sebagai BELUM LUNAS.
        $this->assertDatabaseHas('nota_transaksis', [
            'rekam_servis_id' => $servis->id,
            'status_pembayaran' => 'belum_lunas',
        ]);

        return NotaTransaksi::where('rekam_servis_id', $servis->id)
            ->firstOrFail();
    }

    /**
     * TEST 1: Halaman login.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk();
    }

    /**
     * TEST 2: Dashboard admin.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    /**
     * TEST 3: Smart search kendaraan.
     */
    public function test_smart_search_returns_vehicle_data(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/riwayat/search-api?q=BM')
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'plat_nomor',
                    'merk',
                    'tipe_model',
                    'konsumen_nama',
                    'km_terakhir',
                ],
            ]);
    }

    /**
     * TEST 4: Akses riwayat servis pelanggan.
     */
    public function test_pelanggan_can_access_service_history(): void
    {
        $pelanggan = User::where('role', 'pelanggan')
            ->firstOrFail();

        $this->actingAs($pelanggan)
            ->get('/pelanggan/rekam-servis')
            ->assertRedirect(route('pelanggan.riwayat'));
    }

    /**
     * TEST 5: Registrasi beberapa kendaraan.
     */
    public function test_multiple_vehicles_can_be_registered(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/konsumen', [
                'nama_lengkap' => 'Pak Joko',
                'no_hp' => '089911223344',
                'kendaraan' => [
                    [
                        'plat_nomor' => 'BM 9999 JK',
                        'merk' => 'Toyota',
                        'tipe_model' => 'Fortuner',
                        'tahun' => 2022,
                    ],
                    [
                        'plat_nomor' => 'BM 8888 JK',
                        'merk' => 'Honda',
                        'tipe_model' => 'CR-V',
                        'tahun' => 2023,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kendaraans', [
            'plat_nomor' => 'BM 9999 JK',
        ]);

        $this->assertDatabaseHas('kendaraans', [
            'plat_nomor' => 'BM 8888 JK',
        ]);
    }

    /**
     * TEST 6: Penolakan plat nomor duplikat.
     */
    public function test_duplicate_vehicle_plate_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/konsumen', [
                'nama_lengkap' => 'Konsumen Baru',
                'no_hp' => '087711223344',
                'kendaraan' => [
                    [
                        'plat_nomor' => 'BM 1234 ABC',
                        'merk' => 'Toyota',
                        'tipe_model' => 'Avanza',
                    ],
                ],
            ])
            ->assertSessionHas('error');
    }

    /**
     * TEST 7: KM masuk pada transaksi servis.
     */
    public function test_rekam_servis_uses_km_masuk(): void
    {
        $servis = $this->buatServisBaru();

        $this->assertEquals(
            60000,
            $servis->km_masuk
        );

        $this->assertEquals(
            'menunggu_pengerjaan',
            $servis->status
        );
    }

    /**
     * TEST 8: Koreksi diagnosis.
     */
    public function test_koreksi_diagnosa_updates_service_status(): void
    {
        $servis = $this->buatServisBaru();

        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/koreksi", [
                'diagnosa_akhir' => 'Kampas rem menipis',
                'tindakan_servis' => 'Ganti kampas rem',
            ])
            ->assertSessionHasNoErrors();

        $servis->refresh();

        $this->assertEquals(
            'sedang_dikerjakan',
            $servis->status
        );

        $this->assertEquals(
            'Kampas rem menipis',
            $servis->diagnosa_akhir
        );
    }

    /**
     * TEST 9: Nota yang baru diterbitkan berstatus belum lunas.
     */
    public function test_nota_is_initially_unpaid(): void
    {
        $servis = $this->buatServisBaru();

        $nota = $this->terbitkanNota($servis);

        $this->assertEquals(
            'belum_lunas',
            $nota->status_pembayaran
        );

        $this->assertGreaterThan(
            0,
            (float) $nota->total_biaya
        );

        $this->assertNotNull(
            $nota->tanggal_terbit_tagihan
        );

        $this->assertNull(
            $nota->tanggal_lunas
        );

        $this->assertEquals(
            0,
            PembayaranNota::where(
                'nota_transaksi_id',
                $nota->id
            )->count()
        );
    }

    /**
     * TEST 10: Pembayaran sebagian hingga lunas.
     */
    public function test_nota_supports_partial_payments(): void
    {
        $servis = $this->buatServisBaru();

        $nota = $this->terbitkanNota($servis);

        $total = (int) round((float) $nota->total_biaya);

        $this->assertGreaterThan(1, $total);

        $pembayaranPertama = intdiv($total, 2);
        $pembayaranKedua = $total - $pembayaranPertama;

        // Pembayaran pertama: sebagian.
        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/nota/bayar", [
                'jumlah_diterima' => $pembayaranPertama,
                'metode_pembayaran' => 'tunai',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $nota->refresh();

        $this->assertEquals(
            'dibayar_sebagian',
            $nota->status_pembayaran
        );

        $this->assertNull(
            $nota->tanggal_lunas
        );

        $this->assertEquals(
            1,
            PembayaranNota::where(
                'nota_transaksi_id',
                $nota->id
            )->count()
        );

        $this->assertEquals(
            $pembayaranPertama,
            (int) PembayaranNota::where(
                'nota_transaksi_id',
                $nota->id
            )->sum('nominal_dialokasikan')
        );

        // Pembayaran kedua: pelunasan.
        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/nota/bayar", [
                'jumlah_diterima' => $pembayaranKedua,
                'metode_pembayaran' => 'tunai',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $nota->refresh();

        $this->assertEquals(
            'lunas',
            $nota->status_pembayaran
        );

        $this->assertNotNull(
            $nota->tanggal_lunas
        );

        $this->assertEquals(
            2,
            PembayaranNota::where(
                'nota_transaksi_id',
                $nota->id
            )->count()
        );

        $this->assertEquals(
            $total,
            (int) PembayaranNota::where(
                'nota_transaksi_id',
                $nota->id
            )->sum('nominal_dialokasikan')
        );
    }

    /**
     * TEST 11: Penyelesaian servis mencatat KM keluar.
     */
    public function test_service_completion_records_km_keluar(): void
    {
        $servis = $this->buatServisBaru();

        $nota = $this->terbitkanNota($servis);

        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/selesaikan", [
                'km_keluar' => 60010,
                'catatan_nota' => 'Periksa rem kembali bulan depan',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $servis->refresh();
        $nota->refresh();

        $this->assertEquals(
            60010,
            $servis->km_keluar
        );

        $this->assertEquals(
            'selesai',
            $servis->status
        );

        $this->assertEquals(
            'Periksa rem kembali bulan depan',
            $nota->catatan_nota
        );

        $this->assertEquals(
            'belum',
            $nota->status_rekomendasi
        );
    }
}