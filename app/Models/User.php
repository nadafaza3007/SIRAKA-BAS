<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'name',
        'username',
        'no_hp',
        'password',
        'role',
        'konsumen_id',
    ];

    /**
     * Kolom yang tidak ditampilkan ketika model
     * dikonversi menjadi array / JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cek apakah user adalah administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah anggota / mekanik.
     */
    public function isAnggota(): bool
    {
        return $this->role === 'anggota';
    }

    /**
     * Cek apakah user adalah pelanggan.
     */
    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }

    /**
     * Relasi user pelanggan ke data konsumen.
     */
    public function konsumen(): BelongsTo
    {
        return $this->belongsTo(
            Konsumen::class,
            'konsumen_id'
        );
    }
}