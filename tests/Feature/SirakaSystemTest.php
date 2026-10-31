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

    private function buatServisBaru(): RekamServis
    {
        $kendaraan = Kendaraan::firstOrFail();

        $response = $this->actingAs($this->admin())
            ->post('/admin/rekam-servis', [
                'konsumen_id' => $kendaraan->konsumen_id,
                'kendaraan_id' => $kendaraan->id,
                'km_masuk' => 60000,
                'keluhan_awal' => 'rem bunyi decit',
            ]);

        $response->assertSessionHasNoErrors();

        return RekamServis::where('kendaraan_id', $kendaraan->id)
            ->where('km_masuk', 60000)
            ->firstOrFail();
    }

    private function terbitkanNota(RekamServis $servis): NotaTransaksi
    {
        $jasa = Jasa::firstOrFail();

        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/jasa", [
                'jasa_id' => $jasa->id,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/nota/konfirmasi", [
                'diskon_ribuan' => 0,
            ])
            ->assertSessionHasNoErrors();

        return NotaTransaksi::where('rekam_servis_id', $servis->id)
            ->firstOrFail();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk();
    }

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

    public function test_pelanggan_can_access_service_history(): void
    {
        $pelanggan = User::where('role', 'pelanggan')->firstOrFail();

        $this->actingAs($pelanggan)
            ->get('/pelanggan/rekam-servis')
            ->assertRedirect(route('pelanggan.riwayat'));
    }

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

    public function test_rekam_servis_uses_km_masuk(): void
    {
        $servis = $this->buatServisBaru();

        $this->assertEquals(60000, $servis->km_masuk);
        $this->assertEquals(
            'menunggu_pengerjaan',
            $servis->status
        );
    }

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
            $nota->total_biaya
        );
    }

    public function test_nota_supports_partial_payments(): void
    {
        $servis = $this->buatServisBaru();
        $nota = $this->terbitkanNota($servis);

        $total = (int) $nota->total_biaya;

        $this->assertGreaterThan(1, $total);

        $pembayaranPertama = intdiv($total, 2);
        $pembayaranKedua = $total - $pembayaranPertama;

        // Pembayaran pertama
        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/nota/bayar", [
                'jumlah_diterima' => $pembayaranPertama,
                'metode_pembayaran' => 'tunai',
            ])
            ->assertSessionHasNoErrors();

        $nota->refresh();

        $this->assertEquals(
            'dibayar_sebagian',
            $nota->status_pembayaran
        );

        // Pembayaran kedua hingga lunas
        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/nota/bayar", [
                'jumlah_diterima' => $pembayaranKedua,
                'metode_pembayaran' => 'tunai',
            ])
            ->assertSessionHasNoErrors();

        $nota->refresh();

        $this->assertEquals(
            'lunas',
            $nota->status_pembayaran
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

    public function test_service_completion_records_km_keluar(): void
    {
        $servis = $this->buatServisBaru();

        $this->terbitkanNota($servis);

        $this->actingAs($this->admin())
            ->post("/admin/rekam-servis/{$servis->id}/selesaikan", [
                'km_keluar' => 60010,
                'catatan_nota' => 'Periksa rem kembali bulan depan',
            ])
            ->assertSessionHasNoErrors();

        $servis->refresh();

        $this->assertEquals(60010, $servis->km_keluar);
        $this->assertEquals('selesai', $servis->status);
    }
}
