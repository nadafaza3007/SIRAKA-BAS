<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AturanDiagnosa;
use Illuminate\Http\Request;

/**
 * Master Diagnosa. Menjaga perilaku asli tiga route Master Diagnosa Tahap 2.
 * Penyempurnaan validasi dapat dilakukan terpisah agar refactoring tidak
 * mengubah perilaku yang sudah berjalan.
 */
class DiagnosaController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi khusus untuk Administrator.');
        }

        $aturan = AturanDiagnosa::latest()->get()->map(function ($item) {
            if (!isset($item->diagnosa_terkait) && isset($item->hasil_diagnosa)) {
                $item->diagnosa_terkait = $item->hasil_diagnosa;
            }
            return $item;
        });

        return view('admin.diagnosa.index', compact('aturan'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }

        AturanDiagnosa::create([
            'kata_kunci'     => $request->kata_kunci,
            'hasil_diagnosa' => $request->diagnosa_terkait ?? $request->hasil_diagnosa,
        ]);

        return redirect('/admin/diagnosa')->with('success', 'Aturan diagnosa berhasil ditambahkan');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }

        $aturan = AturanDiagnosa::findOrFail($id);
        $aturan->delete();

        return redirect('/admin/diagnosa')->with('success', 'Aturan diagnosa berhasil dihapus');
    }
}