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
    public const BATAS_BULAN_SERVIS = 6;

    protected $table = 'kendaraans';
    protected $guarded = [];

    protected $casts = [
        'cc'                 => 'integer',
        'transmisi'          => 'string',
        'jenis_bahan_bakar'  => 'string',
        'tahun'              => 'integer',
    ];

    public function konsumen(): BelongsTo
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function rekamServis(): HasMany
    {
        return $this->hasMany(RekamServis::class, 'kendaraan_id');
    }

    public function servisTerakhir(): HasOne
    {
        return $this->hasOne(RekamServis::class, 'kendaraan_id')
            ->ofMany([
                'tanggal_servis' => 'max',
                'id' => 'max',
            ]);
    }

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

    public function rekomendasiNotaTerbuka(): HasManyThrough
    {
        return $this->hasManyThrough(
            NotaTransaksi::class,
            RekamServis::class,
            'kendaraan_id',
            'rekam_servis_id',
            'id',
            'id'
        )->where('status_rekomendasi', 'belum')
         ->whereNotNull('catatan_nota')
         ->where('catatan_nota', '<>', '')
         ->orderByDesc('nota_transaksis.id');
    }

    public function scopeDenganStatus(Builder $query): Builder
    {
        return $query
            ->with(['servisTerakhir', 'rekomendasiNotaTerbuka'])
            ->withCount('rekamServis');
    }

    public function getNamaAttribute(): string
    {
        return trim(
            ($this->merk ?? '') . ' ' . ($this->tipe_model ?? '')
        );
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->nama
            . ($this->tahun ? ' ' . $this->tahun : '');
    }

    public function getTransmisiLabelAttribute(): string
    {
        return match ($this->transmisi) {
            'manual'   => 'Manual',
            'otomatis' => 'Otomatis',
            'cvt'      => 'CVT',
            default    => '-',
        };
    }

    public function getBahanBakarLabelAttribute(): string
    {
        return match ($this->jenis_bahan_bakar) {
            'gasoline' => 'Gasoline',
            'diesel'   => 'Diesel',
            default    => '-',
        };
    }

    /**
     * ✅ Format CC dengan koma (Liter)
     * Contoh: 2200 → "2,2 L"
     */
    public function getCcFormatAttribute(): string
{
    if (!$this->cc) {
        return '-';
    }

    // Format desimal dengan koma + suffix L
    return number_format($this->cc, 1, ',', '') . ' L';
}

    public function getStatusServisAttribute(): string
    {
        $terakhir = $this->servisTerakhir;

        if (! $terakhir) {
            return 'belum_servis';
        }

        if ($terakhir->status !== 'selesai') {
            return 'dikerjakan';
        }

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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_servis) {
            'baik'         => 'Terpantau baik',
            'perlu_servis' => 'Perlu servis',
            'dikerjakan'   => 'Sedang diproses',
            default        => 'Belum ada riwayat',
        };
    }

    public function getStatusToneAttribute(): string
    {
        return match ($this->status_servis) {
            'baik'         => 'green',
            'perlu_servis' => 'amber',
            'dikerjakan'   => 'blue',
            default        => 'gray',
        };
    }

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