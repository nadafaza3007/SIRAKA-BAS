<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanInspeksi extends Model
{
    protected $table = 'laporan_inspeksis';
    protected $guarded = [];

    protected $casts = [
        'tanggal_inspeksi' => 'date',
        'km_kendaraan' => 'integer',
        'rating_score' => 'integer',
    ];

    public function konsumen(): BelongsTo { return $this->belongsTo(Konsumen::class); }
    public function kendaraan(): BelongsTo { return $this->belongsTo(Kendaraan::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function kelistrikan(): HasMany {
        return $this->hasMany(InspeksiKelistrikan::class, 'laporan_inspeksi_id');
    }
    public function interior(): HasMany {
        return $this->hasMany(InspeksiInterior::class, 'laporan_inspeksi_id');
    }
    public function kakiKaki(): HasMany {
        return $this->hasMany(InspeksiKakiKaki::class, 'laporan_inspeksi_id');
    }
    public function exterior(): HasMany {
        return $this->hasMany(InspeksiExterior::class, 'laporan_inspeksi_id');
    }
    public function mesin(): HasMany {
        return $this->hasMany(InspeksiMesin::class, 'laporan_inspeksi_id');
    }
    public function dokumen(): HasMany {
        return $this->hasMany(InspeksiDokumen::class, 'laporan_inspeksi_id');
    }
    public function estimasi(): HasMany {
        return $this->hasMany(InspeksiEstimasi::class, 'laporan_inspeksi_id');
    }

    public static function generateNomorInspeksi(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $last = static::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->latest('id')
            ->first();
        $nomorUrut = $last ? (intval(substr($last->nomor_inspeksi, -4)) + 1) : 1;
        return 'INS/' . $tahun . '/' . $bulan . '/' . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalEstimasiAttribute(): float
    {
        return \DB::table('inspeksi_estimasi')
            ->where('laporan_inspeksi_id', $this->id)
            ->sum(\DB::raw('harga_part * qty + harga_jasa'));
    }
}