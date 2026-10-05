<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Konsumen extends Model
{
    protected $table = 'konsumens';

    // Dipertahankan dari kode utama agar proses admin/anggota
    // yang sudah berjalan tidak berubah.
    protected $guarded = [];

    public function kendaraans(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'konsumen_id');
    }
}