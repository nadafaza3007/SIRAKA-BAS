<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    protected $table = 'spareparts';

    // Dipertahankan dari kode utama agar proses admin/anggota
    // yang sudah berjalan tidak berubah.
    protected $guarded = [];

    public function detailSpareparts(): HasMany
    {
        return $this->hasMany(
            DetailSparepart::class,
            'sparepart_id'
        );
    }

    /**
     * Hitung margin keuntungan per sparepart.
     */
    public function marginKeuntungan(): float
    {
        return (float) (
            $this->harga_jual - $this->harga_modal
        );
    }

    /**
     * Hitung persentase margin terhadap harga modal.
     */
    public function marginPersentase(): float
    {
        if ((float) $this->harga_modal <= 0) {
            return 0;
        }

        return round(
            (
                ($this->harga_jual - $this->harga_modal)
                / $this->harga_modal
            ) * 100,
            1
        );
    }
}