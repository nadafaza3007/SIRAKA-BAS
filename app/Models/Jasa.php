<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jasa extends Model
{
    use SoftDeletes;

    protected $table = 'jasas';

    protected $guarded = [];

    public function detailJasas(): HasMany
    {
        return $this->hasMany(DetailJasa::class, 'jasa_id');
    }
}