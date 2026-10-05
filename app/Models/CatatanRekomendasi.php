<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanRekomendasi extends Model
{
    use HasFactory;

    protected $table = 'catatan_rekomendasis';

    protected $fillable = [
        'rekam_servis_id',
        'catatan',
    ];
}