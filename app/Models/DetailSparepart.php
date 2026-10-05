<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailSparepart extends Model
{
    protected $guarded = [];

    public function rekamServis()
    {
        return $this->belongsTo(RekamServis::class);
    }

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }
}

