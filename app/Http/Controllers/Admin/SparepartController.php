<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\Http\Request;

class SparepartController extends Controller
{
    private function hanyaAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi khusus untuk Administrator.');
        }

        $spareparts = Sparepart::latest()->get();
        return view('admin.sparepart.index', compact('spareparts'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }

        $data = $request->validate([
            'nama_barang' => ['required', 'string', 'max:255'],
            'kode_barang' => ['nullable', 'string', 'max:255'],
            'nama_toko' => ['nullable', 'string', 'max:255'],
            'jenis_ketersediaan' => ['required', 'in:stok,po'],
            'stok' => ['required_if:jenis_ketersediaan,stok', 'nullable', 'integer', 'min:0'],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
        ]);

        $data['stok'] = $data['jenis_ketersediaan'] === 'po' ? null : (int) $data['stok'];
        Sparepart::create($data);

        return redirect('/admin/sparepart')->with('success', 'Sparepart berhasil ditambahkan');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }

        $sparepart = Sparepart::findOrFail($id);
        $sparepart->delete();

        return redirect('/admin/sparepart')->with('success', 'Sparepart berhasil dihapus');
    }
}