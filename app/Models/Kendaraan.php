<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kendaraan extends Model
{
    /**
     * Kendaraan dianggap perlu servis bila servis terakhir
     * sudah lebih dari 6 bulan.
     */
    public const BATAS_BULAN_SERVIS = 6;

    protected $table = 'kendaraans';

    /**
     * Dipertahankan dari kode utama agar proses
     * admin/anggota yang sudah ada tidak berubah.
     */
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function konsumen(): BelongsTo
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function rekamServis(): HasMany
    {
        return $this->hasMany(RekamServis::class, 'kendaraan_id');
    }

    /**
     * Mengambil servis terakhir kendaraan.
     */
    public function servisTerakhir(): HasOne
    {
        return $this->hasOne(RekamServis::class, 'kendaraan_id')
            ->ofMany([
                'tanggal_servis' => 'max',
                'id' => 'max',
            ]);
    }

    /**
     * Semua catatan rekomendasi yang dimiliki kendaraan
     * melalui rekam servis.
     */
    public function catatanRekomendasis(): HasManyThrough
    {
        return $this->hasManyThrough(
            CatatanRekomendasi::class,
            RekamServis::class,
            'kendaraan_id',
            'rekam_servis_id',
            'id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Memuat data yang diperlukan untuk menentukan
     * status kendaraan pada sisi pelanggan.
     */
    public function scopeDenganStatus(Builder $query): Builder
    {
        return $query
            ->with('servisTerakhir')
            ->withCount('rekamServis');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    /**
     * Contoh:
     * Toyota Avanza Veloz
     */
    public function getNamaAttribute(): string
    {
        return trim(
            ($this->merk ?? '') . ' ' . ($this->tipe_model ?? '')
        );
    }

    /**
     * Contoh:
     * Toyota Avanza Veloz 2021
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->nama
            . ($this->tahun ? ' ' . $this->tahun : '');
    }

    /**
     * Status:
     * baik
     * perlu_servis
     * dikerjakan
     * belum_servis
     */
    public function getStatusServisAttribute(): string
    {
        $terakhir = $this->servisTerakhir;

        if (! $terakhir) {
            return 'belum_servis';
        }

        /**
         * Selama servis terakhir belum selesai,
         * kendaraan dianggap masih dalam proses.
         */
        if ($terakhir->status !== 'selesai') {
            return 'dikerjakan';
        }

        /**
         * Cek apakah servis terakhir sudah lebih
         * dari batas servis berkala.
         */
        $tanggalServis = Carbon::parse(
            $terakhir->tanggal_servis
        );

        if (
            $tanggalServis->lt(
                now()->subMonths(self::BATAS_BULAN_SERVIS)
            )
        ) {
            return 'perlu_servis';
        }

        return 'baik';
    }

    /**
     * Label status untuk ditampilkan ke pelanggan.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_servis) {
            'baik' => 'Terpantau baik',
            'perlu_servis' => 'Perlu servis',
            'dikerjakan' => 'Sedang diproses',
            default => 'Belum ada riwayat',
        };
    }

    /**
     * Tone untuk kebutuhan tampilan badge.
     */
    public function getStatusToneAttribute(): string
    {
        return match ($this->status_servis) {
            'baik' => 'green',
            'perlu_servis' => 'amber',
            'dikerjakan' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Alasan kendaraan membutuhkan perhatian.
     */
    public function getAlasanPerhatianAttribute(): ?string
    {
        $terakhir = $this->servisTerakhir;

        if (! $terakhir) {
            return null;
        }

        if ($this->status_servis === 'dikerjakan') {
            return 'Servis kendaraan masih dalam proses.';
        }

        if ($this->status_servis === 'perlu_servis') {
            $tanggalServis = Carbon::parse(
                $terakhir->tanggal_servis
            );

            return 'Servis terakhir '
                . $tanggalServis
                    ->copy()
                    ->locale('id')
                    ->diffForHumans()
                . '. Disarankan servis berkala setiap '
                . self::BATAS_BULAN_SERVIS
                . ' bulan.';
        }

        return null;
    }
}