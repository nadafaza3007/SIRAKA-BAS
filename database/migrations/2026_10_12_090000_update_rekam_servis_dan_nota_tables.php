<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kompatibel dengan dua keadaan:
     * - database baru hasil migration awal (untuk CI);
     * - database Laragon yang beberapa perubahan strukturnya sudah diterapkan.
     * Tidak menghapus data historis.
     */
    public function up(): void
    {
        if (
            Schema::hasColumn('rekam_servis', 'km_akhir') && 
            ! Schema::hasColumn('rekam_servis', 'km_masuk')
            ) {
                Schema::table('rekam_servis', function (Blueprint $table) {
                    $table->renameColumn('km_akhir', 'km_masuk');
                });
            }

        if (! Schema::hasColumn('rekam_servis', 'km_masuk')) {
            throw new \RuntimeException(
                'Kolom rekam_servis.km_masuk tidak tersedia; periksa migration sebelumnya.'
            );
        }

        if (! Schema::hasColumn('rekam_servis', 'km_keluar')) {
            Schema::table('rekam_servis', function (Blueprint $table) {
                $table->unsignedInteger('km_keluar')->nullable()->after('km_masuk');
            });
        }

        Schema::table('nota_transaksis', function (Blueprint $table) {
            $table->string('status_pembayaran')->default('belum_lunas')->change();
        });

        if (! Schema::hasColumn('nota_transaksis', 'tanggal_terbit_tagihan')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->dateTime('tanggal_terbit_tagihan')->nullable()->after('tanggal_cetak');
            });
        }
        if (! Schema::hasColumn('nota_transaksis', 'tanggal_lunas')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->dateTime('tanggal_lunas')->nullable()->after('status_pembayaran');
            });
        }
        if (! Schema::hasColumn('nota_transaksis', 'catatan_nota')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->text('catatan_nota')->nullable();
            });
        }

        if (! Schema::hasColumn('detail_jasas', 'qty')) {
            Schema::table('detail_jasas', function (Blueprint $table) {
                $table->unsignedInteger('qty')->default(1)->after('jasa_id');
            });
        }
        // Migration berikutnya, 2026_10_12_144155, menghapus diskon.
        // Tidak perlu menambahkan diskon di sini karena tidak dipakai fitur final.

        if (! Schema::hasTable('pembayaran_notas')) {
            Schema::create('pembayaran_notas', function (Blueprint $table) {
                $table->id();
                // Tanpa unique: satu nota dapat dibayar beberapa kali.
                $table->foreignId('nota_transaksi_id')
                    ->constrained('nota_transaksis')->restrictOnDelete();
                $table->foreignId('user_id')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->string('no_bukti', 100)->nullable()->unique();
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
        } else {
            // Penambahan kolom aman untuk tabel lama yang sudah ada.
            if (! Schema::hasColumn('pembayaran_notas', 'no_bukti')) {
                Schema::table('pembayaran_notas', function (Blueprint $table) {
                    $table->string('no_bukti', 100)->nullable()->unique();
                });
            }
            if (! Schema::hasColumn('pembayaran_notas', 'nominal_dialokasikan')) {
                Schema::table('pembayaran_notas', function (Blueprint $table) {
                    $table->decimal('nominal_dialokasikan', 12, 2)->nullable();
                });
                // Alokasi transaksi lama mengacu pada nilai tagihan yang dibayar.
                \Illuminate\Support\Facades\DB::table('pembayaran_notas')
                    ->whereNull('nominal_dialokasikan')
                    ->update(['nominal_dialokasikan' => \Illuminate\Support\Facades\DB::raw('jumlah_tagihan')]);
                Schema::table('pembayaran_notas', function (Blueprint $table) {
                    $table->decimal('nominal_dialokasikan', 12, 2)->nullable(false)->change();
                });
            }
        }

        if (! Schema::hasTable('pengeluaran_operasional')) {
            Schema::create('pengeluaran_operasional', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->date('tanggal');
                $table->string('kategori', 100);
                $table->text('keterangan')->nullable();
                $table->decimal('jumlah', 12, 2);
                $table->string('metode_pembayaran', 30)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Perubahan pada database lama mungkin sudah ada SEBELUM migration ini.
        // Rollback otomatis dapat menghapus data pembayaran dan histori KM.
        throw new \RuntimeException('Rollback otomatis dinonaktifkan untuk melindungi data SIRAKA. Pulihkan dari backup dengan prosedur terencana.');
    }
};
