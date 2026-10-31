<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KonsumenController extends Controller
{
    public function index()
    {
        $konsumen = Konsumen::with('kendaraans')->latest()->get();
        return view('admin.konsumen.index', compact('konsumen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'no_hp' => 'required|unique:konsumens,no_hp',
            'plat_nomor' => 'required|unique:kendaraans,plat_nomor',
            'merk' => 'required|string',
            'tipe_model' => 'required|string',
            'tahun' => 'nullable|numeric',
            'cc' => 'nullable|string', // ✅ String karena format koma (2,2)
            'transmisi' => 'nullable|string',
            'jenis_bahan_bakar' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $konsumen = Konsumen::create([
                'nama_lengkap' => $request->nama_lengkap,
                'no_hp' => $request->no_hp,
            ]);

            // ✅ Konversi CC dari Liter (2,2) ke cc (2200)
            $ccDalamCc = null;
            if (!empty($request->cc)) {
                $ccLiter = str_replace(',', '.', $request->cc);
                $ccDalamCc = (int) round(floatval($ccLiter) * 1000);
            }

            Kendaraan::create([
    'konsumen_id' => $konsumen->id,
    'plat_nomor' => strtoupper(str_replace(' ', '', $request->plat_nomor)),
    'merk' => $request->merk,
    'tipe_model' => $request->tipe_model,
    'tahun' => $request->tahun,
    'cc' => !empty($request->cc) ? str_replace(',', '.', $request->cc) : null, // ✅ Ganti koma jadi titik
    'transmisi' => $request->transmisi,
    'jenis_bahan_bakar' => $request->jenis_bahan_bakar,
]);
        });

        return back()->with('success', 'Konsumen dan kendaraan berhasil didaftarkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'no_hp' => 'required|string|unique:konsumens,no_hp,' . $id,
            'kendaraan' => 'required|array',
            'kendaraan.*.plat_nomor' => 'required|string',
            'kendaraan.*.merk' => 'required|string',
            'kendaraan.*.tipe_model' => 'required|string',
            'kendaraan.*.tahun' => 'nullable|numeric|min:1900|max:' . (date('Y') + 1),
            'kendaraan.*.cc' => 'nullable|string', // ✅ String
            'kendaraan.*.transmisi' => 'nullable|in:manual,otomatis,cvt',
            'kendaraan.*.jenis_bahan_bakar' => 'nullable|in:gasoline,diesel',
        ]);

        DB::transaction(function () use ($request, $id) {
            $konsumen = Konsumen::findOrFail($id);
            $konsumen->update([
                'nama_lengkap' => $request->nama_lengkap,
                'no_hp' => $request->no_hp,
            ]);

            foreach ($request->kendaraan as $kId => $data) {
    Kendaraan::where('id', $kId)->update([
        'plat_nomor' => strtoupper(str_replace(' ', '', $data['plat_nomor'])),
        'merk' => $data['merk'],
        'tipe_model' => $data['tipe_model'],
        'tahun' => $data['tahun'] ?? null,
        'cc' => !empty($data['cc']) ? str_replace(',', '.', $data['cc']) : null, // ✅ Ganti koma jadi titik
        'transmisi' => $data['transmisi'] ?? null,
        'jenis_bahan_bakar' => $data['jenis_bahan_bakar'] ?? null,
    ]);
}
        });

        return back()->with('success', 'Data konsumen berhasil diperbarui!');
    }
}