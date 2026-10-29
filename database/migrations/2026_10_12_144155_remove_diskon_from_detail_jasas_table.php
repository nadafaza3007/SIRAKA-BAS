
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus hanya jika kolom masih ada.
        if (Schema::hasColumn('detail_jasas', 'diskon')) {
            Schema::table('detail_jasas', function (Blueprint $table) {
                $table->dropColumn('diskon');
            });
        }
    }

    public function down(): void
    {
        // Kembalikan kolom saat rollback.
        if (!Schema::hasColumn('detail_jasas', 'diskon')) {
            Schema::table('detail_jasas', function (Blueprint $table) {
                $table->decimal('diskon', 12, 2)->default(0);
            });
        }
    }
};
