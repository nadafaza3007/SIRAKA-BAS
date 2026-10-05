<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Konsumen;
use App\Models\Kendaraan;
use App\Models\RekamServis;
use App\Models\AturanDiagnosa;
use App\Models\AuditLog;
use App\Models\NotaTransaksi;
use App\Models\Sparepart;
use App\Models\Jasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/
if (!function_exists('redirectByRole')) {
    function redirectByRole() {
        $role = auth()->user()->role;
        if ($role === 'pelanggan') {
            return redirect('/pelanggan/rekam-servis');
        }
        return redirect('/admin/dashboard');
    }
}

/*
|--------------------------------------------------------------------------
| Auth & Root Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $loginInput = $request->input('no_hp') ?? $request->input('username');
    $password = $request->input('password');

    if (Auth::attempt(['no_hp' => $loginInput, 'password' => $password]) || 
        Auth::attempt(['name' => $loginInput, 'password' => $password])) {
        $request->session()->regenerate();
        return redirectByRole();
    }

    return back()->withErrors([
        'no_hp' => 'Nomor HP/Username atau Password salah.',
    ]);
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    // Validasi input termasuk konfirmasi password
    $request->validate([
        'nama_lengkap'          => 'required|string|max:255',
        'no_hp'                 => 'required|string|max:20|unique:users,no_hp',
        'password'              => 'required|string|min:6|confirmed', // 'confirmed' mencocokkan dengan field password_confirmation
    ], [
        'no_hp.unique'          => 'Nomor HP sudah terdaftar. Silakan gunakan nomor lain atau langsung masuk.',
        'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
        'password.min'          => 'Kata sandi minimal harus 6 karakter.'
    ]);

    $konsumen = Konsumen::where('no_hp', $request->no_hp)->first();

    User::create([
        'name'        => $request->nama_lengkap,
        'no_hp'       => $request->no_hp,
        'role'        => 'pelanggan',
        'konsumen_id' => $konsumen ? $konsumen->id : null,
        'password'    => bcrypt($request->password),
    ]);

    return redirect('/login')->with('success', 'Pendaftaran akun berhasil! Silakan masuk menggunakan nomor HP dan kata sandi Anda.');
})->name('register.post');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Pelanggan Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/pelanggan/rekam-servis', function () {
        $servis = RekamServis::with(['kendaraan', 'nota'])
            ->whereHas('kendaraan', function ($q) {
                $q->where('konsumen_id', auth()->user()->konsumen_id);
            })->latest()->get();

        return view('pelanggan.rekam-servis', compact('servis'));
    })->name('pelanggan.rekam-servis');
});

/*
|--------------------------------------------------------------------------
| Admin & Mekanik Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->group(function () {

    // 1. Dashboard Utama
    Route::get('/dashboard', function () {
        $totalKonsumen  = Konsumen::count();
        $totalKendaraan = Kendaraan::count();
        $servisHariIni  = RekamServis::whereDate('tanggal_servis', now()->toDateString())->count();
        $servisBerjalan = RekamServis::whereIn('status', ['menunggu_pengerjaan', 'diproses'])->count();
        $servisMenunggu = RekamServis::where('status', 'menunggu_pengerjaan')->count();
        
        $totalOmzet = 0;
        if (Schema::hasTable('nota_transaksis')) {
            $totalOmzet = NotaTransaksi::where('status_pembayaran', 'lunas')->sum('total_biaya');
        } elseif (Schema::hasTable('nota_transaksi')) {
            $totalOmzet = DB::table('nota_transaksi')->where('status_pembayaran', 'lunas')->sum('total_biaya');
        }

        $antreanTerbaru = RekamServis::with(['kendaraan', 'kendaraan.konsumen'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalKonsumen',
            'totalKendaraan',
            'servisHariIni',
            'servisBerjalan',
            'servisMenunggu',
            'totalOmzet',
            'antreanTerbaru'
        ));
    })->name('admin.dashboard');

    // 2. Rekam Servis Workflows
    Route::get('/rekam-servis', function () {
        $servis = RekamServis::with(['kendaraan', 'kendaraan.konsumen'])->latest()->get();
        return view('admin.rekam-servis.index', compact('servis'));
    })->name('admin.rekam-servis.index');

    Route::get('/rekam-servis/create', function () {
        $kendaraans = Kendaraan::with('konsumen')->get();
        return view('admin.rekam-servis.create', compact('kendaraans'));
    })->name('admin.rekam-servis.create');

    Route::post('/rekam-servis', function (Request $request) {
        $keluhanInput = trim($request->keluhan_awal);
        $semuaAturan = AturanDiagnosa::all();

        $diagnosaTerpilih = 'Pengecekan Umum';
        $skorTertinggi = 0;

        $hitungKemiripan = function ($teks1, $teks2) {
            $stopWords = ['dan', 'atau', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'dengan', 'ada', 'ini', 'itu', 'saat', 'ketika'];
            $bersihkan = function($str) use ($stopWords) {
                $str = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $str));
                $kataArray = explode(' ', $str);
                return array_diff($kataArray, $stopWords, ['']);
            };

            $kata1 = array_unique($bersihkan($teks1));
            $kata2 = array_unique($bersihkan($teks2));

            if (empty($kata1) || empty($kata2)) return 0;

            $irisan = array_intersect($kata1, $kata2);
            $gabungan = array_unique(array_merge($kata1, $kata2));

            return count($irisan) / count($gabungan);
        };

        foreach ($semuaAturan as $aturan) {
            if (stripos($keluhanInput, $aturan->kata_kunci) !== false) {
                $skorTertinggi = 1.0;
                $diagnosaTerpilih = $aturan->diagnosa_terkait ?? $aturan->hasil_diagnosa;
                break;
            }

            $skor = $hitungKemiripan($keluhanInput, $aturan->kata_kunci);
            if ($skor > $skorTertinggi) {
                $skorTertinggi = $skor;
                $diagnosaTerpilih = $aturan->diagnosa_terkait ?? $aturan->hasil_diagnosa;
            }
        }

        if ($skorTertinggi < 0.15) {
            $diagnosaTerpilih = 'Pengecekan Umum';
        }

        RekamServis::create([
            'kendaraan_id'   => $request->kendaraan_id,
            'km_akhir'       => $request->km_akhir,
            'keluhan_awal'   => $keluhanInput,
            'diagnosa_awal'  => $diagnosaTerpilih,
            'tanggal_servis' => now(),
            'status'         => 'menunggu_pengerjaan',
            'user_id'        => auth()->id(),
        ]);

        return redirect('/admin/rekam-servis')->with('success', 'Rekam servis berhasil dibuat dengan deteksi diagnosa otomatis.');
    })->name('admin.rekam-servis.store');

    // Route untuk Form Koreksi Rekam Servis (PENTING AGAR TIDAK ERROR LAGI)
    Route::get('/rekam-servis/{id}/koreksi', function ($id) {
        $servis = RekamServis::with(['kendaraan.konsumen'])->findOrFail($id);
        return view('admin.rekam-servis.koreksi', compact('servis'));
    })->name('admin.rekam-servis.koreksi-form');

    // 2. Untuk memproses penyimpanan data koreksi (Metode POST) <--- TAMBAHKAN INI
    Route::post('/rekam-servis/{id}/koreksi', function (Request $request, $id) {
        $servis = RekamServis::findOrFail($id);
        
        $servis->update([
            'diagnosa_akhir'  => $request->diagnosa_akhir,
            'tindakan_servis' => $request->tindakan_servis,
            'status'          => 'sedang_dikerjakan',
        ]);

        return redirect('/admin/rekam-servis')->with('success', 'Koreksi diagnosa dan tindakan berhasil disimpan.');
    })->name('admin.rekam-servis.koreksi');

    // Halaman Nota Transaksi
    Route::get('/rekam-servis/{id}/nota', function ($id) {
        $servis = RekamServis::with([
            'kendaraan.konsumen',
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
            'catatanRekomendasis',
            'nota'
        ])->findOrFail($id);

        $spareparts = Sparepart::latest()->get();
        $jasas = Jasa::latest()->get();

        return view('admin.rekam-servis.nota', compact('servis', 'spareparts', 'jasas'));
    })->name('admin.rekam-servis.nota');

    // Item Suku Cadang & Jasa pada Nota (Aksi Tambah & Hapus)
    Route::post('/rekam-servis/{id}/sparepart', function ($id, Request $request) {
        $sp = Sparepart::findOrFail($request->sparepart_id);
        
        DB::table('detail_spareparts')->insert([
            'rekam_servis_id'            => $id,
            'sparepart_id'               => $sp->id,
            'qty'                        => $request->qty ?? 1,
            'harga_modal_saat_transaksi' => $sp->harga_modal ?? 0,
            'harga_jual_saat_transaksi'  => $sp->harga_jual,
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);

        return back()->with('success', 'Sparepart berhasil ditambahkan ke nota');
    })->name('admin.rekam-servis.sparepart.tambah');

    Route::delete('/rekam-servis/{id}/sparepart/{detailId}', function ($id, $detailId) {
        DB::table('detail_spareparts')->where('id', $detailId)->delete();
        return back()->with('success', 'Sparepart berhasil dihapus dari nota');
    })->name('admin.rekam-servis.sparepart.hapus');

    Route::post('/rekam-servis/{id}/jasa', function ($id, Request $request) {
        $jasa = Jasa::findOrFail($request->jasa_id);
        
        DB::table('detail_jasas')->insert([
            'rekam_servis_id'       => $id,
            'jasa_id'               => $jasa->id,
            'harga_saat_transaksi'  => $request->harga_custom ?? $jasa->harga,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return back()->with('success', 'Jasa berhasil ditambahkan ke nota');
    })->name('admin.rekam-servis.jasa.tambah');

    Route::delete('/rekam-servis/{id}/jasa/{detailId}', function ($id, $detailId) {
        DB::table('detail_jasas')->where('id', $detailId)->delete();
        return back()->with('success', 'Jasa berhasil dihapus dari nota');
    })->name('admin.rekam-servis.jasa.hapus');

    // 3. Data Konsumen
    Route::get('/konsumen', function () {
        $konsumen = Konsumen::with('kendaraans')->latest()->get();
        return view('admin.konsumen.index', compact('konsumen'));
    })->name('admin.konsumen.index');

    Route::post('/konsumen', function (Request $request) {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp'        => 'required|string|max:20',
        ]);

        if ($request->has('kendaraan')) {
            foreach ($request->kendaraan as $k) {
                if (empty($k['plat_nomor'])) continue;

                $platNomor = strtoupper(trim($k['plat_nomor']));
                $platAda = Kendaraan::where('plat_nomor', $platNomor)->first();

                if ($platAda) {
                    return back()
                        ->withInput()
                        ->with('error', "Gagal! Plat nomor '{$platNomor}' sudah terdaftar atas nama konsumen lain.");
                }
            }
        }

        $dataKonsumen = Konsumen::create([
            'nama_lengkap' => $request->nama_lengkap,
            'no_hp'        => $request->no_hp,
        ]);

        if ($request->has('kendaraan')) {
            foreach ($request->kendaraan as $k) {
                if (!empty($k['plat_nomor'])) {
                    Kendaraan::create([
                        'konsumen_id' => $dataKonsumen->id,
                        'plat_nomor'  => strtoupper(trim($k['plat_nomor'])),
                        'merk'        => $k['merk'] ?? 'Toyota',
                        'tipe_model'  => $k['tipe_model'] ?? '-',
                        'tahun'       => $k['tahun'] ?? 2020,
                    ]);
                }
            }
        }

        return redirect('/admin/konsumen')->with('success', 'Konsumen & Kendaraan berhasil ditambahkan');
    })->name('admin.konsumen.store');

    /*
    |--------------------------------------------------------------------------
    | ROUTE KHUSUS ADMIN (Dicek langsung di dalam Group tanpa Closure middleware)
    |--------------------------------------------------------------------------
    */
    // 4. Master Sparepart
    Route::get('/sparepart', function () {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi khusus untuk Administrator.');
        }
        $spareparts = Sparepart::latest()->get();
        return view('admin.sparepart.index', compact('spareparts'));
    })->name('admin.sparepart.index');

    Route::post('/sparepart', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        Sparepart::create([
            'nama_barang'  => $request->nama_barang,
            'kode_barang'  => $request->kode_barang,
            'stok'         => $request->stok ?? 0,
            'harga_modal'  => $request->harga_modal,
            'harga_jual'   => $request->harga_jual,
            'nama_toko'    => $request->nama_toko,
        ]);

        return redirect('/admin/sparepart')->with('success', 'Sparepart berhasil ditambahkan');
    })->name('admin.sparepart.store');

    Route::delete('/sparepart/{id}', function ($id) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        $sparepart = Sparepart::findOrFail($id);
        $sparepart->delete();

        return redirect('/admin/sparepart')->with('success', 'Sparepart berhasil dihapus');
    })->name('admin.sparepart.destroy');

    // 5. Master Jasa
    Route::get('/jasa', function () {
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
    })->name('admin.jasa.index');

    Route::post('/jasa', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        Jasa::create([
            'nama_jasa' => $request->nama_jasa,
            'harga'     => $request->harga,
        ]);

        return redirect('/admin/jasa')->with('success', 'Jasa berhasil ditambahkan');
    })->name('admin.jasa.store');

    Route::delete('/jasa/{id}', function ($id) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        $jasa = Jasa::findOrFail($id);
        $jasa->delete();

        return redirect('/admin/jasa')->with('success', 'Jasa berhasil dihapus');
    })->name('admin.jasa.destroy');

    // 6. Master Diagnosa
    Route::get('/diagnosa', function () {
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
    })->name('admin.diagnosa.index');

    Route::post('/diagnosa', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        
        // Hanya menggunakan kolom hasil_diagnosa agar sesuai dengan struktur database saat ini
        AturanDiagnosa::create([
            'kata_kunci'     => $request->kata_kunci,
            'hasil_diagnosa' => $request->diagnosa_terkait ?? $request->hasil_diagnosa,
        ]);

        return redirect('/admin/diagnosa')->with('success', 'Aturan diagnosa berhasil ditambahkan');
    })->name('admin.diagnosa.store');

    Route::delete('/diagnosa/{id}', function ($id) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        $aturan = AturanDiagnosa::findOrFail($id);
        $aturan->delete();

        return redirect('/admin/diagnosa')->with('success', 'Aturan diagnosa berhasil dihapus');
    })->name('admin.diagnosa.destroy');

    // 7. Laporan Keuangan
    Route::get('/laporan', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi khusus untuk Administrator.');
        }
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $transaksi = collect();
        if (Schema::hasTable('nota_transaksis')) {
            $transaksi = NotaTransaksi::with(['rekamServis.kendaraan.konsumen'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();
        } elseif (Schema::hasTable('nota_transaksi')) {
            $transaksi = DB::table('nota_transaksi')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();
        }

        $transaksi = $transaksi->map(function ($item) {
            if (!isset($item->no_nota)) {
                $item->no_nota = 'NOTA-' . str_pad($item->id, 4, '0', STR_PAD_LEFT);
            }
            if (!isset($item->tanggal_cetak)) {
                $item->tanggal_cetak = isset($item->created_at) ? date('Y-m-d', strtotime($item->created_at)) : now()->format('Y-m-d');
            }
            return $item;
        });

        $pendapatanKotor     = $transaksi->where('status_pembayaran', 'lunas')->sum('total_biaya');
        $totalModalSparepart = $pendapatanKotor * 0.3;
        $labaBersih          = $pendapatanKotor - $totalModalSparepart;

        return view('admin.laporan.index', compact(
            'startDate',
            'endDate',
            'transaksi',
            'pendapatanKotor',
            'totalModalSparepart',
            'labaBersih'
        ));
    })->name('admin.laporan.index');

    Route::get('/laporan/cetak-pdf', function () {
        if (auth()->user()->role !== 'admin') {
            return redirect('/admin/dashboard')->with('error', 'Akses dibatasi.');
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'entitas' => 'Laporan Pemasukan',
            'aksi'    => 'Cetak / Unduh Laporan Keuangan Periode ' . now()->startOfMonth()->toDateString() . ' s/d ' . now()->endOfMonth()->toDateString(),
        ]);
        return response('PDF Generated', 200);
    })->name('admin.laporan.cetak-pdf');


    // 8. API Search Kendaraan
    Route::get('/riwayat/search-api', function (Request $request) {
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
    })->name('admin.riwayat.search-api');

    // Workflows Catatan Rekomendasi & Nota
    Route::post('/rekam-servis/{id}/rekomendasi', function ($id, Request $request) {
        DB::table('catatan_rekomendasis')->insert([
            'rekam_servis_id' => (int) $id,
            'catatan'         => $request->catatan,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect("/admin/rekam-servis/{$id}/nota");
    })->name('admin.rekam-servis.rekomendasi');

    Route::post('/rekam-servis/{id}/nota/konfirmasi', function ($id, Request $request) {
        $servis = RekamServis::findOrFail($id);
        $subtotal = $servis->subtotal();
        $diskon = $request->diskon ?? 0;
        $totalBiaya = max(0, $subtotal - $diskon);

        if (!$servis->nota) {
            $lastNota = NotaTransaksi::latest('id')->first();
            $nextId = $lastNota ? ($lastNota->id + 1) : 1;
            $noNota = 'NOTA-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            NotaTransaksi::create([
                'rekam_servis_id'   => $servis->id,
                'no_nota'           => $noNota,
                'tanggal_cetak'     => now()->toDateString(),
                'subtotal'          => $subtotal,
                'diskon'            => $diskon,
                'total_biaya'       => $totalBiaya,
                'metode_cetak'      => $request->metode_cetak ?? 'standar',
                'status_pembayaran' => 'lunas',
            ]);
        } else {
            $servis->nota->update([
                'subtotal'     => $subtotal,
                'diskon'       => $diskon,
                'total_biaya'  => $totalBiaya,
                'metode_cetak' => $request->metode_cetak ?? 'standar',
            ]);
        }

        $servis->update(['status' => 'selesai']);

        return redirect()->route('admin.rekam-servis.index')
            ->with('success', 'Servis berhasil dikonfirmasi dan diselesaikan! Nota telah tersimpan.');
    })->name('admin.rekam-servis.nota.konfirmasi');
});