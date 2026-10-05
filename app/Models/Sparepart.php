<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $guarded = [];

    public function detailSpareparts()
    {
        return $this->hasMany(DetailSparepart::class);
    }

    public function marginKeuntungan(): float
    {
        return (float) ($this->harga_jual - $this->harga_modal);
    }

    public function marginPersentase(): float
    {
        if ($this->harga_modal <= 0) return 0;
        return round((($this->harga_jual - $this->harga_modal) / $this->harga_modal) * 100, 1);
    }
}

