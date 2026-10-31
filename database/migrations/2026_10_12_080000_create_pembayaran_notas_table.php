<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pembayaran_notas')) {
            Schema::create('pembayaran_notas', function (Blueprint $table) {
                $table->id();

                $table->foreignId('nota_transaksi_id')
                    ->constrained('nota_transaksis')
                    ->restrictOnDelete();

                $table->string('no_bukti', 100)
                    ->nullable()
                    ->unique();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->decimal('jumlah_diterima', 12, 2);
                $table->decimal('jumlah_tagihan', 12, 2);
                $table->decimal('nominal_dialokasikan', 12, 2);

                $table->decimal('tips', 12, 2)->default(0);
                $table->decimal('kembalian', 12, 2)->default(0);

                $table->string('metode_pembayaran', 30);
                $table->dateTime('tanggal_bayar');

                $table->text('catatan')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Tidak menghapus tabel pembayaran historis secara otomatis.
    }
};