<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('nota_transaksis', 'status_rekomendasi')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->string('status_rekomendasi', 20)->nullable()->after('catatan_nota');
            });
        }
    }

    public function down(): void
    {
        // Tidak menghapus kolom untuk menjaga data rekomendasi historis.
        // Kolom mungkin sudah dibuat secara manual sebelum migration ini.
    }
};
