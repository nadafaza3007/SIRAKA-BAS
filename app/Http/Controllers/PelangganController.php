<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\RekamServis;
use App\Models\Konsumen;
use App\Models\NotaTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelangganController extends Controller
{
    public function rekamServis(Request $request)
    {
        $user = Auth::user();
        
        // Cari konsumen yang terkait dengan user
        $konsumen = null;
        if ($user->konsumen_id) {
            $konsumen = Konsumen::with(['kendaraans.rekamServis.nota', 'kendaraans.rekamServis.detailSpareparts.sparepart', 'kendaraans.rekamServis.detailJasas.jasa', 'kendaraans.rekamServis.catatanRekomendasis'])->find($user->konsumen_id);
        }

        if (!$konsumen && $user->no_hp) {
            $konsumen = Konsumen::with(['kendaraans.rekamServis.nota', 'kendaraans.rekamServis.detailSpareparts.sparepart', 'kendaraans.rekamServis.detailJasas.jasa', 'kendaraans.rekamServis.catatanRekomendasis'])->where('no_hp', $user->no_hp)->first();
        }

        $kendaraans = $konsumen ? $konsumen->kendaraans : collect([]);

        // Filter jika pelanggan mencari nomor plat tertentu
        $searchPlat = $request->query('plat');
        if ($searchPlat) {
            $kendaraans = $kendaraans->filter(function ($k) use ($searchPlat) {
                return str_contains(strtoupper(str_replace(' ', '', $k->plat_nomor)), strtoupper(str_replace(' ', '', $searchPlat)));
            });
        }

        return view('pelanggan.rekam-servis', compact('user', 'konsumen', 'kendaraans', 'searchPlat'));
    }

    public function detailServis($id)
    {
        $servis = RekamServis::with([
            'kendaraan.konsumen',
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
            'catatanRekomendasis',
            'nota'
        ])->findOrFail($id);

        $user = Auth::user();
        // Validasi kepemilikan data kendaraan
        if ($user->konsumen_id && $servis->kendaraan->konsumen_id != $user->konsumen_id) {
            return redirect()->route('pelanggan.rekam-servis')->with('error', 'Anda tidak memiliki otorisasi melihat rekam kendaraan ini.');
        }

        return view('pelanggan.detail', compact('servis'));
    }
}

