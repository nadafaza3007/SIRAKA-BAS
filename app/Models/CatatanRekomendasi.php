<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanRekomendasi extends Model
{
    use HasFactory;

    protected $table = 'catatan_rekomendasis';

    protected $fillable = [
        'rekam_servis_id',
        'catatan',
    ];

    /**
     * Relasi catatan rekomendasi ke rekam servis.
     */
    public function rekamServis(): BelongsTo
    {
        return $this->belongsTo(RekamServis::class, 'rekam_servis_id');
    }
}