<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekamServis extends Model
{
    use HasFactory;

    protected $table = 'rekam_servis';

    protected $fillable = [
        'kendaraan_id',
        'km_akhir',
        'keluhan_awal',
        'diagnosa_awal',
        'diagnosa_akhir',
        'tindakan_servis',
        'tanggal_servis',
        'status',
        'user_id',
    ];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function nota()
    {
        return $this->hasOne(NotaTransaksi::class, 'rekam_servis_id');
    }

    public function catatanRekomendasis()
    {
        return $this->hasMany(CatatanRekomendasi::class, 'rekam_servis_id');
    }

    // Relasi ke Detail Sparepart pada Nota (menunjuk ke tabel detail_spareparts)
    public function detailSpareparts()
    {
        return $this->hasMany(DetailRekamServisSparepart::class, 'rekam_servis_id');
    }

    // Relasi ke Detail Jasa pada Nota (menunjuk ke tabel detail_jasas)
    public function detailJasas()
    {
        return $this->hasMany(DetailRekamServisJasa::class, 'rekam_servis_id');
    }

    // Hitung Subtotal Transaksi Otomatis
    public function subtotal()
    {
        $totalSparepart = $this->detailSpareparts->sum(function ($item) {
            return $item->harga_jual_saat_transaksi * $item->qty;
        });

        $totalJasa = $this->detailJasas->sum('harga_saat_transaksi');

        return $totalSparepart + $totalJasa;
    }
}