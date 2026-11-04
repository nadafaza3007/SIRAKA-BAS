<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_spareparts', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_spareparts', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0)->after('harga_jual_saat_transaksi');
            }
            if (!Schema::hasColumn('detail_spareparts', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 12, 2)->default(0)->after('diskon_persen');
            }
        });

        Schema::table('detail_jasas', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_jasas', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0)->after('harga_saat_transaksi');
            }
            if (!Schema::hasColumn('detail_jasas', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 12, 2)->default(0)->after('diskon_persen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_spareparts', function (Blueprint $table) {
            $table->dropColumn(['diskon_persen', 'diskon_nominal']);
        });

        Schema::table('detail_jasas', function (Blueprint $table) {
            $table->dropColumn(['diskon_persen', 'diskon_nominal']);
        });
    }
};