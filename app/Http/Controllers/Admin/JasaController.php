<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jasa;
use Illuminate\Http\Request;

/**
 * Master Jasa: perilaku disalin dari tiga route aktif tanpa mengubah bisnis proses.
 */
class JasaController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi khusus untuk Administrator.');
        }

        $jasas = Jasa::latest()->get()->map(function ($item) {
            if (!isset($item->status)) {
                $item->status = 'aktif';
            }
            return $item;
        });
        return view('admin.jasa.index', compact('jasas'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }

        Jasa::create([
            'nama_jasa' => $request->nama_jasa,
            'harga'     => $request->harga,
        ]);
        return redirect('/admin/jasa')->with('success', 'Jasa berhasil ditambahkan');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }

        $jasa = Jasa::findOrFail($id);
        $jasa->delete();
        return redirect('/admin/jasa')->with('success', 'Jasa berhasil dihapus');
    }
}