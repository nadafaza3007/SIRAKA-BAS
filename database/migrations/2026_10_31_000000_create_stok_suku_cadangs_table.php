<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_suku_cadangs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sparepart_id')
                ->constrained('spareparts')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('tipe', [
                'masuk',
                'keluar',
                'penyesuaian'
            ]);

            $table->integer('jumlah');

            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');

            $table->date('tanggal');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_suku_cadangs');
    }
};