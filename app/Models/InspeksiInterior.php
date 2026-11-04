<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiInterior extends Model
{
    protected $table = 'inspeksi_interior';
    protected $guarded = []; // WAJIB!

    public function laporanInspeksi(): BelongsTo {
        return $this->belongsTo(LaporanInspeksi::class, 'laporan_inspeksi_id');
    }
}