<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailJasa extends Model
{
    protected $guarded = [];

    public function rekamServis()
    {
        return $this->belongsTo(RekamServis::class);
    }

    public function jasa()
    {
        return $this->belongsTo(Jasa::class);
    }
}

