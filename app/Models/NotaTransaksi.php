<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaTransaksi extends Model
{
    protected $table = 'nota_transaksis';

    // Dipertahankan dari kode utama agar proses admin/anggota
    // yang sudah berjalan tidak berubah.
    protected $guarded = [];

    public function rekamServis(): BelongsTo
    {
        return $this->belongsTo(RekamServis::class, 'rekam_servis_id');
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
     *
     * Dipertahankan dari kode utama agar pemanggilan
     * pada halaman admin/anggota tidak terganggu.
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