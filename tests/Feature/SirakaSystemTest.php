<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Konsumen;
use App\Models\Kendaraan;
use App\Models\RekamServis;
use App\Models\AturanDiagnosa;
use App\Models\Sparepart;
use App\Models\Jasa;
use App\Models\NotaTransaksi;
use App\Models\AuditLog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SirakaSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIRAKA');
    }

    public function test_admin_can_login_and_access_dashboard()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Panel Owner', false); // Tambahkan false agar tidak memvalidasi HTML entity encoding
    }

    public function test_anggota_cannot_access_master_data()
    {
        $anggota = User::where('role', 'anggota')->first();
        $response = $this->actingAs($anggota)->get('/admin/sparepart');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_smart_search_api_returns_results()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/riwayat/search-api?q=BM');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'plat_nomor', 'merk', 'tipe_model', 'konsumen_nama', 'km_terakhir']
        ]);
    }

    public function test_pelanggan_can_access_own_service_records()
    {
        $pelanggan = User::where('role', 'pelanggan')->first();
        $response = $this->actingAs($pelanggan)->get('/pelanggan/rekam-servis');
        $response->assertStatus(200);
        $response->assertSee('Rekam Medis Kendaraan');
    }

    public function test_pelanggan_registration_links_matching_konsumen_phone()
    {
        // Konsumen Rina Marlina has no_hp '085298765432' in seeder
        $response = $this->post('/register', [
            'nama_lengkap' => 'Rina Marlina',
            'no_hp' => '085298765432',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $user = User::where('no_hp', '085298765432')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pelanggan', $user->role);
        $this->assertNotNull($user->konsumen_id);
    }

    public function test_multi_unit_registration_and_duplicate_plat_detection()
    {
        $admin = User::where('role', 'admin')->first();

        // Register multi-unit for new customer
        $response = $this->actingAs($admin)->post('/admin/konsumen', [
            'nama_lengkap' => 'Pak Joko',
            'no_hp' => '089911223344',
            'kendaraan' => [
                [
                    'plat_nomor' => 'BM 9999 JK',
                    'merk' => 'Toyota',
                    'tipe_model' => 'Fortuner',
                    'tahun' => 2022
                ],
                [
                    'plat_nomor' => 'BM 8888 JK',
                    'merk' => 'Honda',
                    'tipe_model' => 'CR-V',
                    'tahun' => 2023
                ]
            ]
        ]);

        $response->assertRedirect('/admin/konsumen');
        $this->assertDatabaseHas('kendaraans', ['plat_nomor' => 'BM 9999 JK']);
        $this->assertDatabaseHas('kendaraans', ['plat_nomor' => 'BM 8888 JK']);

        // Duplicate plat should fail
        $failResponse = $this->actingAs($admin)->post('/admin/konsumen', [
            'nama_lengkap' => 'Orang Lain',
            'no_hp' => '087711223344',
            'kendaraan' => [
                [
                    'plat_nomor' => 'BM 9999 JK',
                    'merk' => 'Toyota',
                    'tipe_model' => 'Fortuner',
                ]
            ]
        ]);

        $failResponse->assertSessionHas('error');
    }

    public function test_rekam_servis_creation_with_keyword_matching()
    {
        $admin = User::where('role', 'admin')->first();
        $kendaraan = Kendaraan::first();

        $response = $this->actingAs($admin)->post('/admin/rekam-servis', [
            'kendaraan_id' => $kendaraan->id,
            'km_akhir' => 60000,
            'keluhan_awal' => 'rem bunyi decit saat pengereman mendadak',
        ]);

        $response->assertRedirect('/admin/rekam-servis');
        $servis = RekamServis::latest()->first();
        $this->assertEquals('menunggu_pengerjaan', $servis->status);
        $this->assertEquals('Pengecekan Kampas & Piringan Rem', $servis->diagnosa_awal);
    }

    public function test_koreksi_diagnosa_physical_inspection_and_nota_workflow()
    {
        $admin = User::where('role', 'admin')->first();
        $servis = RekamServis::first(); 

        // 1. Koreksi Diagnosa Fisik (UC-05)
        $koreksiResponse = $this->actingAs($admin)->post("/admin/rekam-servis/{$servis->id}/koreksi", [
            'diagnosa_akhir' => 'Freon habis dan kompresor bocor halus',
            'tindakan_servis' => 'Ganti O-ring kompresor dan isi freon R134a murni',
        ]);

        $koreksiResponse->assertRedirect("/admin/rekam-servis/{$servis->id}/nota");
        $servis->refresh();
        $this->assertEquals('siap_cetak_nota', $servis->status);

        // 2. Tambah Catatan Rekomendasi (UC-06)
        $this->actingAs($admin)->post("/admin/rekam-servis/{$servis->id}/rekomendasi", [
            'catatan' => 'Cek kembali tekanan AC setelah 1 bulan pemakaian'
        ]);

        $this->assertDatabaseHas('catatan_rekomendasis', [
            'rekam_servis_id' => (int) $servis->id,
            'catatan'         => 'Cek kembali tekanan AC setelah 1 bulan pemakaian'
        ]);

        // 3. Konfirmasi & Cetak Nota (UC-08)
        $notaResponse = $this->actingAs($admin)->post("/admin/rekam-servis/{$servis->id}/nota/konfirmasi", [
            'metode_cetak' => 'thermal',
            'diskon' => 20000
        ]);

        $servis->refresh();
        $this->assertEquals('selesai', $servis->status);
        $this->assertNotNull($servis->nota);
        $this->assertEquals('lunas', $servis->nota->status_pembayaran);
    }

    public function test_financial_report_and_audit_logging()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/laporan');
        $response->assertStatus(200);
        $response->assertSee('Pendapatan Kotor (Omzet)');
        $response->assertSee('Laba / Rugi Bersih');

        // Cetak PDF (UC-13) generates AuditLog
        $pdfResponse = $this->actingAs($admin)->get('/admin/laporan/cetak-pdf');
        $pdfResponse->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'entitas' => 'Laporan Pemasukan',
            'aksi' => 'Cetak / Unduh Laporan Keuangan Periode ' . now()->startOfMonth()->toDateString() . ' s/d ' . now()->endOfMonth()->toDateString(),
        ]);
    }
}

