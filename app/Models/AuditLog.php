<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function catat($aktor, $entitas, $aksi, $nilaiLama = null, $nilaiBaru = null, $userId = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'aktor' => $aktor,
            'entitas' => $entitas,
            'aksi' => $aksi,
            'nilai_lama' => is_array($nilaiLama) ? json_encode($nilaiLama) : $nilaiLama,
            'nilai_baru' => is_array($nilaiBaru) ? json_encode($nilaiBaru) : $nilaiBaru,
        ]);
    }
}

