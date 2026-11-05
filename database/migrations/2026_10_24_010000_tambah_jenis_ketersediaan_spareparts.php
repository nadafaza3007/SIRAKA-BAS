<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->string('jenis_ketersediaan', 10)->default('stok')->after('nama_barang');
        });

        // Baris lama tetap stok; PO disimpan dengan NULL, bukan angka 0.
        Schema::table('spareparts', function (Blueprint $table) {
            $table->integer('stok')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('spareparts')->whereNull('stok')->update(['stok' => 0]);
        Schema::table('spareparts', function (Blueprint $table) {
            $table->integer('stok')->default(0)->nullable(false)->change();
            $table->dropColumn('jenis_ketersediaan');
        });
    }
};