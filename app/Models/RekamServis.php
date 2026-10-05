<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class RekamServis extends Model
{
    use HasFactory;

    protected $table = 'rekam_servis';

    /**
     * Dipertahankan dari kode utama.
     */
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

    /**
     * Label dan warna status untuk tampilan pelanggan.
     */
    private const STATUS = [
        'menunggu_pengerjaan' => ['Menunggu pengerjaan', 'gray'],
        'sedang_dikerjakan'   => ['Sedang dikerjakan', 'blue'],
        'dikerjakan'          => ['Sedang dikerjakan', 'blue'],
        'proses'              => ['Sedang dikerjakan', 'blue'],
        'selesai'             => ['Selesai', 'green'],
        'dibatalkan'          => ['Dibatalkan', 'red'],
        'batal'               => ['Dibatalkan', 'red'],
    ];

    /**
     * Status yang menandakan servis sudah tidak berjalan.
     */
    private const STATUS_FINAL = [
        'selesai',
        'dibatalkan',
        'batal',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(
            Kendaraan::class,
            'kendaraan_id'
        );
    }

    /**
     * User/admin/anggota yang menangani servis.
     *
     * Tambahan ini dibutuhkan halaman pelanggan
     * ketika menggunakan ->with('user:id,name').
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function nota(): HasOne
    {
        return $this->hasOne(
            NotaTransaksi::class,
            'rekam_servis_id'
        );
    }

    public function catatanRekomendasis(): HasMany
    {
        return $this->hasMany(
            CatatanRekomendasi::class,
            'rekam_servis_id'
        );
    }

    /**
     * Relasi detail sparepart.
     */
    public function detailSpareparts(): HasMany
    {
        return $this->hasMany(
            DetailSparepart::class,
            'rekam_servis_id'
        );
    }

    /**
     * Relasi detail jasa.
     */
    public function detailJasas(): HasMany
    {
        return $this->hasMany(
            DetailJasa::class,
            'rekam_servis_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Perhitungan transaksi
    |--------------------------------------------------------------------------
    */

    /**
     * Hitung subtotal transaksi.
     *
     * Fungsi utama dipertahankan.
     */
    public function subtotal()
    {
        $totalSparepart = $this->detailSpareparts->sum(
            function ($item) {
                return
                    (float) $item->harga_jual_saat_transaksi
                    * (int) $item->qty;
            }
        );

        $totalJasa = $this->detailJasas->sum(
            'harga_saat_transaksi'
        );

        return $totalSparepart + $totalJasa;
    }

    /**
     * Hitung total modal sparepart.
     *
     * Dibutuhkan oleh NotaTransaksi::totalModalSparepart().
     */
    public function totalModalSparepart(): float
    {
        return (float) $this->detailSpareparts->sum(
            function ($item) {
                return
                    (float) $item->harga_modal_saat_transaksi
                    * (int) $item->qty;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Status
    |--------------------------------------------------------------------------
    */

    /**
     * Apakah servis masih berjalan.
     */
    public function isBerjalan(): bool
    {
        return ! in_array(
            $this->status,
            self::STATUS_FINAL,
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor untuk Tampilan Pelanggan
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status][0]
            ?? Str::headline((string) $this->status);
    }

    public function getStatusToneAttribute(): string
    {
        return self::STATUS[$this->status][1]
            ?? 'gray';
    }

    public function getTanggalLabelAttribute(): string
    {
        if (! $this->tanggal_servis) {
            return '-';
        }

        return Carbon::parse($this->tanggal_servis)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    public function getTanggalSingkatAttribute(): string
    {
        if (! $this->tanggal_servis) {
            return '-';
        }

        return Carbon::parse($this->tanggal_servis)
            ->locale('id')
            ->translatedFormat('d M Y');
    }

    /**
     * Estimasi dari rincian jasa + sparepart.
     * Digunakan bila nota belum tersedia.
     */
    public function getEstimasiBiayaAttribute(): float
    {
        return (float) $this->subtotal();
    }

    /**
     * Jika nota sudah ada gunakan total dari nota.
     * Jika belum, gunakan estimasi rincian transaksi.
     */
    public function getTotalBiayaAttribute(): float
    {
        if ($this->nota) {
            return (float) $this->nota->total_biaya;
        }

        return $this->estimasi_biaya;
    }
}