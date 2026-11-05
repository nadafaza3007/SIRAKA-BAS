<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiWhatsappDelivery extends Model
{
    protected $table = 'inspeksi_whatsapp_deliveries';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function inspeksi(): BelongsTo
    {
        return $this->belongsTo(
            LaporanInspeksi::class,
            'laporan_inspeksi_id'
        );
    }
}