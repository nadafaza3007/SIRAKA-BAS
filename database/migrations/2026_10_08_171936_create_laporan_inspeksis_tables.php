<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabel utama laporan inspeksi
        Schema::create('laporan_inspeksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsumen_id')->constrained('konsumens')->cascadeOnDelete();
            $table->foreignId('kendaraan_id')->constrained('kendaraans')->cascadeOnDelete();
            $table->string('nomor_inspeksi')->unique();
            $table->date('tanggal_inspeksi');
            $table->integer('km_kendaraan');
            $table->integer('rating_score')->default(0);
            $table->enum('ex_banjir', ['ya', 'tidak'])->default('tidak');
            $table->enum('ex_tabrak', ['ya', 'tidak'])->default('tidak');

            // Kondisi umum
            $table->enum('kondisi_mesin', ['baik', 'cukup', 'buruk'])->nullable();
            $table->enum('kondisi_transmisi', ['baik', 'cukup', 'buruk'])->nullable();
            $table->enum('kondisi_kaki_kaki', ['baik', 'cukup', 'buruk'])->nullable();
            $table->enum('kondisi_eksterior', ['baik', 'cukup', 'buruk'])->nullable();
            $table->enum('kondisi_interior', ['baik', 'cukup', 'buruk'])->nullable();

            $table->text('catatan_umum')->nullable();
            $table->text('saran')->nullable();

            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['draft', 'selesai'])->default('draft');
            $table->timestamps();
        });

        // Detail inspeksi kelistrikan & interior
        Schema::create('inspeksi_kelistrikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->string('komponen');
            $table->enum('status', ['baik', 'cukup', 'buruk', 'n/a']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail inspeksi interior
        Schema::create('inspeksi_interior', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->string('komponen');
            $table->enum('status', ['baik', 'cukup', 'buruk', 'n/a']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail inspeksi kaki-kaki
        Schema::create('inspeksi_kaki_kaki', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->string('komponen');
            $table->enum('status_kiri', ['baik', 'cukup', 'buruk', 'n/a']);
            $table->enum('status_kanan', ['baik', 'cukup', 'buruk', 'n/a']);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Detail inspeksi exterior
        Schema::create('inspeksi_exterior', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->string('komponen');
            $table->enum('status', ['baik', 'cukup', 'buruk', 'n/a']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail inspeksi mesin & transmisi
        Schema::create('inspeksi_mesin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->string('komponen');
            $table->enum('status', ['baik', 'cukup', 'buruk', 'n/a']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail inspeksi dokumen
        Schema::create('inspeksi_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->string('dokumen');
            $table->enum('status', ['ya', 'tidak', 'n/a']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Estimasi perbaikan (rekomendasi sparepart & jasa)
        Schema::create('inspeksi_estimasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_inspeksi_id')->constrained('laporan_inspeksis')->cascadeOnDelete();
            $table->enum('kategori', ['engine', 'kaki_kaki', 'kelistrikan', 'exterior', 'interior']);
            $table->foreignId('sparepart_id')->nullable()->constrained('spareparts')->nullOnDelete();
            $table->foreignId('jasa_id')->nullable()->constrained('jasas')->nullOnDelete();
            $table->string('nama_item'); // Untuk custom item
            $table->integer('qty')->default(1);
            $table->decimal('harga_part', 15, 2)->default(0);
            $table->decimal('harga_jasa', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeksi_estimasi');
        Schema::dropIfExists('inspeksi_dokumen');
        Schema::dropIfExists('inspeksi_mesin');
        Schema::dropIfExists('inspeksi_exterior');
        Schema::dropIfExists('inspeksi_kaki_kaki');
        Schema::dropIfExists('inspeksi_interior');
        Schema::dropIfExists('inspeksi_kelistrikan');
        Schema::dropIfExists('laporan_inspeksis');
    }
};