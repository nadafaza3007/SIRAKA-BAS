<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiEstimasi extends Model
{
    protected $table = 'inspeksi_estimasi';
    protected $guarded = []; // WAJIB!

    protected $casts = [
        'harga_part' => 'decimal:2',
        'harga_jasa' => 'decimal:2',
    ];

    public function laporanInspeksi(): BelongsTo {
        return $this->belongsTo(LaporanInspeksi::class, 'laporan_inspeksi_id');
    }
    public function sparepart(): BelongsTo {
        return $this->belongsTo(Sparepart::class);
    }
    public function jasa(): BelongsTo {
        return $this->belongsTo(Jasa::class);
    }
    public function getTotalAttribute(): float {
        return ($this->harga_part * $this->qty) + $this->harga_jasa;
    }
}