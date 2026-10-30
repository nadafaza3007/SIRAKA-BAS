<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaTransaksi extends Model
{
    protected $table = 'nota_transaksis';

    protected $guarded = [];

    protected $casts = [
        'tanggal_lunas' => 'datetime',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    // Satu nota dapat memiliki banyak pembayaran/cicilan.
    public function pembayaran(): HasMany
    {
        return $this->hasMany(
            PembayaranNota::class,
            'nota_transaksi_id'
        );
    }

    public function getTotalDibayarAttribute(): float
    {
        return (float) $this->pembayaran()
            ->sum('nominal_dialokasikan');
    }

    public function getSisaTagihanAttribute(): float
    {
        return max(
            0,
            (float) $this->total_biaya
                - $this->total_dibayar
        );
    }

    public function getStatusPembayaranLabelAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'LUNAS',
            'dibayar_sebagian' => 'DIBAYAR SEBAGIAN',
            default => 'BELUM LUNAS',
        };
    }

    public function rekamServis(): BelongsTo
    {
        return $this->belongsTo(
            RekamServis::class,
            'rekam_servis_id'
        );
    }

    /**
     * Hitung total modal sparepart dalam nota ini.
     */
    public function totalModalSparepart(): float
    {
        if (! $this->rekamServis) {
            return 0;
        }

        return $this->rekamServis->totalModalSparepart();
    }

    /**
     * Hitung laba dari transaksi ini.
     */
    public function labaBersih(): float
    {
        return (float) $this->total_biaya
            - $this->totalModalSparepart();
    }

    /**
     * Label tanggal untuk tampilan pelanggan.
     */
    public function getTanggalLabelAttribute(): string
    {
        if (! $this->tanggal_cetak) {
            return '-';
        }

        return Carbon::parse($this->tanggal_cetak)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    /**
     * Mengecek apakah nota sudah lunas.
     */
    public function getLunasAttribute(): bool
    {
        return $this->status_pembayaran === 'lunas';
    }
}
