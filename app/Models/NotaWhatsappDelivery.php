<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaWhatsappDelivery extends Model
{
    protected $table = 'nota_whatsapp_deliveries';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(
            NotaTransaksi::class,
            'nota_transaksi_id'
        );
    }
}