<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PENTING: Migration ini diasumsikan berjalan di database asli sirakabas.sql
     * yang BELUM menerima SQL/migration tambahan sebelumnya.
     * Pastikan semua referensi km_akhir dalam kode aplikasi diganti ke km_masuk
     * SEBELUM migration ini dieksekusi.
     */
    public function up(): void
    {
        // 1. Kolom lama km_akhir sebenarnya mencatat KM saat kendaraan MASUK.
        // renameColumn mempertahankan nilai historisnya.
        Schema::table('rekam_servis', function (Blueprint $table) {
            $table->renameColumn('km_akhir', 'km_masuk');
        });

        Schema::table('rekam_servis', function (Blueprint $table) {
            $table->unsignedInteger('km_keluar')->nullable()->after('km_masuk');
        });

        // 2. Nota baru default belum_lunas; nilai nota historis tidak berubah.
        Schema::table('nota_transaksis', function (Blueprint $table) {
            $table->string('status_pembayaran')->default('belum_lunas')->change();
            $table->dateTime('tanggal_lunas')->nullable()->after('status_pembayaran');
            $table->text('catatan_nota')->nullable()->after('tanggal_lunas');
        });

        // 3. Jasa: kuantitas dan diskon per baris transaksi.
        Schema::table('detail_jasas', function (Blueprint $table) {
            $table->unsignedInteger('qty')->default(1)->after('jasa_id');
            $table->decimal('diskon', 12, 2)->default(0)->after('harga_saat_transaksi');
        });

        // 4. Satu pembayaran penuh saja per nota (tanpa DP).
        Schema::create('pembayaran_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_transaksi_id')->unique()
                ->constrained('nota_transaksis')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->decimal('jumlah_diterima', 12, 2);
            $table->decimal('jumlah_tagihan', 12, 2);
            $table->decimal('tips', 12, 2)->default(0);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->string('metode_pembayaran', 30);
            $table->dateTime('tanggal_bayar');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 5. Arus kas keluar.
        Schema::create('pengeluaran_operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->string('kategori', 100);
            $table->text('keterangan')->nullable();
            $table->decimal('jumlah', 12, 2);
            $table->string('metode_pembayaran', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // PERINGATAN: rollback menghapus semua data pada tabel/kolom tambahan.
        Schema::dropIfExists('pengeluaran_operasional');
        Schema::dropIfExists('pembayaran_notas');

        Schema::table('detail_jasas', function (Blueprint $table) {
            $table->dropColumn(['qty', 'diskon']);
        });

        Schema::table('nota_transaksis', function (Blueprint $table) {
            $table->dropColumn(['tanggal_lunas', 'catatan_nota']);
            $table->string('status_pembayaran')->default('lunas')->change();
        });

        Schema::table('rekam_servis', function (Blueprint $table) {
            $table->dropColumn('km_keluar');
        });
        Schema::table('rekam_servis', function (Blueprint $table) {
            $table->renameColumn('km_masuk', 'km_akhir');
        });
    }
};
