<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailSparepart extends Model
{
    protected $table = 'detail_spareparts';

    // Dipertahankan dari kode utama agar proses admin/anggota
    // yang sudah ada tidak terganggu.
    protected $guarded = [];

    public function rekamServis(): BelongsTo
    {
        return $this->belongsTo(RekamServis::class, 'rekam_servis_id');
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class, 'sparepart_id');
    }
}