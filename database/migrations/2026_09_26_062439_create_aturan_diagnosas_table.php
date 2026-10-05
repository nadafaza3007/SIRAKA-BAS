<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aturan_diagnosas', function (Blueprint $table) {
            $table->id();
            $table->string('kata_kunci');
            $table->text('hasil_diagnosa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aturan_diagnosas');
    }
};