<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Konsumen;
use App\Models\Kendaraan;
use App\Models\Sparepart;
use App\Models\Jasa;
use App\Models\AturanDiagnosa;
use App\Models\RekamServis;
use App\Models\NotaTransaksi;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin / Owner
        $admin = User::updateOrCreate(
            ['no_hp' => '081234567890'],
            [
                'name'     => 'Administrator SIRAKA',
                'role'     => 'admin',
                'password' => bcrypt('adminni'),
            ]
        );

        // 2. Akun Mekanik / Anggota
        User::updateOrCreate(
            ['no_hp' => '081298765432'],
            [
                'name'     => 'Mekanik Senior',
                'role'     => 'anggota',
                'password' => bcrypt('mekanikk'),
            ]
        );

        // 3. Data Konsumen & Akun Pelanggan
        $konsumen1 = Konsumen::firstOrCreate(
            ['no_hp' => '081122334455'],
            ['nama_lengkap' => 'Budi Santoso']
        );

        $kendaraan1 = Kendaraan::firstOrCreate(
            ['plat_nomor' => 'BM 1234 ABC'],
            [
                'konsumen_id' => $konsumen1->id,
                'merk'        => 'Toyota',
                'tipe_model'  => 'Avanza Veloz',
                'tahun'       => 2021,
            ]
        );

        User::updateOrCreate(
            ['no_hp' => '081122334455'],
            [
                'name'        => 'Budi Santoso',
                'role'        => 'pelanggan',
                'konsumen_id' => $konsumen1->id,
                'password'    => bcrypt('budisan'),
            ]
        );

        $konsumen2 = Konsumen::firstOrCreate(
            ['no_hp' => '085266778899'],
            ['nama_lengkap' => 'Siti Aminah']
        );

        $kendaraan2 = Kendaraan::firstOrCreate(
            ['plat_nomor' => 'BM 8888 XYZ'],
            [
                'konsumen_id' => $konsumen2->id,
                'merk'        => 'Honda',
                'tipe_model'  => 'CR-V Turbo',
                'tahun'       => 2022,
            ]
        );

        // 4. Master Sparepart
        $sp1 = Sparepart::firstOrCreate(
            ['kode_barang' => 'OLI-001'],
            [
                'nama_barang' => 'Oli Shell Helix HX8 5W-30 (4L)',
                'stok'        => 15,
                'harga_modal' => 320000,
                'harga_jual'  => 400000,
                'nama_toko'   => 'PT Shell Indonesia',
            ]
        );

        $sp2 = Sparepart::firstOrCreate(
            ['kode_barang' => 'KMP-002'],
            [
                'nama_barang' => 'Kampas Rem Depan Avanza',
                'stok'        => 8,
                'harga_modal' => 150000,
                'harga_jual'  => 220000,
                'nama_toko'   => 'Sumber Makmur Motor',
            ]
        );

        // 5. Master Jasa
        $js1 = Jasa::firstOrCreate(
            ['nama_jasa' => 'Tune Up Mesin & Carbon Clean'],
            ['harga' => 250000, 'status' => 'aktif']
        );

        $js2 = Jasa::firstOrCreate(
            ['nama_jasa' => 'Ganti Oli & Filter'],
            ['harga' => 50000, 'status' => 'aktif']
        );

        // 6. Master Aturan Diagnosa
        AturanDiagnosa::firstOrCreate(
            ['kata_kunci' => 'rem, bunyi, decit'],
            ['hasil_diagnosa' => 'Pengecekan Kampas & Piringan Rem Depan/Belakang']
        );

        AturanDiagnosa::firstOrCreate(
            ['kata_kunci' => 'oli, berkurang, hitam'],
            ['hasil_diagnosa' => 'Pengantian Oli Mesin & Filter Oli']
        );

        // 7. Sampel Data Rekam Servis 1
        $servis1 = RekamServis::firstOrCreate(
            ['kendaraan_id' => $kendaraan1->id, 'km_akhir' => 45000],
            [
                'keluhan_awal'   => 'Rem bunyi berdecit saat diinjak pada kecepatan tinggi',
                'diagnosa_awal'  => 'Pengecekan Kampas & Piringan Rem Depan/Belakang',
                'diagnosa_akhir' => 'Kampas rem depan menipis dan kotor',
                'tindakan_servis'=> 'Penggantian kampas rem depan baru dan pembersihan rem belakang',
                'tanggal_servis' => now()->subDays(1),
                'status'         => 'menunggu_pengerjaan',
                'user_id'        => $admin->id,
            ]
        );

        // 8. Sampel Data Rekam Servis 2
        $servis2 = RekamServis::firstOrCreate(
            ['kendaraan_id' => $kendaraan2->id, 'km_akhir' => 28000],
            [
                'keluhan_awal'   => 'Waktunya servis berkala dan ganti oli mesin',
                'diagnosa_awal'  => 'Pengantian Oli Mesin & Filter Oli',
                'diagnosa_akhir' => 'Oli mesin sudah keruh, butuh penggantian',
                'tindakan_servis'=> 'Ganti oli mesin Shell Helix HX8 dan tune up ringan',
                'tanggal_servis' => now()->subDays(3),
                'status'         => 'selesai',
                'user_id'        => $admin->id,
            ]
        );

        // Nota Transaksi
        NotaTransaksi::firstOrCreate(
            ['rekam_servis_id' => $servis2->id],
            [
                'no_nota'           => 'NOTA-0001',
                'tanggal_cetak'     => now()->subDays(3)->toDateString(),
                'subtotal'          => 650000,
                'diskon'            => 50000,
                'total_biaya'       => 600000,
                'metode_cetak'      => 'standar',
                'status_pembayaran' => 'lunas',
            ]
        );
    }
}