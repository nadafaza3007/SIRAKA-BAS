<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, make cc nullable to avoid data truncation issues
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->decimal('cc', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->unsignedInteger('cc')->nullable()->change();
        });
    }
};