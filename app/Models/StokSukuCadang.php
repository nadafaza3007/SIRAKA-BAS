<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokSukuCadang extends Model
{
    protected $table = 'stok_suku_cadangs';

    protected $fillable = [
        'sparepart_id',
        'user_id',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}