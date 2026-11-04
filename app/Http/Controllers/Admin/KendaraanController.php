<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use Illuminate\Http\JsonResponse;

class KendaraanController extends Controller
{
    /**
     * API pencarian kendaraan untuk halaman riwayat servis.
     *
     * Respons dipertahankan sama persis dengan closure route sebelumnya,
     * termasuk nilai km_terakhir = 50000 untuk kompatibilitas.
     * Perubahan data/perhitungan KM dapat dilakukan pada tahap fitur tersendiri.
     */
    public function searchApi(): JsonResponse
    {
        $data = Kendaraan::with('konsumen')->get()->map(function ($k) {
            return [
                'id'            => $k->id,
                'plat_nomor'    => $k->plat_nomor,
                'merk'          => $k->merk,
                'tipe_model'    => $k->tipe_model,
                'konsumen_nama' => $k->konsumen ? $k->konsumen->nama_lengkap : '-',
                'km_terakhir'   => 50000,
            ];
        });

        return response()->json($data);
    }
}