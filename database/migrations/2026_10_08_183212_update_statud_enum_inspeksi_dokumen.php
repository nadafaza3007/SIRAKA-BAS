<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ubah ENUM status di inspeksi_dokumen agar menerima semua nilai
        DB::statement("ALTER TABLE inspeksi_dokumen MODIFY COLUMN status ENUM('ya', 'tidak', 'n/a', 'Baik', 'Cukup', 'Buruk', 'N/A') NOT NULL DEFAULT 'n/a'");
    }

    public function down(): void
    {
        // Kembalikan ke ENUM semula
        DB::statement("ALTER TABLE inspeksi_dokumen MODIFY COLUMN status ENUM('ya', 'tidak', 'n/a') NOT NULL DEFAULT 'n/a'");
    }
};