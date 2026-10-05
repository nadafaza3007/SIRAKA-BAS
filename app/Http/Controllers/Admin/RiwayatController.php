<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\Konsumen;
use App\Models\RekamServis;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));
        $kendaraans = collect([]);
        $selectedKendaraan = null;

        if ($query !== '') {
            $cleanedPlat = strtoupper(str_replace(' ', '', $query));
            $kendaraans = Kendaraan::with(['konsumen', 'rekamServis.nota'])
                ->where(function ($q) use ($query, $cleanedPlat) {
                    $q->whereRaw("REPLACE(plat_nomor, ' ', '') LIKE ?", ["%{$cleanedPlat}%"])
                      ->orWhereHas('konsumen', function ($kq) use ($query) {
                          $kq->where('nama_lengkap', 'like', "%{$query}%")
                             ->orWhere('no_hp', 'like', "%{$query}%");
                      });
                })
                ->get();
        }

        $kendaraanId = $request->input('kendaraan_id');
        if ($kendaraanId) {
            $selectedKendaraan = Kendaraan::with([
                'konsumen',
                'rekamServis' => function ($sq) {
                    $sq->with(['detailSpareparts.sparepart', 'detailJasas.jasa', 'catatanRekomendasis', 'nota', 'user'])
                       ->orderBy('tanggal_servis', 'desc')
                       ->orderBy('id', 'desc');
                }
            ])->find($kendaraanId);
        } elseif ($kendaraans->count() === 1 && $query !== '') {
            $selectedKendaraan = Kendaraan::with([
                'konsumen',
                'rekamServis' => function ($sq) {
                    $sq->with(['detailSpareparts.sparepart', 'detailJasas.jasa', 'catatanRekomendasis', 'nota', 'user'])
                       ->orderBy('tanggal_servis', 'desc')
                       ->orderBy('id', 'desc');
                }
            ])->find($kendaraans->first()->id);
        }

        return view('admin.riwayat.index', compact('query', 'kendaraans', 'selectedKendaraan'));
    }

    public function searchApi(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $cleanedPlat = strtoupper(str_replace(' ', '', $q));
        $kendaraans = Kendaraan::with('konsumen')
            ->where(function ($query) use ($q, $cleanedPlat) {
                $query->whereRaw("REPLACE(plat_nomor, ' ', '') LIKE ?", ["%{$cleanedPlat}%"])
                      ->orWhereHas('konsumen', function ($kq) use ($q) {
                          $kq->where('nama_lengkap', 'like', "%{$q}%")
                             ->orWhere('no_hp', 'like', "%{$q}%");
                      });
            })
            ->take(10)
            ->get();

        return response()->json($kendaraans->map(function ($k) {
            $lastKm = $k->rekamServis()->latest('tanggal_servis')->value('km_akhir') ?? 0;
            return [
                'id' => $k->id,
                'plat_nomor' => $k->plat_nomor,
                'merk' => $k->merk,
                'tipe_model' => $k->tipe_model,
                'tahun' => $k->tahun,
                'konsumen_nama' => $k->konsumen->nama_lengkap,
                'konsumen_hp' => $k->konsumen->no_hp,
                'km_terakhir' => $lastKm,
                'total_servis' => $k->rekamServis()->count(),
            ];
        }));
    }
}

