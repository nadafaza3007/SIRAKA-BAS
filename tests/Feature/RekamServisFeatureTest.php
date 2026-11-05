<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RekamServis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RekamServisFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private int $konsumenId;
    private int $kendaraanId;
    private int $kendaraanLainId;

    protected function setUp(): void
    {
        parent::setUp();

        // Membuat akun admin pengujian.
        $this->staff = User::create([
            'name' => 'Admin Pengujian',
            'no_hp' => '081234567891',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // Membuat konsumen.
        $this->konsumenId = DB::table('konsumens')->insertGetId([
            'nama_lengkap' => 'Konsumen Pengujian',
            'no_hp' => '081234567892',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Membuat kendaraan milik konsumen.
        $this->kendaraanId = DB::table('kendaraans')->insertGetId([
            'konsumen_id' => $this->konsumenId,
            'plat_nomor' => 'P 1234 TEST',
            'merk' => 'Toyota',
            'tipe_model' => 'Avanza',
            'tahun' => 2022,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Membuat kendaraan milik konsumen yang berbeda.
        $konsumenLainId = DB::table('konsumens')->insertGetId([
            'nama_lengkap' => 'Konsumen Lain',
            'no_hp' => '081234567893',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->kendaraanLainId = DB::table('kendaraans')->insertGetId([
            'konsumen_id' => $konsumenLainId,
            'plat_nomor' => 'P 5678 TEST',
            'merk' => 'Honda',
            'tipe_model' => 'CR-V',
            'tahun' => 2023,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->staff);
    }

    /**
     * Data standar untuk membuat rekam servis.
     */
    private function dataServis(array $tambahan = []): array
    {
        return array_merge([
            'konsumen_id' => $this->konsumenId,
            'kendaraan_id' => $this->kendaraanId,
            'km_masuk' => 25000,
            'keluhan_awal' => 'Pemeriksaan kendaraan secara rutin',
        ], $tambahan);
    }

    /**
     * Membuat rekam servis langsung untuk pengujian koreksi.
     */
    private function buatRekamServis(string $status = 'menunggu_pengerjaan'): RekamServis
    {
        return RekamServis::create([
            'kendaraan_id' => $this->kendaraanId,
            'user_id' => $this->staff->id,
            'tanggal_servis' => now()->toDateString(),
            'km_masuk' => 25000,
            'keluhan_awal' => 'Pengujian rekam servis',
            'diagnosa_awal' => 'Pengecekan Umum',
            'status' => $status,
        ]);
    }

    /**
     * RS-01
     * Membuat rekam servis baru.
     */
    public function test_rekam_servis_baru_berhasil_dibuat(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis()
        );

        $response->assertRedirect(route('admin.rekam-servis.index'));

        $this->assertDatabaseHas('rekam_servis', [
            'kendaraan_id' => $this->kendaraanId,
            'user_id' => $this->staff->id,
            'km_masuk' => 25000,
            'keluhan_awal' => 'Pemeriksaan kendaraan secara rutin',
            'diagnosa_awal' => 'Pengecekan Umum',
            'status' => 'menunggu_pengerjaan',
        ]);
    }

    /**
     * RS-02
     * Konsumen wajib dipilih.
     */
    public function test_konsumen_wajib_dipilih(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis(['konsumen_id' => ''])
        );

        $response->assertSessionHasErrors('konsumen_id');

        $this->assertDatabaseCount('rekam_servis', 0);
    }

    /**
     * RS-03
     * Kendaraan wajib dipilih.
     */
    public function test_kendaraan_wajib_dipilih(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis(['kendaraan_id' => ''])
        );

        $response->assertSessionHasErrors('kendaraan_id');

        $this->assertDatabaseCount('rekam_servis', 0);
    }

    /**
     * RS-04
     * Kilometer tidak boleh negatif.
     */
    public function test_km_masuk_tidak_boleh_negatif(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis(['km_masuk' => -100])
        );

        $response->assertSessionHasErrors('km_masuk');

        $this->assertDatabaseCount('rekam_servis', 0);
    }

    /**
     * RS-05
     * Keluhan awal wajib diisi.
     */
    public function test_keluhan_awal_wajib_diisi(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis(['keluhan_awal' => ''])
        );

        $response->assertSessionHasErrors('keluhan_awal');

        $this->assertDatabaseCount('rekam_servis', 0);
    }

    /**
     * RS-06
     * Kendaraan harus sesuai dengan konsumen.
     */
    public function test_kendaraan_harus_milik_konsumen_yang_dipilih(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis([
                'kendaraan_id' => $this->kendaraanLainId,
            ])
        );

        $response->assertSessionHasErrors('kendaraan_id');

        $this->assertDatabaseCount('rekam_servis', 0);
    }

    /**
     * RS-07
     * Diagnosa otomatis berdasarkan kata kunci.
     */
    public function test_diagnosa_otomatis_berhasil_dideteksi(): void
    {
        DB::table('aturan_diagnosas')->insert([
            'kata_kunci' => 'rem bunyi',
            'hasil_diagnosa' => 'Pengecekan Kampas Rem',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis([
                'keluhan_awal' => 'Rem bunyi ketika diinjak',
            ])
        );

        $response->assertRedirect(route('admin.rekam-servis.index'));

        $this->assertDatabaseHas('rekam_servis', [
            'kendaraan_id' => $this->kendaraanId,
            'diagnosa_awal' => 'Pengecekan Kampas Rem',
        ]);
    }

    /**
     * RS-08
     * Diagnosa default jika tidak ditemukan kecocokan.
     */
    public function test_diagnosa_default_digunakan_jika_tidak_cocok(): void
    {
        $response = $this->post(
            route('admin.rekam-servis.store'),
            $this->dataServis([
                'keluhan_awal' => 'Warna kendaraan terlihat berbeda',
            ])
        );

        $response->assertRedirect(route('admin.rekam-servis.index'));

        $this->assertDatabaseHas('rekam_servis', [
            'diagnosa_awal' => 'Pengecekan Umum',
        ]);
    }

    /**
     * RS-09 dan RS-10
     * Koreksi diagnosa dan tindakan servis.
     */
    public function test_koreksi_diagnosa_dan_tindakan_berhasil_disimpan(): void
    {
        $servis = $this->buatRekamServis();

        $response = $this->post(
            route('admin.rekam-servis.koreksi', $servis->id),
            [
                'diagnosa_akhir' => 'Kampas rem mulai menipis',
                'tindakan_servis' => 'Penggantian kampas rem depan',
            ]
        );

        $response->assertRedirect(route('admin.rekam-servis.index'));

        $this->assertDatabaseHas('rekam_servis', [
            'id' => $servis->id,
            'diagnosa_akhir' => 'Kampas rem mulai menipis',
            'tindakan_servis' => 'Penggantian kampas rem depan',
            'status' => 'sedang_dikerjakan',
        ]);
    }

    /**
     * RS-11
     * Servis yang sudah selesai tidak boleh dikoreksi.
     */
    public function test_servis_selesai_tidak_boleh_dikoreksi(): void
    {
        $servis = $this->buatRekamServis('selesai');

        $response = $this->post(
            route('admin.rekam-servis.koreksi', $servis->id),
            [
                'diagnosa_akhir' => 'Diagnosa perubahan',
                'tindakan_servis' => 'Tindakan perubahan',
            ]
        );

        $response->assertRedirect(route('admin.rekam-servis.index'));

        $this->assertDatabaseMissing('rekam_servis', [
            'id' => $servis->id,
            'diagnosa_akhir' => 'Diagnosa perubahan',
        ]);
    }

    /**
     * RS-12
     * Servis yang sudah memiliki nota tidak boleh dikoreksi.
     */
    public function test_servis_yang_memiliki_nota_tidak_boleh_dikoreksi(): void
    {
        $servis = $this->buatRekamServis();

        DB::table('nota_transaksis')->insert([
            'no_nota' => 'TEST-RS-NOTA-001',
            'rekam_servis_id' => $servis->id,
            'tanggal_cetak' => now()->toDateString(),
            'subtotal' => 100000,
            'diskon' => 0,
            'total_biaya' => 100000,
            'metode_cetak' => 'thermal',
            'status_pembayaran' => 'belum_lunas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(
            route('admin.rekam-servis.koreksi', $servis->id),
            [
                'diagnosa_akhir' => 'Diagnosa perubahan',
                'tindakan_servis' => 'Tindakan perubahan',
            ]
        );

        $response->assertRedirect(route('admin.rekam-servis.index'));

        $this->assertDatabaseMissing('rekam_servis', [
            'id' => $servis->id,
            'diagnosa_akhir' => 'Diagnosa perubahan',
        ]);
    }
}