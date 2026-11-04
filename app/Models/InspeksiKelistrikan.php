<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiKelistrikan extends Model
{
    protected $table = 'inspeksi_kelistrikan';
    protected $guarded = []; // WAJIB!

    public function laporanInspeksi(): BelongsTo {
        return $this->belongsTo(LaporanInspeksi::class, 'laporan_inspeksi_id');
    }
}