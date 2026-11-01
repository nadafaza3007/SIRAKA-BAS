<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\StokSukuCadang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokSukuCadangController extends Controller
{
    // Menampilkan daftar stok suku cadang
    public function index()
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        $spareparts = Sparepart::where('jenis_ketersediaan', 'stok')
            ->orderBy('nama_barang')
            ->get();

        $totalBarang = $spareparts->count();

        $totalStok = $spareparts->sum('stok');

        $stokMenipis = $spareparts->filter(function ($item) {
            return $item->stok <= 2;
        })->count();

        return view('admin.stok-suku-cadang.index', compact(
            'spareparts',
            'totalBarang',
            'totalStok',
            'stokMenipis'
        ));
    }

    // Menampilkan form barang masuk
    public function create()
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        $spareparts = Sparepart::where('jenis_ketersediaan', 'stok')
            ->orderBy('nama_barang')
            ->get();

        return view('admin.stok-suku-cadang.create', compact(
            'spareparts'
        ));
    }

    // Menyimpan barang masuk
    public function store(Request $request)
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        $data = $request->validate([
            'sparepart_id' => [
                'required',
                'exists:spareparts,id'
            ],

            'jumlah' => [
                'required',
                'integer',
                'min:1',
                'max:999999'
            ],

            'tanggal' => [
                'required',
                'date'
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ]);

        DB::transaction(function () use ($data) {

            // Mengunci data agar tidak terjadi perubahan stok bersamaan
            $sparepart = Sparepart::whereKey($data['sparepart_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Barang PO tidak menggunakan stok fisik
            if (!$sparepart->isBarangStok()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'sparepart_id' => 'Barang Pre-Order tidak dapat dicatat sebagai barang masuk stok.'
                ]);
            }

            $stokSebelum = (int) $sparepart->stok;

            $jumlahMasuk = (int) $data['jumlah'];

            $stokSesudah = $stokSebelum + $jumlahMasuk;

            // Tambahkan stok master
            $sparepart->update([
                'stok' => $stokSesudah
            ]);

            // Simpan riwayat stok
            StokSukuCadang::create([
                'sparepart_id' => $sparepart->id,
                'user_id' => auth()->id(),
                'tipe' => 'masuk',
                'jumlah' => $jumlahMasuk,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'tanggal' => $data['tanggal'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.stok-suku-cadang.index')
            ->with('success', 'Barang masuk berhasil disimpan dan stok berhasil diperbarui.');
    }

    // Menampilkan riwayat stok
    public function riwayat()
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        $riwayat = StokSukuCadang::with([
            'sparepart',
            'user'
        ])
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        return view('admin.stok-suku-cadang.riwayat', compact(
            'riwayat'
        ));
    }
}