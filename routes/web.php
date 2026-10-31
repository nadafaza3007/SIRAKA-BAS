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
use App\Http\Controllers\PelangganController;
use App\Http\Middleware\StaffOnlyMiddleware;
use App\Http\Controllers\Admin\PembayaranNotaController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/
if (!function_exists('redirectByRole')) {
    function redirectByRole() {
        $role = auth()->user()->role;
        if ($role === 'pelanggan') {
            return redirect()->route('pelanggan.dashboard');
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
    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */
    $request->validate([
        'no_hp' => [
            'required',
            'string',
        ],
        'password' => [
            'required',
            'string',
        ],
    ], [
        'no_hp.required'    => 'Username atau nomor HP wajib diisi.',
        'password.required' => 'Kata sandi wajib diisi.',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Ambil input
    |--------------------------------------------------------------------------
    |
    | Field form tetap bernama no_hp, tetapi nilainya boleh:
    |
    | - username
    | - nomor HP
    |
    */
    $loginInput = trim($request->input('no_hp'));
    $password   = $request->input('password');
    $remember   = $request->boolean('remember');

    /*
    |--------------------------------------------------------------------------
    | Percobaan 1: login menggunakan nomor HP
    |--------------------------------------------------------------------------
    */
    $berhasil = Auth::attempt(
        [
            'no_hp'    => $loginInput,
            'password' => $password,
        ],
        $remember
    );

    /*
    |--------------------------------------------------------------------------
    | Percobaan 2: login menggunakan username
    |--------------------------------------------------------------------------
    */
    if (! $berhasil) {
        $berhasil = Auth::attempt(
            [
                'username' => strtolower($loginInput),
                'password' => $password,
            ],
            $remember
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Login berhasil
    |--------------------------------------------------------------------------
    */
    if ($berhasil) {
        $request->session()->regenerate();
        return redirectByRole();
    }

    /*
    |--------------------------------------------------------------------------
    | Login gagal
    |--------------------------------------------------------------------------
    */
    return back()
        ->withInput($request->only('no_hp'))
        ->withErrors([
            'no_hp' => 'Username / Nomor HP atau kata sandi salah.',
        ]);
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

/*
|--------------------------------------------------------------------------
| Cek Username Secara Realtime
|--------------------------------------------------------------------------
*/
Route::get('/check-username', function (Request $request) {
    $username = strtolower(trim((string) $request->query('username')));

    if ($username === '') {
        return response()->json([
            'available' => false,
            'message'   => 'Username wajib diisi.',
        ]);
    }

    if (strlen($username) < 3) {
        return response()->json([
            'available' => false,
            'message'   => 'Username minimal 3 karakter.',
        ]);
    }

    if (strlen($username) > 50) {
        return response()->json([
            'available' => false,
            'message'   => 'Username maksimal 50 karakter.',
        ]);
    }

    if (! preg_match('/^[A-Za-z0-9\_-]+$/', $username)) {
        return response()->json([
            'available' => false,
            'message'   => 'Username hanya boleh berisi huruf, angka, underscore, atau tanda hubung.',
        ]);
    }

    $usernameSudahAda = User::whereRaw('LOWER(username) = ?', [$username])->exists();

    if ($usernameSudahAda) {
        return response()->json([
            'available' => false,
            'message'   => 'Username sudah digunakan.',
        ]);
    }

    return response()->json([
        'available' => true,
        'message'   => 'Username tersedia.',
    ]);
})
    ->middleware('throttle:30,1')
    ->name('username.check');

Route::post('/register', function (Request $request) {
    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */
    $request->validate([
        'nama_lengkap' => [
            'required',
            'string',
            'max:255',
        ],
        'username' => [
            'required',
            'string',
            'min:3',
            'max:50',
            'alpha_dash',
            'unique:users,username',
        ],
        'no_hp' => [
            'required',
            'string',
            'max:20',
            'unique:users,no_hp',
        ],
        'password' => [
            'required',
            'string',
            'min:6',
            'confirmed',
        ],
    ], [
        'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
        'username.required'     => 'Username wajib diisi.',
        'username.min'          => 'Username minimal 3 karakter.',
        'username.max'          => 'Username maksimal 50 karakter.',
        'username.alpha_dash'   => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan underscore.',
        'username.unique'       => 'Username sudah digunakan. Silakan gunakan username lain.',
        'no_hp.required'        => 'Nomor HP wajib diisi.',
        'no_hp.unique'          => 'Nomor HP sudah terdaftar. Silakan masuk menggunakan akun tersebut.',
        'password.required'     => 'Kata sandi wajib diisi.',
        'password.min'          => 'Kata sandi minimal 6 karakter.',
        'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Cari data konsumen berdasarkan nomor HP
    |--------------------------------------------------------------------------
    */
    $konsumen = Konsumen::where('no_hp', $request->no_hp)->first();

    /*
    |--------------------------------------------------------------------------
    | Simpan akun baru
    |--------------------------------------------------------------------------
    */
    User::create([
        'name'        => trim($request->nama_lengkap),
        'username'    => strtolower(trim($request->username)),
        'no_hp'       => trim($request->no_hp),
        'role'        => 'pelanggan',
        'konsumen_id' => $konsumen ? $konsumen->id : null,
        'password'    => bcrypt($request->password),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Kembali ke login
    |--------------------------------------------------------------------------
    */
    return redirect()
        ->route('login')
        ->with(
            'success',
            'Pendaftaran akun berhasil! Silakan masuk menggunakan username atau nomor HP dan kata sandi Anda.'
        );
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
Route::prefix('pelanggan')
    ->middleware(['auth'])
    ->group(function () {
        // Dashboard pelanggan
        Route::get('/dashboard', [PelangganController::class, 'dashboard'])
            ->name('pelanggan.dashboard');

        // Daftar kendaraan pelanggan
        Route::get('/kendaraan', [PelangganController::class, 'kendaraan'])
            ->name('pelanggan.kendaraan');

        // Semua riwayat servis pelanggan
        Route::get('/riwayat', [PelangganController::class, 'riwayat'])
            ->name('pelanggan.riwayat');

        // Riwayat servis berdasarkan satu kendaraan
        Route::get('/kendaraan/{kendaraan}/riwayat', [PelangganController::class, 'riwayatKendaraan'])
            ->name('pelanggan.kendaraan.riwayat');

        // Nota resmi pelanggan: dibatasi ketat pada kepemilikan kendaraan.
        Route::get('/servis/{id}/nota', function ($id) {
            $konsumen = auth()->user()->konsumen_id
                ? Konsumen::find(auth()->user()->konsumen_id)
                : Konsumen::where('no_hp', auth()->user()->no_hp)->first();

            abort_unless($konsumen, 404);

            $servis = RekamServis::with([
                'kendaraan.konsumen',
                'detailSpareparts.sparepart',
                'detailJasas.jasa',
                'catatanRekomendasis',
                'nota.pembayaran',
            ])->findOrFail($id);

            abort_unless($servis->kendaraan &&
                (int) $servis->kendaraan->konsumen_id === (int) $konsumen->id, 404);
            abort_unless($servis->nota, 404);

            $nota = $servis->nota;
            return view('pelanggan.riwayat-servis.nota-resmi', compact('servis', 'nota'));
        })->name('pelanggan.nota-resmi');

        // Detail satu rekam servis
        Route::get('/servis/{id}', [PelangganController::class, 'detailServis'])
            ->name('pelanggan.detail-servis');

        /*
        |--------------------------------------------------------------------------
        | Route Lama - Kompatibilitas
        |--------------------------------------------------------------------------
        */
        Route::get('/rekam-servis', function () {
            return redirect()->route('pelanggan.riwayat');
        })->name('pelanggan.rekam-servis');
    });

/*
|--------------------------------------------------------------------------
| Admin & Mekanik Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', StaffOnlyMiddleware::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 1. DASHBOARD UTAMA
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('admin.dashboard');

    /*
    |--------------------------------------------------------------------------
    | 2. REKAM SERVIS
    |--------------------------------------------------------------------------
    */
    Route::get('/rekam-servis', function () {
        $servis = RekamServis::with([
            'kendaraan',
            'kendaraan.konsumen',
            'nota',
        ])
            ->latest()
            ->get();

        return view('admin.rekam-servis.index', compact('servis'));
    })->name('admin.rekam-servis.index');

    Route::get('/rekam-servis/create', function () {
        $konsumens = Konsumen::with([
            'kendaraans' => function ($query) {
                $query->with('rekomendasiNotaTerbuka')->orderBy('plat_nomor');
            },
        ])
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.rekam-servis.create', compact('konsumens'));
    })->name('admin.rekam-servis.create');

    Route::post('/rekam-servis', function (Request $request) {
        $request->validate([
            'konsumen_id'  => ['required', 'exists:konsumens,id'],
            'kendaraan_id' => ['required', 'exists:kendaraans,id'],
            'km_masuk'     => ['required', 'integer', 'min:0'],
            'keluhan_awal' => ['required', 'string', 'max:2000'],
        ], [
            'konsumen_id.required'  => 'Konsumen wajib dipilih.',
            'konsumen_id.exists'    => 'Data konsumen tidak ditemukan.',
            'kendaraan_id.required' => 'Plat kendaraan wajib dipilih.',
            'kendaraan_id.exists'   => 'Kendaraan yang dipilih tidak ditemukan.',
            'km_masuk.required'     => 'Kilometer terkini wajib diisi.',
            'km_masuk.integer'      => 'Kilometer harus berupa angka.',
            'km_masuk.min'          => 'Kilometer tidak boleh kurang dari 0.',
            'keluhan_awal.required' => 'Keluhan konsumen wajib diisi.',
        ]);

        $kendaraan = Kendaraan::where('id', $request->kendaraan_id)
            ->where('konsumen_id', $request->konsumen_id)
            ->first();

        if (! $kendaraan) {
            return back()
                ->withInput()
                ->withErrors([
                    'kendaraan_id' => 'Kendaraan tersebut tidak terdaftar atas nama konsumen yang dipilih.',
                ]);
        }

        $keluhanInput = trim($request->keluhan_awal);

        $semuaAturan     = AturanDiagnosa::all();
        $diagnosaTerpilih = 'Pengecekan Umum';
        $skorTertinggi   = 0;

        $hitungKemiripan = function ($teks1, $teks2) {
            $stopWords = ['dan', 'atau', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'dengan', 'ada', 'ini', 'itu', 'saat', 'ketika'];
            $bersihkan = function ($str) use ($stopWords) {
                $str       = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $str));
                $kataArray = preg_split('/\s+/', trim($str));
                return array_values(array_diff($kataArray, $stopWords, ['']));
            };
            $kata1 = array_unique($bersihkan($teks1));
            $kata2 = array_unique($bersihkan($teks2));
            if (empty($kata1) || empty($kata2)) {
                return 0;
            }
            $irisan    = array_intersect($kata1, $kata2);
            $gabungan  = array_unique(array_merge($kata1, $kata2));
            if (count($gabungan) === 0) {
                return 0;
            }
            return count($irisan) / count($gabungan);
        };

        foreach ($semuaAturan as $aturan) {
            if (stripos($keluhanInput, $aturan->kata_kunci) !== false) {
                $skorTertinggi    = 1.0;
                $diagnosaTerpilih = $aturan->diagnosa_terkait ?? $aturan->hasil_diagnosa;
                break;
            }
            $skor = $hitungKemiripan($keluhanInput, $aturan->kata_kunci);
            if ($skor > $skorTertinggi) {
                $skorTertinggi    = $skor;
                $diagnosaTerpilih = $aturan->diagnosa_terkait ?? $aturan->hasil_diagnosa;
            }
        }

        if ($skorTertinggi < 0.15) {
            $diagnosaTerpilih = 'Pengecekan Umum';
        }

        RekamServis::create([
            'kendaraan_id' => $kendaraan->id,
            'km_masuk'     => $request->km_masuk,
            'keluhan_awal' => $keluhanInput,
            'diagnosa_awal' => $diagnosaTerpilih,
            'tanggal_servis' => now(),
            'status'       => 'menunggu_pengerjaan',
            'user_id'      => auth()->id(),
        ]);

        return redirect()
            ->route('admin.rekam-servis.index')
            ->with('success', 'Rekam servis ' . $kendaraan->plat_nomor . ' berhasil dibuat dengan deteksi diagnosa otomatis.');
    })->name('admin.rekam-servis.store');

    Route::get('/rekam-servis/{id}/koreksi', function ($id) {
        $servis = RekamServis::with(['kendaraan.konsumen'])->findOrFail($id);

        if ($servis->status === 'selesai'
            || NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()) {
            return redirect()->route('admin.rekam-servis.index')
                ->with('error', 'Servis ini sudah memiliki tagihan dan tidak dapat dikoreksi lagi.');
        }

        return view('admin.rekam-servis.koreksi', compact('servis'));
    })->name('admin.rekam-servis.koreksi-form');

    Route::post('/rekam-servis/{id}/koreksi', function (Request $request, $id) {
        $request->validate([
            'diagnosa_akhir'  => ['required', 'string', 'max:2000'],
            'tindakan_servis' => ['required', 'string', 'max:5000'],
        ]);

        $servis = RekamServis::findOrFail($id);

        if ($servis->status === 'selesai'
            || NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()) {
            return redirect()->route('admin.rekam-servis.index')
                ->with('error', 'Servis ini sudah memiliki tagihan dan tidak dapat dikoreksi lagi.');
        }

        $servis->update([
            'diagnosa_akhir'  => $request->diagnosa_akhir,
            'tindakan_servis' => $request->tindakan_servis,
            'status'          => 'sedang_dikerjakan',
        ]);

        return redirect()
            ->route('admin.rekam-servis.index')
            ->with('success', 'Koreksi diagnosa dan tindakan berhasil disimpan.');
    })->name('admin.rekam-servis.koreksi');

    Route::get('/rekam-servis/{id}/nota', function ($id) {
        $servis = RekamServis::with([
            'kendaraan.konsumen',
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
            'catatanRekomendasis',
            'nota.pembayaran.penerima',
        ])->findOrFail($id);

        $spareparts = Sparepart::latest()->get();
        $jasas      = Jasa::latest()->get();

        return view('admin.rekam-servis.nota', compact('servis', 'spareparts', 'jasas'));
    })->name('admin.rekam-servis.nota');

    Route::post('/rekam-servis/{id}/sparepart', function ($id, Request $request) {
        $data = $request->validate([
            'sparepart_id' => ['required', 'exists:spareparts,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        $result = DB::transaction(function () use ($id, $data) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'sparepart' => 'Tagihan sudah diterbitkan atau servis selesai. Rincian tidak dapat diubah.',
                ]);
            }
            $sparepart = Sparepart::whereKey($data['sparepart_id'])->lockForUpdate()->firstOrFail();
            $tambahan = (int) $data['qty'];
            if ((int) $sparepart->stok < $tambahan) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'qty' => 'Stok ' . $sparepart->nama_barang . ' tidak mencukupi. Tersedia ' . $sparepart->stok . '.',
                ]);
            }
            $detailLama = DB::table('detail_spareparts')
                ->where('rekam_servis_id', $servis->id)
                ->where('sparepart_id', $sparepart->id)->first();
            if ($detailLama) {
                $qtyBaru = (int) $detailLama->qty + $tambahan;
                if ($qtyBaru > 999) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'qty' => 'Jumlah sparepart pada satu baris maksimal 999.',
                    ]);
                }
                DB::table('detail_spareparts')->where('id', $detailLama->id)
                    ->update(['qty' => $qtyBaru, 'updated_at' => now()]);
                $detailId = $detailLama->id;
                $hargaSatuan = (float) $detailLama->harga_jual_saat_transaksi;
            } else {
                $qtyBaru = $tambahan;
                $hargaSatuan = (float) $sparepart->harga_jual;
                $detailId = DB::table('detail_spareparts')->insertGetId([
                    'rekam_servis_id' => $servis->id,
                    'sparepart_id' => $sparepart->id,
                    'qty' => $qtyBaru,
                    'harga_modal_saat_transaksi' => $sparepart->harga_modal ?? 0,
                    'harga_jual_saat_transaksi' => $hargaSatuan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $sparepart->decrement('stok', $tambahan);
            $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);
            return [
                'id' => $detailId,
                'name' => $sparepart->nama_barang,
                'qty' => $qtyBaru,
                'unit_price' => $hargaSatuan,
                'subtotal' => (float) $servis->subtotal(),
                'servis_id' => $servis->id,
            ];
        }, 3);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sparepart berhasil ditambahkan.',
                'subtotal' => $result['subtotal'],
                'detail' => [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'qty' => $result['qty'],
                    'unit_price' => $result['unit_price'],
                    'subtotal' => $result['unit_price'] * $result['qty'],
                    'qty_url' => route('admin.rekam-servis.sparepart.qty', [$result['servis_id'], $result['id']]),
                    'delete_url' => route('admin.rekam-servis.sparepart.hapus', [$result['servis_id'], $result['id']]),
                ],
            ]);
        }
        return back()->with('success', 'Sparepart berhasil ditambahkan ke nota dan stok diperbarui.');
    })->name('admin.rekam-servis.sparepart.tambah');

    Route::patch('/rekam-servis/{id}/sparepart/{detailId}/qty', function ($id, $detailId, Request $request) {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:1', 'max:999']]);
        $result = DB::transaction(function () use ($id, $detailId, $data) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'qty' => 'Tagihan sudah diterbitkan atau servis selesai. Rincian tidak dapat diubah.',
                ]);
            }
            $detail = DB::table('detail_spareparts')
                ->where('rekam_servis_id', $servis->id)->where('id', $detailId)->first();
            if (! $detail) {
                abort(404, 'Detail sparepart tidak ditemukan.');
            }
            $sparepart = Sparepart::whereKey($detail->sparepart_id)->lockForUpdate()->firstOrFail();
            $qtyBaru = (int) $data['qty'];
            $selisih = $qtyBaru - (int) $detail->qty;
            if ($selisih > 0 && (int) $sparepart->stok < $selisih) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'qty' => 'Stok tidak mencukupi. Tersedia ' . $sparepart->stok . '.',
                ]);
            }
            DB::table('detail_spareparts')->where('id', $detail->id)
                ->update(['qty' => $qtyBaru, 'updated_at' => now()]);
            if ($selisih > 0) {
                $sparepart->decrement('stok', $selisih);
            } elseif ($selisih < 0) {
                $sparepart->increment('stok', -$selisih);
            }
            $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);
            return [
                'qty' => $qtyBaru,
                'item_subtotal' => (float) $detail->harga_jual_saat_transaksi * $qtyBaru,
                'subtotal' => (float) $servis->subtotal(),
            ];
        }, 3);
        return response()->json([
            'message' => 'Jumlah sparepart diperbarui.',
            'qty' => $result['qty'],
            'item_subtotal' => $result['item_subtotal'],
            'subtotal' => $result['subtotal'],
        ]);
    })->name('admin.rekam-servis.sparepart.qty');

    Route::delete('/rekam-servis/{id}/sparepart/{detailId}', function ($id, $detailId, Request $request) {
        $subtotal = DB::transaction(function () use ($id, $detailId) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'sparepart' => 'Tagihan sudah diterbitkan atau servis selesai. Rincian tidak dapat diubah.',
                ]);
            }
            $detail = DB::table('detail_spareparts')
                ->where('rekam_servis_id', $servis->id)->where('id', $detailId)->first();
            if (! $detail) {
                abort(404, 'Detail sparepart tidak ditemukan.');
            }
            $sparepart = Sparepart::whereKey($detail->sparepart_id)->lockForUpdate()->firstOrFail();
            DB::table('detail_spareparts')->where('id', $detail->id)->delete();
            $sparepart->increment('stok', (int) $detail->qty);
            $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);
            return (float) $servis->subtotal();
        }, 3);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sparepart berhasil dihapus.',
                'subtotal' => $subtotal,
            ]);
        }
        return back()->with('success', 'Sparepart berhasil dihapus dan stok dikembalikan.');
    })->name('admin.rekam-servis.sparepart.hapus');

    Route::post('/rekam-servis/{id}/jasa', function ($id, Request $request) {
        abort_unless(in_array(auth()->user()->role, ['admin','anggota'], true), 403);
        $servis = RekamServis::findOrFail($id);
        if (
                $servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()
            ) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Transaksi sudah dikunci.'], 422)
                : back()->with('error', 'Transaksi sudah dikunci.');
        }

        $data = $request->validate([
            'jasa_id' => ['required', 'exists:jasas,id'],
            'harga_custom' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
        ]);
        $jasa = Jasa::findOrFail($data['jasa_id']);
        $harga = $request->filled('harga_custom') ? (float) $data['harga_custom'] : (float) $jasa->harga;
        // Setiap baris jasa adalah satu layanan (tidak memakai kuantitas).
        $qty = 1;
        $bruto = $harga;
        // Potongan harga hanya pada total nota, bukan per-item jasa.
        $detailId = DB::table('detail_jasas')->insertGetId([
            'rekam_servis_id' => $servis->id,
            'jasa_id' => $jasa->id,
            'qty' => $qty,
            'harga_saat_transaksi' => $harga,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Jasa berhasil ditambahkan.',
                'subtotal' => $servis->subtotal(),
                'detail' => [
                    'id' => $detailId, 'name' => $jasa->nama_jasa,
                    'qty' => $qty, 'diskon' => 0,
                    'unit_price' => $harga, 'subtotal' => $bruto,
                    'delete_url' => route('admin.rekam-servis.jasa.hapus', [$servis->id, $detailId]),
                ],
            ]);
        }
        return back()->with('success', 'Jasa berhasil ditambahkan ke nota.');
    })->name('admin.rekam-servis.jasa.tambah');

    Route::delete('/rekam-servis/{id}/jasa/{detailId}', function ($id, $detailId, Request $request) {
        $servis = RekamServis::findOrFail($id);

        if (
                $servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()
            ) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Transaksi sudah selesai dan terkunci.'], 422)
                : back()->with('error', 'Transaksi sudah selesai dan terkunci.');
        }

        DB::table('detail_jasas')
            ->where('rekam_servis_id', $servis->id)
            ->where('id', $detailId)
            ->delete();

        $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Jasa berhasil dihapus.',
                'subtotal' => $servis->subtotal(),
            ]);
        }

        return back()->with('success', 'Jasa berhasil dihapus dari nota.');
    })->name('admin.rekam-servis.jasa.hapus');

    /*
    |--------------------------------------------------------------------------
    | 3. DATA KONSUMEN & KENDARAAN
    |--------------------------------------------------------------------------
    */
    Route::get('/konsumen', function () {
        $konsumen = Konsumen::with([
            'kendaraans' => function ($query) {
                $query->orderBy('plat_nomor');
            },
        ])
            ->latest()
            ->get();

        return view('admin.konsumen.index', compact('konsumen'));
    })->name('admin.konsumen.index');

    Route::get('/konsumen/{konsumen}/edit', function (Konsumen $konsumen) {
        $konsumen->load([
            'kendaraans' => function ($query) {
                $query->with('rekomendasiNotaTerbuka')->orderBy('plat_nomor');
            },
        ]);

        return view('admin.konsumen.edit', compact('konsumen'));
    })->name('admin.konsumen.edit');

    /*
    |--------------------------------------------------------------------------
    | TAMBAH KONSUMEN BARU + BANYAK KENDARAAN
    |--------------------------------------------------------------------------
    */
    Route::post('/konsumen', function (Request $request) {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp'        => ['required', 'string', 'max:20', 'unique:konsumens,no_hp'],
            'kendaraan'    => ['required', 'array', 'min:1'],
            'kendaraan.*.plat_nomor'   => ['required', 'string', 'max:20'],
            'kendaraan.*.merk'         => ['required', 'string', 'max:100'],
            'kendaraan.*.tipe_model'   => ['required', 'string', 'max:100'],
            'kendaraan.*.tahun'        => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
            'kendaraan.*.cc'               => ['nullable', 'integer', 'min:300', 'max:10000'],
            'kendaraan.*.transmisi'        => ['nullable', 'string', 'in:manual,otomatis,cvt'],
            'kendaraan.*.jenis_bahan_bakar' => ['nullable', 'string', 'in:gasoline,diesel'],
        ], [
            'nama_lengkap.required'              => 'Nama konsumen wajib diisi.',
            'no_hp.required'                     => 'Nomor HP konsumen wajib diisi.',
            'no_hp.unique'                       => 'Nomor HP tersebut sudah terdaftar sebagai konsumen.',
            'kendaraan.required'                 => 'Minimal satu kendaraan harus ditambahkan.',
            'kendaraan.*.plat_nomor.required'    => 'Plat nomor kendaraan wajib diisi.',
            'kendaraan.*.merk.required'          => 'Merk kendaraan wajib diisi.',
            'kendaraan.*.tipe_model.required'    => 'Tipe atau model kendaraan wajib diisi.',
            'kendaraan.*.cc.min'                 => 'CC mesin minimal 300.',
            'kendaraan.*.cc.max'                 => 'CC mesin maksimal 10000.',
            'kendaraan.*.transmisi.in'           => 'Transmisi harus: manual, otomatis, atau cvt.',
            'kendaraan.*.jenis_bahan_bakar.in'   => 'Bahan bakar harus: gasoline atau diesel.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cek Duplikasi Plat
        |--------------------------------------------------------------------------
        */
        $platDalamForm = [];
        foreach ($request->kendaraan as $data) {
            $platNomor      = strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor'])));
            $platTanpaSpasi = str_replace(' ', '', $platNomor);

            if (in_array($platTanpaSpasi, $platDalamForm, true)) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} dimasukkan lebih dari satu kali.");
            }

            $platAda = Kendaraan::whereRaw("REPLACE(UPPER(plat_nomor), ' ', '') = ?", [$platTanpaSpasi])->exists();
            if ($platAda) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} sudah terdaftar pada kendaraan lain.");
            }

            $platDalamForm[] = $platTanpaSpasi;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Konsumen dan Kendaraan
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($request) {
            $dataKonsumen = Konsumen::create([
                'nama_lengkap' => trim($request->nama_lengkap),
                'no_hp'        => trim($request->no_hp),
            ]);

            foreach ($request->kendaraan as $data) {
                $dataKonsumen->kendaraans()->create([
                    'plat_nomor'   => strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor']))),
                    'merk'         => trim($data['merk']),
                    'tipe_model'   => trim($data['tipe_model']),
                    'tahun'        => ! empty($data['tahun']) ? (int) $data['tahun'] : null,

                    /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
                    'cc'                => ! empty($data['cc']) ? (int) $data['cc'] : null,
                    'transmisi'         => ! empty($data['transmisi']) ? $data['transmisi'] : null,
                    'jenis_bahan_bakar' => ! empty($data['jenis_bahan_bakar']) ? $data['jenis_bahan_bakar'] : null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Hubungkan User Pelanggan yang Sudah Register Lebih Dulu
            |--------------------------------------------------------------------------
            */
            User::where('role', 'pelanggan')
                ->where('no_hp', $dataKonsumen->no_hp)
                ->whereNull('konsumen_id')
                ->update(['konsumen_id' => $dataKonsumen->id]);
        });

        return redirect()
            ->route('admin.konsumen.index')
            ->with('success', 'Konsumen dan kendaraan berhasil ditambahkan.');
    })->name('admin.konsumen.store');

    /*
    |--------------------------------------------------------------------------
    | EDIT KONSUMEN + KENDARAAN EXISTING
    |--------------------------------------------------------------------------
    */
    Route::put('/konsumen/{konsumen}', function (Request $request, Konsumen $konsumen) {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp'        => ['required', 'string', 'max:20', 'unique:konsumens,no_hp,' . $konsumen->id],
            'kendaraan'    => ['nullable', 'array'],
            'kendaraan.*.plat_nomor'   => ['required', 'string', 'max:20'],
            'kendaraan.*.merk'         => ['required', 'string', 'max:100'],
            'kendaraan.*.tipe_model'   => ['required', 'string', 'max:100'],
            'kendaraan.*.tahun'        => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
            'kendaraan.*.cc'               => ['nullable', 'integer', 'min:300', 'max:10000'],
            'kendaraan.*.transmisi'        => ['nullable', 'string', 'in:manual,otomatis,cvt'],
            'kendaraan.*.jenis_bahan_bakar' => ['nullable', 'string', 'in:gasoline,diesel'],
        ], [
            'kendaraan.*.cc.min'               => 'CC mesin minimal 300.',
            'kendaraan.*.cc.max'               => 'CC mesin maksimal 10000.',
            'kendaraan.*.transmisi.in'         => 'Transmisi harus: manual, otomatis, atau cvt.',
            'kendaraan.*.jenis_bahan_bakar.in' => 'Bahan bakar harus: gasoline atau diesel.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Nomor HP Lama
        |--------------------------------------------------------------------------
        */
        $noHpLama = $konsumen->no_hp;

        /*
        |--------------------------------------------------------------------------
        | Validasi Kendaraan Existing
        |--------------------------------------------------------------------------
        */
        foreach ($request->input('kendaraan', []) as $kendaraanId => $data) {
            $kendaraan = $konsumen->kendaraans()->whereKey($kendaraanId)->firstOrFail();

            $platNomor      = strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor'])));
            $platTanpaSpasi = str_replace(' ', '', $platNomor);

            $platDipakai = Kendaraan::whereRaw("REPLACE(UPPER(plat_nomor), ' ', '') = ?", [$platTanpaSpasi])
                ->where('id', '!=', $kendaraan->id)
                ->exists();

            if ($platDipakai) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} sudah digunakan kendaraan lain.");
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Perubahan
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($request, $konsumen, $noHpLama) {
            /*
            |--------------------------------------------------------------------------
            | Update Konsumen
            |--------------------------------------------------------------------------
            */
            $konsumen->update([
                'nama_lengkap' => trim($request->nama_lengkap),
                'no_hp'        => trim($request->no_hp),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Kendaraan Existing
            |--------------------------------------------------------------------------
            */
            foreach ($request->input('kendaraan', []) as $kendaraanId => $data) {
                $kendaraan = $konsumen->kendaraans()->whereKey($kendaraanId)->firstOrFail();

                $kendaraan->update([
                    'plat_nomor'   => strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor']))),
                    'merk'         => trim($data['merk']),
                    'tipe_model'   => trim($data['tipe_model']),
                    'tahun'        => ! empty($data['tahun']) ? (int) $data['tahun'] : null,

                    /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
                    'cc'                => ! empty($data['cc']) ? (int) $data['cc'] : null,
                    'transmisi'         => ! empty($data['transmisi']) ? $data['transmisi'] : null,
                    'jenis_bahan_bakar' => ! empty($data['jenis_bahan_bakar']) ? $data['jenis_bahan_bakar'] : null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Sinkron Nomor HP User Pelanggan
            |--------------------------------------------------------------------------
            */
            $userPelanggan = User::where('role', 'pelanggan')
                ->where('konsumen_id', $konsumen->id)
                ->first();

            if ($userPelanggan) {
                $noHpBaru = trim($request->no_hp);

                if ($noHpLama !== $noHpBaru) {
                    $dipakaiUserLain = User::where('no_hp', $noHpBaru)
                        ->where('id', '!=', $userPelanggan->id)
                        ->exists();

                    if (! $dipakaiUserLain) {
                        $userPelanggan->update(['no_hp' => $noHpBaru]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.konsumen.index')
            ->with('success', 'Data konsumen dan kendaraan berhasil diperbarui.');
    })->name('admin.konsumen.update');

    /*
    |--------------------------------------------------------------------------
    | TAMBAH KENDARAAN KE KONSUMEN EXISTING
    |--------------------------------------------------------------------------
    */
    Route::post('/konsumen/{konsumen}/kendaraan', function (Request $request, Konsumen $konsumen) {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'kendaraan'    => ['required', 'array', 'min:1'],
            'kendaraan.*.plat_nomor'   => ['required', 'string', 'max:20'],
            'kendaraan.*.merk'         => ['required', 'string', 'max:100'],
            'kendaraan.*.tipe_model'   => ['required', 'string', 'max:100'],
            'kendaraan.*.tahun'        => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
            'kendaraan.*.cc'               => ['nullable', 'integer', 'min:300', 'max:10000'],
            'kendaraan.*.transmisi'        => ['nullable', 'string', 'in:manual,otomatis,cvt'],
            'kendaraan.*.jenis_bahan_bakar' => ['nullable', 'string', 'in:gasoline,diesel'],
        ], [
            'kendaraan.*.cc.min'               => 'CC mesin minimal 300.',
            'kendaraan.*.cc.max'               => 'CC mesin maksimal 10000.',
            'kendaraan.*.transmisi.in'         => 'Transmisi harus: manual, otomatis, atau cvt.',
            'kendaraan.*.jenis_bahan_bakar.in' => 'Bahan bakar harus: gasoline atau diesel.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cek Duplikasi Plat
        |--------------------------------------------------------------------------
        */
        $platDalamForm = [];
        foreach ($request->kendaraan as $data) {
            $platNomor      = strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor'])));
            $platTanpaSpasi = str_replace(' ', '', $platNomor);

            if (in_array($platTanpaSpasi, $platDalamForm, true)) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} dimasukkan lebih dari satu kali.");
            }

            $sudahAda = Kendaraan::whereRaw("REPLACE(UPPER(plat_nomor), ' ', '') = ?", [$platTanpaSpasi])->exists();
            if ($sudahAda) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} sudah terdaftar.");
            }

            $platDalamForm[] = $platTanpaSpasi;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Kendaraan Baru
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($request, $konsumen) {
            foreach ($request->kendaraan as $data) {
                $konsumen->kendaraans()->create([
                    'plat_nomor'   => strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor']))),
                    'merk'         => trim($data['merk']),
                    'tipe_model'   => trim($data['tipe_model']),
                    'tahun'        => ! empty($data['tahun']) ? (int) $data['tahun'] : null,

                    /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
                    'cc'                => ! empty($data['cc']) ? (int) $data['cc'] : null,
                    'transmisi'         => ! empty($data['transmisi']) ? $data['transmisi'] : null,
                    'jenis_bahan_bakar' => ! empty($data['jenis_bahan_bakar']) ? $data['jenis_bahan_bakar'] : null,
                ]);
            }
        });

        return redirect()
            ->route('admin.konsumen.index')
            ->with('success', 'Kendaraan baru berhasil ditambahkan ke ' . $konsumen->nama_lengkap . '.');
    })->name('admin.konsumen.kendaraan.store');

    /*
    |--------------------------------------------------------------------------
    | ROUTE KHUSUS ADMIN
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
            'nama_barang' => $request->nama_barang,
            'kode_barang' => $request->kode_barang,
            'stok'        => $request->stok ?? 0,
            'harga_modal' => $request->harga_modal,
            'harga_jual'  => $request->harga_jual,
            'nama_toko'   => $request->nama_toko,
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

    // =========================================================
    // 7. LAPORAN PEMASUKAN BENGKEL
    // =========================================================

    Route::get(
        '/laporan',
        [\App\Http\Controllers\Admin\LaporanController::class, 'index']
    )->name('admin.laporan.index');

    Route::get(
        '/laporan/cetak-pdf',
        [\App\Http\Controllers\Admin\LaporanController::class, 'cetakPdf']
    )->name('admin.laporan.cetak-pdf');

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

    // Selesaikan servis dan terbitkan SATU nota dengan status BELUM LUNAS.
    // Jangan menyimpan pembayaran di sini.
    Route::post('/rekam-servis/{id}/nota/konfirmasi', function ($id, Request $request) {
    abort_unless(
        auth()->check() &&
        in_array(auth()->user()->role, ['admin', 'anggota'], true),
        403
    );

    $data = $request->validate([
        'diskon_ribuan' => [
    'required', 'integer', 'min:0', 'max:999999999'],
    ]);

    DB::transaction(function () use ($id, $data) {
        $servis = RekamServis::whereKey($id)
            ->lockForUpdate()->firstOrFail();

        if (
                $servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()
            ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nota' => 'Servis ini sudah selesai atau tagihannya sudah diterbitkan.',
            ]);
        }

        $servis->load(['detailSpareparts', 'detailJasas']);

        if (
            $servis->detailSpareparts->isEmpty() &&
            $servis->detailJasas->isEmpty()
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nota' => 'Tambahkan sparepart atau jasa terlebih dahulu.',
            ]);
        }

        $subtotal = (int) round($servis->subtotal());

        $diskon = (int) $data['diskon_ribuan'] * 1000;

        $diskon = min($subtotal, max(0, $diskon));

        NotaTransaksi::create([
            'rekam_servis_id' => $servis->id,
            'no_nota' => 'NOTA-' . now()->format('Ymd')
                . '-RS' . str_pad((string) $servis->id, 6, '0', STR_PAD_LEFT),
            'tanggal_cetak' => now()->toDateString(),
            'tanggal_terbit_tagihan' => now(),
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total_biaya' => $subtotal - $diskon,
            'metode_cetak' => 'standar',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_lunas' => null,
            'catatan_nota' => null,
        ]);
    }, 3);

    return redirect()
        ->route('admin.rekam-servis.nota', $id)
        ->with('success', 'Tagihan servis berhasil diterbitkan.');

})->name('admin.rekam-servis.nota.konfirmasi');

/*
|--------------------------------------------------------------------------
| PEMBAYARAN NOTA - TIPS OTOMATIS
|--------------------------------------------------------------------------
*/

Route::post(
    '/rekam-servis/{id}/nota/bayar',
    [PembayaranNotaController::class, 'bayar']
)->name('admin.rekam-servis.nota.bayar');

    /*
|--------------------------------------------------------------------------
| DATA AKHIR & NOTA FINAL
|--------------------------------------------------------------------------
*/

Route::post('/rekam-servis/{id}/selesaikan', function ($id, Request $request) {
    abort_unless(
        auth()->check() &&
        in_array(auth()->user()->role, ['admin', 'anggota'], true),
        403
    );

    $data = $request->validate([
        'km_keluar' => ['required', 'integer', 'min:0', 'max:999999999'],
        'catatan_nota' => ['nullable', 'string', 'max:5000'],
        'rekomendasi_selesai' => ['nullable', 'array'],
        'rekomendasi_selesai.*' => ['integer', 'distinct'],
    ]);

    DB::transaction(function () use ($id, $data) {
        $servis = RekamServis::whereKey($id)
            ->lockForUpdate()->firstOrFail();

        $nota = NotaTransaksi::where('rekam_servis_id', $id)
            ->lockForUpdate()->firstOrFail();

        if ($servis->status === 'selesai' && $servis->km_keluar !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'servis' => 'Data akhir sudah tersimpan dan terkunci.',
            ]);
        }

        if ((int) $data['km_keluar'] < (int) $servis->km_masuk) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'km_keluar' => 'KM keluar tidak boleh kurang dari KM masuk.',
            ]);
        }

        // Konfirmasi hanya rekomendasi lama milik kendaraan yang sama.
        $ids = $data['rekomendasi_selesai'] ?? [];
        if ($ids) {
            $jumlahValid = NotaTransaksi::query()
                ->join('rekam_servis as lama', 'lama.id', '=', 'nota_transaksis.rekam_servis_id')
                ->where('lama.kendaraan_id', $servis->kendaraan_id)
                ->where('lama.id', '<>', $servis->id)
                ->where('nota_transaksis.status_rekomendasi', 'belum')
                ->whereIn('nota_transaksis.id', $ids)
                ->count();
            if ($jumlahValid !== count($ids)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'rekomendasi_selesai' => 'Rekomendasi tidak valid atau sudah ditangani.',
                ]);
            }
        }

        $servis->update([
            'km_keluar' => (int) $data['km_keluar'],
            'status' => 'selesai',
        ]);

        $catatanNota = trim((string) ($data['catatan_nota'] ?? ''));
        $nota->update([
            'catatan_nota' => $catatanNota !== '' ? $catatanNota : null,
            'status_rekomendasi' => $catatanNota !== '' ? 'belum' : null,
        ]);
        if ($ids) {
            NotaTransaksi::whereIn('id', $ids)
                ->where('status_rekomendasi', 'belum')
                ->update(['status_rekomendasi' => 'sudah']);
        }
    }, 3);

    return redirect()
        ->route('admin.rekam-servis.nota', $id)
        ->with('success', 'Data akhir pekerjaan berhasil disimpan.');

})->name('admin.rekam-servis.selesaikan');

    /*
|--------------------------------------------------------------------------
| LAPORAN INSPEKSI KENDARAAN
|--------------------------------------------------------------------------
*/

