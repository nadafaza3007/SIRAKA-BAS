<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Database lama: pertahankan data KM saat mengganti nama kolom.
        $adaKmLama = Schema::hasColumn('rekam_servis', 'km_akhir');
        $adaKmMasuk = Schema::hasColumn('rekam_servis', 'km_masuk');

        if ($adaKmLama && $adaKmMasuk) {
            throw new RuntimeException(
                'km_akhir dan km_masuk sama-sama tersedia. Periksa data sebelum melanjutkan.'
            );
        }

        if ($adaKmLama && ! $adaKmMasuk) {
            Schema::table('rekam_servis', function (Blueprint $table) {
                $table->renameColumn('km_akhir', 'km_masuk');
            });
        }

        if (! Schema::hasColumn('rekam_servis', 'km_masuk')) {
            throw new RuntimeException('Kolom rekam_servis.km_masuk belum tersedia.');
        }

        if (! Schema::hasColumn('rekam_servis', 'km_keluar')) {
            Schema::table('rekam_servis', function (Blueprint $table) {
                $table->unsignedInteger('km_keluar')->nullable()->after('km_masuk');
            });
        }

        // Hanya lengkapi kolom nota yang belum ada; jangan ubah data historis.
        if (! Schema::hasColumn('nota_transaksis', 'status_pembayaran')) {
            throw new RuntimeException('Kolom nota_transaksis.status_pembayaran belum tersedia.');
        }

        if (! Schema::hasColumn('nota_transaksis', 'tanggal_lunas')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->dateTime('tanggal_lunas')->nullable()->after('status_pembayaran');
            });
        }

        if (! Schema::hasColumn('nota_transaksis', 'catatan_nota')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->text('catatan_nota')->nullable()->after('tanggal_lunas');
            });
        }

        if (! Schema::hasColumn('detail_jasas', 'qty')) {
            Schema::table('detail_jasas', function (Blueprint $table) {
                $table->unsignedInteger('qty')->default(1)->after('jasa_id');
            });
        }

        // Pembayaran cicilan sudah dikelola terpisah. Jangan CREATE atau DROP tabelnya.
        $kolomPembayaran = [
            'nota_transaksi_id', 'user_id', 'jumlah_diterima',
            'jumlah_tagihan', 'nominal_dialokasikan', 'tips',
            'kembalian', 'metode_pembayaran', 'tanggal_bayar',
        ];

        if (! Schema::hasTable('pembayaran_notas')) {
            throw new RuntimeException('Tabel pembayaran_notas belum tersedia.');
        }

        foreach ($kolomPembayaran as $kolom) {
            if (! Schema::hasColumn('pembayaran_notas', $kolom)) {
                throw new RuntimeException("Kolom pembayaran_notas.{$kolom} belum tersedia.");
            }
        }

        // detail_jasas.diskon diurus migration terpisah.
        // pengeluaran_operasional tidak dibuat karena tidak digunakan saat ini.
    }

    public function down(): void
    {
        // Tidak menghapus data transaksi, cicilan, dan KM historis.
    }
};
