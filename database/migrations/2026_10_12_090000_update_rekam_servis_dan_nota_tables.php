<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. REKAM SERVIS
        |--------------------------------------------------------------------------
        */

        $adaKmAkhir = Schema::hasColumn('rekam_servis', 'km_akhir');
        $adaKmMasuk = Schema::hasColumn('rekam_servis', 'km_masuk');

        // Jangan memilih salah satu jika keduanya sudah ada.
        if ($adaKmAkhir && $adaKmMasuk) {
            throw new RuntimeException(
                'Kolom km_akhir dan km_masuk sama-sama tersedia. Periksa data sebelum melanjutkan.'
            );
        }

        // Ubah nama kolom lama tanpa menghilangkan data.
        if ($adaKmAkhir && ! $adaKmMasuk) {
            Schema::table('rekam_servis', function (Blueprint $table) {
                $table->renameColumn('km_akhir', 'km_masuk');
            });
        }

        if (! Schema::hasColumn('rekam_servis', 'km_masuk')) {
            throw new RuntimeException(
                'Kolom rekam_servis.km_masuk tidak tersedia.'
            );
        }

        // Tambahkan KM keluar jika belum tersedia.
        if (! Schema::hasColumn('rekam_servis', 'km_keluar')) {
            Schema::table('rekam_servis', function (Blueprint $table) {
                $table->unsignedInteger('km_keluar')
                    ->nullable()
                    ->after('km_masuk');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. NOTA TRANSAKSI
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('nota_transaksis', 'status_pembayaran')) {
            throw new RuntimeException(
                'Kolom nota_transaksis.status_pembayaran tidak tersedia.'
            );
        }

        // Pastikan status pembayaran menerima nilai alur terbaru.
        Schema::table('nota_transaksis', function (Blueprint $table) {
            $table->string('status_pembayaran')
                ->default('belum_lunas')
                ->change();
        });

        if (! Schema::hasColumn('nota_transaksis', 'tanggal_terbit_tagihan')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->dateTime('tanggal_terbit_tagihan')
                    ->nullable()
                    ->after('tanggal_cetak');
            });
        }

        if (! Schema::hasColumn('nota_transaksis', 'tanggal_lunas')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->dateTime('tanggal_lunas')
                    ->nullable()
                    ->after('status_pembayaran');
            });
        }

        if (! Schema::hasColumn('nota_transaksis', 'catatan_nota')) {
            Schema::table('nota_transaksis', function (Blueprint $table) {
                $table->text('catatan_nota')->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. DETAIL JASA
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('detail_jasas', 'qty')) {
            Schema::table('detail_jasas', function (Blueprint $table) {
                $table->unsignedInteger('qty')
                    ->default(1)
                    ->after('jasa_id');
            });
        }

        // Kolom diskon tidak ditambahkan kembali.
        // Penghapusannya ditangani migration 2026_10_12_144155.

        /*
        |--------------------------------------------------------------------------
        | 4. PEMBAYARAN NOTA
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasTable('pembayaran_notas')) {

            // Database baru, termasuk database CI.
            Schema::create('pembayaran_notas', function (Blueprint $table) {
                $table->id();

                // Tidak unique: satu nota dapat memiliki beberapa pembayaran.
                $table->foreignId('nota_transaksi_id')
                    ->constrained('nota_transaksis')
                    ->restrictOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('no_bukti', 100)
                    ->nullable()
                    ->unique();

                $table->decimal('jumlah_diterima', 12, 2);

                $table->decimal('jumlah_tagihan', 12, 2);

                $table->decimal('nominal_dialokasikan', 12, 2);

                $table->decimal('tips', 12, 2)
                    ->default(0);

                $table->decimal('kembalian', 12, 2)
                    ->default(0);

                $table->string('metode_pembayaran', 30);

                $table->dateTime('tanggal_bayar');

                $table->text('catatan')->nullable();

                $table->timestamps();
            });

        } else {

            // Database lama: pertahankan seluruh pembayaran historis.

            if (! Schema::hasColumn('pembayaran_notas', 'no_bukti')) {
                Schema::table('pembayaran_notas', function (Blueprint $table) {
                    $table->string('no_bukti', 100)
                        ->nullable()
                        ->unique();
                });
            }

            if (! Schema::hasColumn('pembayaran_notas', 'nominal_dialokasikan')) {

                // Tambahkan sebagai nullable agar data lama tetap bisa dibaca.
                Schema::table('pembayaran_notas', function (Blueprint $table) {
                    $table->decimal('nominal_dialokasikan', 12, 2)
                        ->nullable();
                });

                // Isi alokasi pembayaran historis.
                DB::table('pembayaran_notas')
                    ->whereNull('nominal_dialokasikan')
                    ->update([
                        'nominal_dialokasikan' => DB::raw('jumlah_tagihan')
                    ]);

                Schema::table('pembayaran_notas', function (Blueprint $table) {
                    $table->decimal('nominal_dialokasikan', 12, 2)
                        ->nullable(false)
                        ->change();
                });
            }

            // Periksa kolom wajib pada tabel yang sudah tersedia.
            $kolomWajib = [
                'nota_transaksi_id',
                'user_id',
                'jumlah_diterima',
                'jumlah_tagihan',
                'nominal_dialokasikan',
                'tips',
                'kembalian',
                'metode_pembayaran',
                'tanggal_bayar',
            ];

            foreach ($kolomWajib as $kolom) {
                if (! Schema::hasColumn('pembayaran_notas', $kolom)) {
                    throw new RuntimeException(
                        "Kolom pembayaran_notas.{$kolom} belum tersedia. "
                        . "Periksa struktur tabel pembayaran lama."
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. PENGELUARAN OPERASIONAL
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasTable('pengeluaran_operasional')) {
            Schema::create('pengeluaran_operasional', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->date('tanggal');

                $table->string('kategori', 100);

                $table->text('keterangan')->nullable();

                $table->decimal('jumlah', 12, 2);

                $table->string('metode_pembayaran', 30)
                    ->nullable();

                $table->timestamps();
            });
        }

        // status_rekomendasi ditangani migration terpisah:
        // 2026_10_18_090000_tambah_status_rekomendasi_pada_nota_transaksis.php
    }

    public function down(): void
    {
        /*
         * Rollback otomatis dinonaktifkan karena migration dapat
         * berjalan pada database yang sudah memiliki data historis.
         *
         * Menghapus tabel pembayaran atau mengembalikan nama kolom
         * secara otomatis berisiko merusak data transaksi.
         */

        throw new RuntimeException(
            'Rollback otomatis dinonaktifkan untuk melindungi data historis SIRAKA.'
        );
    }
};