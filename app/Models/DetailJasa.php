<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailJasa extends Model
{
    protected $table = 'detail_jasas';

    protected $fillable = [
        'rekam_servis_id',
        'jasa_id',
        'harga_saat_transaksi',
    ];

    public function rekamServis(): BelongsTo
    {
        return $this->belongsTo(RekamServis::class, 'rekam_servis_id');
    }

    public function jasa(): BelongsTo
    {
        return $this->belongsTo(Jasa::class, 'jasa_id');
    }
}