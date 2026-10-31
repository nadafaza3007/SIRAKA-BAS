<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('laporan_inspeksis', function (Blueprint $table) {
            $table->unsignedBigInteger('konsumen_id')->nullable()->change();
            $table->string('nama_konsumen_inspeksi')->nullable();
            $table->string('no_wa_inspeksi',25)->nullable();
            $table->string('plat_nomor_inspeksi',20)->nullable();
            $table->string('merk_inspeksi',100)->nullable();
            $table->string('tipe_model_inspeksi',100)->nullable();
            $table->unsignedSmallInteger('tahun_inspeksi')->nullable();
            $table->decimal('cc_inspeksi',5,2)->nullable();
            $table->string('transmisi_inspeksi',20)->nullable();
        });
        // Snapshot nama/WA untuk laporan historis yang sudah ada.
        \Illuminate\Support\Facades\DB::statement('UPDATE laporan_inspeksis li LEFT JOIN konsumens k ON k.id=li.konsumen_id SET li.nama_konsumen_inspeksi=k.nama_lengkap,li.no_wa_inspeksi=k.no_hp WHERE li.nama_konsumen_inspeksi IS NULL');
    }
    public function down(): void {
        // Jangan paksakan konsumen_id NOT NULL: laporan tamu mungkin telah dibuat.
        Schema::table('laporan_inspeksis', function (Blueprint $table) {
            $table->dropColumn(['nama_konsumen_inspeksi','no_wa_inspeksi','plat_nomor_inspeksi','merk_inspeksi','tipe_model_inspeksi','tahun_inspeksi','cc_inspeksi','transmisi_inspeksi']);
        });
    }
};