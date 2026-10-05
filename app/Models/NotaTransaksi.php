<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaTransaksi extends Model
{
    protected $guarded = [];

    public function rekamServis()
    {
        return $this->belongsTo(RekamServis::class);
    }

    /**
     * Hitung total modal sparepart dalam nota ini
     */
    public function totalModalSparepart(): float
    {
        if (!$this->rekamServis) return 0;
        return $this->rekamServis->totalModalSparepart();
    }

    /**
     * Hitung laba kotor dari transaksi ini
     */
    public function labaBersih(): float
    {
        return (float) $this->total_biaya - $this->totalModalSparepart();
    }
}

