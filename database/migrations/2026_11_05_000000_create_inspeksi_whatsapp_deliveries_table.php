<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeksi_whatsapp_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('laporan_inspeksi_id')
                ->constrained('laporan_inspeksis')
                ->cascadeOnDelete();

            // Status pengiriman PDF inspeksi.
            $table->string('status', 20)->default('pending');

            // Jumlah percobaan pengiriman.
            $table->unsignedInteger('attempts')->default(0);

            // ID pesan dari Kirima API.
            $table->string('message_id')->nullable();

            // Keterangan jika terjadi kegagalan.
            $table->text('error_message')->nullable();

            // Waktu pengiriman berhasil diterima API.
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Satu laporan memiliki satu catatan riwayat.
            $table->unique(
                'laporan_inspeksi_id',
                'inspeksi_wa_laporan_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeksi_whatsapp_deliveries');
    }
};