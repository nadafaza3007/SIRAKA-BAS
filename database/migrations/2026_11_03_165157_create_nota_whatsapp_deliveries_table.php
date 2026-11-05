
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_whatsapp_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nota_transaksi_id')
                ->constrained('nota_transaksis')
                ->cascadeOnDelete();

            // tagihan_awal atau nota_lunas
            $table->string('jenis', 30);

            // pending, sending, sent, failed
            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('attempts')->default(0);

            $table->string('message_id')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Satu jenis nota hanya memiliki satu catatan pengiriman.
            $table->unique(
                ['nota_transaksi_id', 'jenis'],
                'nota_wa_nota_jenis_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_whatsapp_deliveries');
    }
};