// 1. Daftar laporan inspeksi (GET)
Route::get('/inspeksi', function () {
    $inspeksis = \App\Models\LaporanInspeksi::with(['kendaraan.konsumen', 'user'])
        ->latest()
        ->get();
    return view('admin.inspeksi.index', compact('inspeksis'));
})->name('admin.inspeksi.index');

// 2. Form buat inspeksi baru (GET)
Route::get('/inspeksi/create', function () {
    $konsumens = \App\Models\Konsumen::with(['kendaraans' => fn($q) => $q->orderBy('plat_nomor')])
        ->orderBy('nama_lengkap')->get();
    $spareparts = \App\Models\Sparepart::latest()->get();
    $jasas = \App\Models\Jasa::latest()->get();

    $konsumensData = $konsumens->map(fn($k) => [
        'id' => $k->id,
        'nama' => $k->nama_lengkap,
        'no_hp' => $k->no_hp,
        'kendaraans' => $k->kendaraans->map(fn($v) => [
            'id' => $v->id,
            'plat_nomor' => $v->plat_nomor,
            'merk' => $v->merk,
            'tipe_model' => $v->tipe_model,
            'tahun' => $v->tahun,
            'cc' => $v->cc,
            'transmisi' => $v->transmisi,
            'transmisi_label' => $v->transmisi_label,
        ])->values()->all(),
    ])->values()->all();

    return view('admin.inspeksi.create', compact('konsumens', 'spareparts', 'jasas', 'konsumensData'));
})->name('admin.inspeksi.create');

