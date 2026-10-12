<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->unsignedInteger('cc')->nullable()->after('tahun');
            $table->enum('transmisi', ['manual', 'otomatis', 'cvt'])
                  ->nullable()->after('cc');
            $table->enum('jenis_bahan_bakar', ['gasoline', 'diesel'])
                  ->nullable()->after('transmisi');
        });
    }

    public function down(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropColumn(['cc', 'transmisi', 'jenis_bahan_bakar']);
        });
    }
};