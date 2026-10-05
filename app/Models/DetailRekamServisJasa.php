<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailRekamServisJasa extends Model
{
    // Sesuaikan dengan nama tabel di database Anda
    protected $table = 'detail_jasas';
    protected $guarded = [];

    public function jasa()
    {
        return $this->belongsTo(Jasa::class, 'jasa_id');
    }
}