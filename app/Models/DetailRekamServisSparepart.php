<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailRekamServisSparepart extends Model
{
    // Sesuaikan dengan nama tabel di database Anda
    protected $table = 'detail_spareparts';
    protected $guarded = [];

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'sparepart_id');
    }
}