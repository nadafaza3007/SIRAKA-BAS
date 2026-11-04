<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_inspeksis', function (Blueprint $table) {
            $table->unsignedBigInteger('kendaraan_id')->nullable()->change();
            $table->unsignedBigInteger('km_kendaraan')->nullable()->change();
            $table->unsignedInteger('rating_score')->nullable()->change();
            $table->text('saran')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_inspeksis', function (Blueprint $table) {
            $table->unsignedBigInteger('kendaraan_id')->nullable(false)->change();
            $table->unsignedBigInteger('km_kendaraan')->nullable(false)->change();
            $table->unsignedInteger('rating_score')->nullable(false)->change();
            $table->text('saran')->nullable(false)->change();
        });
    }
};