<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranNota extends Model
{
    protected $table = 'pembayaran_notas';

    protected $fillable = [
        'nota_transaksi_id',
        'user_id',
        'no_bukti',
        'jumlah_diterima',
        'jumlah_tagihan',
        'nominal_dialokasikan',
        'tips',
        'kembalian',
        'metode_pembayaran',
        'tanggal_bayar',
        'catatan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'jumlah_diterima' => 'decimal:2',
        'jumlah_tagihan' => 'decimal:2',
        'nominal_dialokasikan' => 'decimal:2',
        'tips' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(
            NotaTransaksi::class,
            'nota_transaksi_id'
        );
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
