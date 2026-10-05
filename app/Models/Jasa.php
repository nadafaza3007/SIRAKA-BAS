<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jasa extends Model
{
    protected $guarded = [];

    public function detailJasas()
    {
        return $this->hasMany(DetailJasa::class);
    }
}