// 3. Simpan inspeksi (POST)
Route::post('/inspeksi', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'konsumen_id' => 'required|exists:konsumens,id',
        'kendaraan_id' => 'required|exists:kendaraans,id',
        'tanggal_inspeksi' => 'required|date',
        'km_kendaraan' => 'required|integer|min:0',
        'rating_score' => 'required|integer|min:1|max:10',
    ]);

    \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
        $inspeksiId = \Illuminate\Support\Facades\DB::table('laporan_inspeksis')->insertGetId([
            'konsumen_id' => $request->konsumen_id,
            'kendaraan_id' => $request->kendaraan_id,
            'nomor_inspeksi' => \App\Models\LaporanInspeksi::generateNomorInspeksi(),
            'tanggal_inspeksi' => $request->tanggal_inspeksi,
            'km_kendaraan' => $request->km_kendaraan,
            'rating_score' => $request->rating_score,
            'ex_banjir' => $request->ex_banjir ?? 'tidak',
            'ex_tabrak' => $request->ex_tabrak ?? 'tidak',
            'kondisi_mesin' => $request->kondisi_mesin,
            'kondisi_transmisi' => $request->kondisi_transmisi,
            'kondisi_kaki_kaki' => $request->kondisi_kaki_kaki,
            'kondisi_eksterior' => $request->kondisi_eksterior,
            'kondisi_interior' => $request->kondisi_interior,
            'saran' => $request->saran,
            'user_id' => auth()->id(),
            'status' => 'selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Simpan KELISTRIKAN
        foreach ($request->input('kelistrikan', []) as $item) {
            if (!empty($item['komponen'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_kelistrikan')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'komponen' => $item['komponen'],
                    'status' => $item['status'] ?? 'Baik',
                    'keterangan' => $item['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Simpan INTERIOR
        foreach ($request->input('interior', []) as $item) {
            if (!empty($item['komponen'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_interior')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'komponen' => $item['komponen'],
                    'status' => $item['status'] ?? 'Baik',
                    'keterangan' => $item['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Simpan KAKI-KAKI
        foreach ($request->input('kaki_kaki', []) as $item) {
            if (!empty($item['komponen'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_kaki_kaki')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'komponen' => $item['komponen'],
                    'status_kiri' => $item['status_kiri'] ?? 'Baik',
                    'status_kanan' => $item['status_kanan'] ?? 'Baik',
                    'catatan' => $item['catatan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Simpan EXTERIOR
        foreach ($request->input('exterior', []) as $item) {
            if (!empty($item['komponen'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_exterior')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'komponen' => $item['komponen'],
                    'status' => $item['status'] ?? 'Baik',
                    'keterangan' => $item['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Simpan MESIN
        foreach ($request->input('mesin', []) as $item) {
            if (!empty($item['komponen'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_mesin')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'komponen' => $item['komponen'],
                    'status' => $item['status'] ?? 'Baik',
                    'keterangan' => $item['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Simpan DOKUMEN
        foreach ($request->input('dokumen', []) as $item) {
            if (!empty($item['dokumen'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_dokumen')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'dokumen' => $item['dokumen'],
                    'status' => $item['status'] ?? 'Ya',
                    'keterangan' => $item['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Simpan ESTIMASI
        foreach ($request->input('estimasi', []) as $item) {
            if (!empty($item['nama_item'])) {
                \Illuminate\Support\Facades\DB::table('inspeksi_estimasi')->insert([
                    'laporan_inspeksi_id' => $inspeksiId,
                    'kategori' => $item['kategori'] ?? 'engine',
                    'sparepart_id' => !empty($item['sparepart_id']) ? $item['sparepart_id'] : null,
                    'jasa_id' => !empty($item['jasa_id']) ? $item['jasa_id'] : null,
                    'nama_item' => $item['nama_item'],
                    'qty' => 1,
                    'harga_part' => $item['harga_part'] ?? 0,
                    'harga_jasa' => $item['harga_jasa'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    });

    return redirect()->route('admin.inspeksi.index')->with('success', 'Laporan inspeksi berhasil dibuat.');
})->name('admin.inspeksi.store');

// 4. Detail inspeksi (GET)
Route::get('/inspeksi/{id}', function ($id) {
    $inspeksi = \App\Models\LaporanInspeksi::with([
        'konsumen', 'kendaraan', 'user', 'kelistrikan', 'interior', 'kakiKaki',
        'exterior', 'mesin', 'dokumen', 'estimasi.sparepart', 'estimasi.jasa',
    ])->findOrFail($id);
    return view('admin.inspeksi.show', compact('inspeksi'));
})->name('admin.inspeksi.show');

// 5. Hapus inspeksi (DELETE)
Route::delete('/inspeksi/{id}', function ($id) {
    \App\Models\LaporanInspeksi::findOrFail($id)->delete();
    return redirect()->route('admin.inspeksi.index')->with('success', 'Laporan inspeksi dihapus.');
})->name('admin.inspeksi.destroy');
});