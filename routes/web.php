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
        'no_hp.required'
            => 'Username atau nomor HP wajib diisi.',

        'password.required'
            => 'Kata sandi wajib diisi.',
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
    $loginInput = trim(
        $request->input('no_hp')
    );

    $password = $request->input(
        'password'
    );

    $remember = $request->boolean(
        'remember'
    );


    /*
    |--------------------------------------------------------------------------
    | Percobaan 1: login menggunakan nomor HP
    |--------------------------------------------------------------------------
    */
    $berhasil = Auth::attempt(
        [
            'no_hp' => $loginInput,
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
        ->withInput(
            $request->only('no_hp')
        )
        ->withErrors([
            'no_hp'
                => 'Username / Nomor HP atau kata sandi salah.',
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

    $username = strtolower(
        trim((string) $request->query('username'))
    );

    if ($username === '') {
        return response()->json([
            'available' => false,
            'message' => 'Username wajib diisi.',
        ]);
    }

    if (strlen($username) < 3) {
        return response()->json([
            'available' => false,
            'message' => 'Username minimal 3 karakter.',
        ]);
    }

    if (strlen($username) > 50) {
        return response()->json([
            'available' => false,
            'message' => 'Username maksimal 50 karakter.',
        ]);
    }

    if (! preg_match('/^[A-Za-z0-9_-]+$/', $username)) {
        return response()->json([
            'available' => false,
            'message' => 'Username hanya boleh berisi huruf, angka, underscore, atau tanda hubung.',
        ]);
    }

    $usernameSudahAda = User::whereRaw(
        'LOWER(username) = ?',
        [$username]
    )->exists();

    if ($usernameSudahAda) {
        return response()->json([
            'available' => false,
            'message' => 'Username sudah digunakan.',
        ]);
    }

    return response()->json([
        'available' => true,
        'message' => 'Username tersedia.',
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
        'nama_lengkap.required'
            => 'Nama lengkap wajib diisi.',

        'username.required'
            => 'Username wajib diisi.',

        'username.min'
            => 'Username minimal 3 karakter.',

        'username.max'
            => 'Username maksimal 50 karakter.',

        'username.alpha_dash'
            => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan underscore.',

        'username.unique'
            => 'Username sudah digunakan. Silakan gunakan username lain.',

        'no_hp.required'
            => 'Nomor HP wajib diisi.',

        'no_hp.unique'
            => 'Nomor HP sudah terdaftar. Silakan masuk menggunakan akun tersebut.',

        'password.required'
            => 'Kata sandi wajib diisi.',

        'password.min'
            => 'Kata sandi minimal 6 karakter.',

        'password.confirmed'
            => 'Konfirmasi kata sandi tidak cocok.',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Cari data konsumen berdasarkan nomor HP
    |--------------------------------------------------------------------------
    */
    $konsumen = Konsumen::where(
        'no_hp',
        $request->no_hp
    )->first();


    /*
    |--------------------------------------------------------------------------
    | Simpan akun baru
    |--------------------------------------------------------------------------
    */
    User::create([
        'name' => trim($request->nama_lengkap),

        'username' => strtolower(
            trim($request->username)
        ),

        'no_hp' => trim($request->no_hp),

        'role' => 'pelanggan',

        'konsumen_id' => $konsumen
            ? $konsumen->id
            : null,

        'password' => bcrypt(
            $request->password
        ),
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
        Route::get(
            '/dashboard',
            [PelangganController::class, 'dashboard']
        )->name('pelanggan.dashboard');


        // Daftar kendaraan pelanggan
        Route::get(
            '/kendaraan',
            [PelangganController::class, 'kendaraan']
        )->name('pelanggan.kendaraan');


        // Semua riwayat servis pelanggan
        Route::get(
            '/riwayat',
            [PelangganController::class, 'riwayat']
        )->name('pelanggan.riwayat');


        // Riwayat servis berdasarkan satu kendaraan
        Route::get(
            '/kendaraan/{kendaraan}/riwayat',
            [PelangganController::class, 'riwayatKendaraan']
        )->name('pelanggan.kendaraan.riwayat');


        // Detail satu rekam servis
        Route::get(
            '/servis/{id}',
            [PelangganController::class, 'detailServis']
        )->name('pelanggan.detail-servis');


        /*
        |--------------------------------------------------------------------------
        | Route Lama - Kompatibilitas
        |--------------------------------------------------------------------------
        |
        | Untuk sementara tetap dipertahankan agar link lama yang masih
        | menggunakan pelanggan.rekam-servis tidak langsung error.
        |
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

Route::prefix('admin')->middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 1. DASHBOARD UTAMA
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {

        $totalKonsumen = Konsumen::count();

        $totalKendaraan = Kendaraan::count();

        $servisHariIni = RekamServis::whereDate(
            'tanggal_servis',
            now()->toDateString()
        )->count();


        /*
         * Menyesuaikan seluruh kemungkinan status servis berjalan.
         */
        $servisBerjalan = RekamServis::whereIn(
            'status',
            [
                'menunggu_pengerjaan',
                'sedang_dikerjakan',
                'diproses',
                'dikerjakan',
                'proses',
            ]
        )->count();


        $servisMenunggu = RekamServis::where(
            'status',
            'menunggu_pengerjaan'
        )->count();


        /*
        |--------------------------------------------------------------------------
        | Total Omzet
        |--------------------------------------------------------------------------
        */

        $totalOmzet = 0;

        if (Schema::hasTable('nota_transaksis')) {

            $totalOmzet = NotaTransaksi::where(
                'status_pembayaran',
                'lunas'
            )->sum('total_biaya');

        } elseif (Schema::hasTable('nota_transaksi')) {

            $totalOmzet = DB::table('nota_transaksi')
                ->where(
                    'status_pembayaran',
                    'lunas'
                )
                ->sum('total_biaya');
        }


        /*
        |--------------------------------------------------------------------------
        | Antrean Terbaru
        |--------------------------------------------------------------------------
        */

        $antreanTerbaru = RekamServis::with([
                'kendaraan',
                'kendaraan.konsumen',
            ])
            ->latest()
            ->take(5)
            ->get();


        return view(
            'admin.dashboard',
            compact(
                'totalKonsumen',
                'totalKendaraan',
                'servisHariIni',
                'servisBerjalan',
                'servisMenunggu',
                'totalOmzet',
                'antreanTerbaru'
            )
        );

    })->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | 2. REKAM SERVIS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Daftar Rekam Servis
    |--------------------------------------------------------------------------
    */

    Route::get('/rekam-servis', function () {

        $servis = RekamServis::with([
                'kendaraan',
                'kendaraan.konsumen',
            ])
            ->latest()
            ->get();


        return view(
            'admin.rekam-servis.index',
            compact('servis')
        );

    })->name('admin.rekam-servis.index');


    /*
    |--------------------------------------------------------------------------
    | Form Input Servis Baru
    |--------------------------------------------------------------------------
    |
    | Sekarang bukan mengirim seluruh kendaraan menjadi satu dropdown.
    |
    | Yang dikirim:
    |
    | Konsumen
    |    └── Kendaraan milik konsumen tersebut
    |
    */

    Route::get('/rekam-servis/create', function () {

        $konsumens = Konsumen::with([
                'kendaraans' => function ($query) {

                    $query->orderBy('plat_nomor');
                },
            ])
            ->orderBy('nama_lengkap')
            ->get();


        return view(
            'admin.rekam-servis.create',
            compact('konsumens')
        );

    })->name('admin.rekam-servis.create');


    /*
    |--------------------------------------------------------------------------
    | Simpan Rekam Servis
    |--------------------------------------------------------------------------
    */

    Route::post('/rekam-servis', function (Request $request) {

        /*
        |--------------------------------------------------------------------------
        | Validasi Input
        |--------------------------------------------------------------------------
        */

        $request->validate(
            [
                'konsumen_id' => [
                    'required',
                    'exists:konsumens,id',
                ],

                'kendaraan_id' => [
                    'required',
                    'exists:kendaraans,id',
                ],

                'km_akhir' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'keluhan_awal' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'konsumen_id.required'
                    => 'Konsumen wajib dipilih.',

                'konsumen_id.exists'
                    => 'Data konsumen tidak ditemukan.',

                'kendaraan_id.required'
                    => 'Plat kendaraan wajib dipilih.',

                'kendaraan_id.exists'
                    => 'Kendaraan yang dipilih tidak ditemukan.',

                'km_akhir.required'
                    => 'Kilometer terkini wajib diisi.',

                'km_akhir.integer'
                    => 'Kilometer harus berupa angka.',

                'km_akhir.min'
                    => 'Kilometer tidak boleh kurang dari 0.',

                'keluhan_awal.required'
                    => 'Keluhan konsumen wajib diisi.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Pastikan Kendaraan Milik Konsumen yang Dipilih
        |--------------------------------------------------------------------------
        */

        $kendaraan = Kendaraan::where(
                'id',
                $request->kendaraan_id
            )
            ->where(
                'konsumen_id',
                $request->konsumen_id
            )
            ->first();


        if (! $kendaraan) {

            return back()
                ->withInput()
                ->withErrors([
                    'kendaraan_id'
                        => 'Kendaraan tersebut tidak terdaftar atas nama konsumen yang dipilih.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Keluhan Konsumen
        |--------------------------------------------------------------------------
        */

        $keluhanInput = trim(
            $request->keluhan_awal
        );


        /*
        |--------------------------------------------------------------------------
        | Ambil Master Diagnosa
        |--------------------------------------------------------------------------
        */

        $semuaAturan = AturanDiagnosa::all();

        $diagnosaTerpilih = 'Pengecekan Umum';

        $skorTertinggi = 0;


        /*
        |--------------------------------------------------------------------------
        | Fungsi Similarity Diagnosa
        |--------------------------------------------------------------------------
        |
        | Logika lama tetap dipertahankan.
        |
        */

        $hitungKemiripan = function (
            $teks1,
            $teks2
        ) {

            $stopWords = [
                'dan',
                'atau',
                'di',
                'ke',
                'dari',
                'yang',
                'untuk',
                'pada',
                'dengan',
                'ada',
                'ini',
                'itu',
                'saat',
                'ketika',
            ];


            $bersihkan = function (
                $str
            ) use (
                $stopWords
            ) {

                $str = strtolower(
                    preg_replace(
                        '/[^a-zA-Z0-9\s]/',
                        '',
                        $str
                    )
                );


                $kataArray = preg_split(
                    '/\s+/',
                    trim($str)
                );


                return array_values(
                    array_diff(
                        $kataArray,
                        $stopWords,
                        ['']
                    )
                );
            };


            $kata1 = array_unique(
                $bersihkan($teks1)
            );

            $kata2 = array_unique(
                $bersihkan($teks2)
            );


            if (
                empty($kata1)
                ||
                empty($kata2)
            ) {
                return 0;
            }


            $irisan = array_intersect(
                $kata1,
                $kata2
            );


            $gabungan = array_unique(
                array_merge(
                    $kata1,
                    $kata2
                )
            );


            if (count($gabungan) === 0) {
                return 0;
            }


            return
                count($irisan)
                /
                count($gabungan);
        };


        /*
        |--------------------------------------------------------------------------
        | Cari Diagnosa Paling Sesuai
        |--------------------------------------------------------------------------
        */

        foreach ($semuaAturan as $aturan) {

            /*
             * Keyword ditemukan langsung.
             */
            if (
                stripos(
                    $keluhanInput,
                    $aturan->kata_kunci
                ) !== false
            ) {

                $skorTertinggi = 1.0;


                $diagnosaTerpilih =
                    $aturan->diagnosa_terkait
                    ??
                    $aturan->hasil_diagnosa;


                break;
            }


            /*
             * Similarity.
             */
            $skor = $hitungKemiripan(
                $keluhanInput,
                $aturan->kata_kunci
            );


            if ($skor > $skorTertinggi) {

                $skorTertinggi = $skor;


                $diagnosaTerpilih =
                    $aturan->diagnosa_terkait
                    ??
                    $aturan->hasil_diagnosa;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Jika kecocokan terlalu kecil
        |--------------------------------------------------------------------------
        */

        if ($skorTertinggi < 0.15) {

            $diagnosaTerpilih =
                'Pengecekan Umum';
        }


        /*
        |--------------------------------------------------------------------------
        | Simpan Rekam Servis
        |--------------------------------------------------------------------------
        |
        | Yang disimpan tetap kendaraan_id.
        |
        | BUKAN:
        |
        | plat_nomor
        |
        */

        RekamServis::create([
            'kendaraan_id'
                => $kendaraan->id,

            'km_akhir'
                => $request->km_akhir,

            'keluhan_awal'
                => $keluhanInput,

            'diagnosa_awal'
                => $diagnosaTerpilih,

            'tanggal_servis'
                => now(),

            'status'
                => 'menunggu_pengerjaan',

            'user_id'
                => auth()->id(),
        ]);


        return redirect()
            ->route('admin.rekam-servis.index')
            ->with(
                'success',
                'Rekam servis '
                . $kendaraan->plat_nomor
                . ' berhasil dibuat dengan deteksi diagnosa otomatis.'
            );

    })->name('admin.rekam-servis.store');


    /*
    |--------------------------------------------------------------------------
    | Form Koreksi Diagnosa
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rekam-servis/{id}/koreksi',
        function ($id) {

            $servis = RekamServis::with([
                    'kendaraan.konsumen',
                ])
                ->findOrFail($id);


            return view(
                'admin.rekam-servis.koreksi',
                compact('servis')
            );

        }
    )->name('admin.rekam-servis.koreksi-form');


    /*
    |--------------------------------------------------------------------------
    | Simpan Koreksi Diagnosa
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rekam-servis/{id}/koreksi',
        function (
            Request $request,
            $id
        ) {

            $request->validate([
                'diagnosa_akhir' => [
                    'required',
                    'string',
                    'max:2000',
                ],

                'tindakan_servis' => [
                    'required',
                    'string',
                    'max:5000',
                ],
            ]);


            $servis = RekamServis::findOrFail(
                $id
            );


            $servis->update([
                'diagnosa_akhir'
                    => $request->diagnosa_akhir,

                'tindakan_servis'
                    => $request->tindakan_servis,

                'status'
                    => 'sedang_dikerjakan',
            ]);


            return redirect()
                ->route('admin.rekam-servis.index')
                ->with(
                    'success',
                    'Koreksi diagnosa dan tindakan berhasil disimpan.'
                );

        }
    )->name('admin.rekam-servis.koreksi');


    /*
    |--------------------------------------------------------------------------
    | Halaman Nota Transaksi
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rekam-servis/{id}/nota',
        function ($id) {

            $servis = RekamServis::with([
                    'kendaraan.konsumen',
                    'detailSpareparts.sparepart',
                    'detailJasas.jasa',
                    'catatanRekomendasis',
                    'nota',
                ])
                ->findOrFail($id);


            $spareparts = Sparepart::latest()
                ->get();


            $jasas = Jasa::latest()
                ->get();


            return view(
                'admin.rekam-servis.nota',
                compact(
                    'servis',
                    'spareparts',
                    'jasas'
                )
            );

        }
    )->name('admin.rekam-servis.nota');


    /*
|--------------------------------------------------------------------------
| TAMBAH SPAREPART KE NOTA
|--------------------------------------------------------------------------
*/
Route::post(
    '/rekam-servis/{id}/sparepart',
    function (
        $id,
        Request $request
    ) {

        $servis = RekamServis::findOrFail($id);

        if ($servis->status === 'selesai') {
            return $request->expectsJson()
                ? response()->json([
                    'message' => 'Transaksi sudah selesai dan tidak dapat diubah.',
                ], 422)
                : back()->with(
                    'error',
                    'Transaksi sudah selesai dan tidak dapat diubah.'
                );
        }


        $request->validate([
            'sparepart_id' => [
                'required',
                'exists:spareparts,id',
            ],

            'qty' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],
        ]);


        $sparepart = Sparepart::findOrFail(
            $request->sparepart_id
        );


        /*
        |--------------------------------------------------------------------------
        | Jika sparepart sudah ada pada nota yang sama,
        | cukup tambahkan qty.
        |--------------------------------------------------------------------------
        */
        $detailLama = DB::table(
                'detail_spareparts'
            )
            ->where(
                'rekam_servis_id',
                $servis->id
            )
            ->where(
                'sparepart_id',
                $sparepart->id
            )
            ->first();


        if ($detailLama) {

            $qtyBaru =
                (int) $detailLama->qty
                +
                (int) $request->qty;


            DB::table(
                'detail_spareparts'
            )
                ->where(
                    'id',
                    $detailLama->id
                )
                ->update([
                    'qty' => $qtyBaru,
                    'updated_at' => now(),
                ]);


            $detailId =
                $detailLama->id;


            /*
             * Harga transaksi lama tetap dipertahankan.
             */
            $hargaSatuan =
                $detailLama
                    ->harga_jual_saat_transaksi;

        } else {

            $qtyBaru =
                (int) $request->qty;


            $hargaSatuan =
                $sparepart->harga_jual;


            $detailId = DB::table(
                    'detail_spareparts'
                )
                ->insertGetId([
                    'rekam_servis_id'
                        => $servis->id,

                    'sparepart_id'
                        => $sparepart->id,

                    'qty'
                        => $qtyBaru,

                    'harga_modal_saat_transaksi'
                        => $sparepart->harga_modal ?? 0,

                    'harga_jual_saat_transaksi'
                        => $hargaSatuan,

                    'created_at'
                        => now(),

                    'updated_at'
                        => now(),
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Hitung ulang subtotal
        |--------------------------------------------------------------------------
        */
        $servis->load([
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
        ]);


        $subtotal =
            $servis->subtotal();


        if ($request->expectsJson()) {

            return response()->json([
                'message'
                    => 'Sparepart berhasil ditambahkan.',

                'subtotal'
                    => $subtotal,

                'detail' => [
                    'id'
                        => $detailId,

                    'name'
                        => $sparepart->nama_barang,

                    'qty'
                        => $qtyBaru,

                    'unit_price'
                        => (float) $hargaSatuan,

                    'subtotal'
                        => (float) $hargaSatuan * $qtyBaru,

                    'qty_url'
                        => route(
                            'admin.rekam-servis.sparepart.qty',
                            [
                                $servis->id,
                                $detailId,
                            ]
                        ),

                    'delete_url'
                        => route(
                            'admin.rekam-servis.sparepart.hapus',
                            [
                                $servis->id,
                                $detailId,
                            ]
                        ),
                ],
            ]);
        }


        return back()->with(
            'success',
            'Sparepart berhasil ditambahkan ke nota.'
        );

    }
)->name(
    'admin.rekam-servis.sparepart.tambah'
);


/*
|--------------------------------------------------------------------------
| UPDATE QTY SPAREPART
|--------------------------------------------------------------------------
*/
Route::patch(
    '/rekam-servis/{id}/sparepart/{detailId}/qty',
    function (
        $id,
        $detailId,
        Request $request
    ) {

        $servis = RekamServis::findOrFail($id);

        if ($servis->status === 'selesai') {
            return response()->json([
                'message'
                    => 'Transaksi sudah selesai dan terkunci.',
            ], 422);
        }


        $request->validate([
            'qty' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],
        ]);


        $detail = DB::table(
                'detail_spareparts'
            )
            ->where(
                'id',
                $detailId
            )
            ->where(
                'rekam_servis_id',
                $servis->id
            )
            ->first();


        if (! $detail) {

            return response()->json([
                'message'
                    => 'Detail sparepart tidak ditemukan.',
            ], 404);
        }


        DB::table(
            'detail_spareparts'
        )
            ->where(
                'id',
                $detailId
            )
            ->update([
                'qty'
                    => (int) $request->qty,

                'updated_at'
                    => now(),
            ]);


        $servis->load([
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
        ]);


        return response()->json([
            'message'
                => 'Jumlah sparepart diperbarui.',

            'qty'
                => (int) $request->qty,

            'item_subtotal'
                => (float)
                    $detail
                        ->harga_jual_saat_transaksi
                    *
                    (int) $request->qty,

            'subtotal'
                => $servis->subtotal(),
        ]);

    }
)->name(
    'admin.rekam-servis.sparepart.qty'
);


/*
|--------------------------------------------------------------------------
| HAPUS SPAREPART
|--------------------------------------------------------------------------
*/
Route::delete(
    '/rekam-servis/{id}/sparepart/{detailId}',
    function (
        $id,
        $detailId,
        Request $request
    ) {

        $servis = RekamServis::findOrFail($id);


        if ($servis->status === 'selesai') {

            return $request->expectsJson()
                ? response()->json([
                    'message'
                        => 'Transaksi sudah selesai dan terkunci.',
                ], 422)
                : back()->with(
                    'error',
                    'Transaksi sudah selesai dan terkunci.'
                );
        }


        DB::table(
            'detail_spareparts'
        )
            ->where(
                'rekam_servis_id',
                $servis->id
            )
            ->where(
                'id',
                $detailId
            )
            ->delete();


        $servis->load([
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
        ]);


        if ($request->expectsJson()) {

            return response()->json([
                'message'
                    => 'Sparepart berhasil dihapus.',

                'subtotal'
                    => $servis->subtotal(),
            ]);
        }


        return back()->with(
            'success',
            'Sparepart berhasil dihapus dari nota.'
        );

    }
)->name(
    'admin.rekam-servis.sparepart.hapus'
);


/*
|--------------------------------------------------------------------------
| TAMBAH JASA
|--------------------------------------------------------------------------
*/
Route::post(
    '/rekam-servis/{id}/jasa',
    function (
        $id,
        Request $request
    ) {

        $servis = RekamServis::findOrFail($id);


        if ($servis->status === 'selesai') {

            return $request->expectsJson()
                ? response()->json([
                    'message'
                        => 'Transaksi sudah selesai dan terkunci.',
                ], 422)
                : back()->with(
                    'error',
                    'Transaksi sudah selesai dan terkunci.'
                );
        }


        $request->validate([
            'jasa_id' => [
                'required',
                'exists:jasas,id',
            ],

            'harga_custom' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        $jasa = Jasa::findOrFail(
            $request->jasa_id
        );


        $harga =
            $request->filled('harga_custom')
                ? (float) $request->harga_custom
                : (float) $jasa->harga;


        $detailId = DB::table(
                'detail_jasas'
            )
            ->insertGetId([
                'rekam_servis_id'
                    => $servis->id,

                'jasa_id'
                    => $jasa->id,

                'harga_saat_transaksi'
                    => $harga,

                'created_at'
                    => now(),

                'updated_at'
                    => now(),
            ]);


        $servis->load([
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
        ]);


        if ($request->expectsJson()) {

            return response()->json([
                'message'
                    => 'Jasa berhasil ditambahkan.',

                'subtotal'
                    => $servis->subtotal(),

                'detail' => [
                    'id'
                        => $detailId,

                    'name'
                        => $jasa->nama_jasa,

                    'unit_price'
                        => $harga,

                    'subtotal'
                        => $harga,

                    'delete_url'
                        => route(
                            'admin.rekam-servis.jasa.hapus',
                            [
                                $servis->id,
                                $detailId,
                            ]
                        ),
                ],
            ]);
        }


        return back()->with(
            'success',
            'Jasa berhasil ditambahkan ke nota.'
        );

    }
)->name(
    'admin.rekam-servis.jasa.tambah'
);


/*
|--------------------------------------------------------------------------
| HAPUS JASA
|--------------------------------------------------------------------------
*/
Route::delete(
    '/rekam-servis/{id}/jasa/{detailId}',
    function (
        $id,
        $detailId,
        Request $request
    ) {

        $servis = RekamServis::findOrFail($id);


        if ($servis->status === 'selesai') {

            return $request->expectsJson()
                ? response()->json([
                    'message'
                        => 'Transaksi sudah selesai dan terkunci.',
                ], 422)
                : back()->with(
                    'error',
                    'Transaksi sudah selesai dan terkunci.'
                );
        }


        DB::table(
            'detail_jasas'
        )
            ->where(
                'rekam_servis_id',
                $servis->id
            )
            ->where(
                'id',
                $detailId
            )
            ->delete();


        $servis->load([
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
        ]);


        if ($request->expectsJson()) {

            return response()->json([
                'message'
                    => 'Jasa berhasil dihapus.',

                'subtotal'
                    => $servis->subtotal(),
            ]);
        }


        return back()->with(
            'success',
            'Jasa berhasil dihapus dari nota.'
        );

    }
)->name(
    'admin.rekam-servis.jasa.hapus'
);

    /*
    |--------------------------------------------------------------------------
    | 3. DATA KONSUMEN & KENDARAAN
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Daftar Konsumen
    |--------------------------------------------------------------------------
    */

    Route::get('/konsumen', function () {

        $konsumen = Konsumen::with([
                'kendaraans' => function ($query) {

                    $query->orderBy(
                        'plat_nomor'
                    );
                },
            ])
            ->latest()
            ->get();


        return view(
            'admin.konsumen.index',
            compact('konsumen')
        );

    })->name('admin.konsumen.index');


    /*
|--------------------------------------------------------------------------
| Halaman Kelola Konsumen
|--------------------------------------------------------------------------
*/

Route::get(
    '/konsumen/{konsumen}/edit',
    function (
        Konsumen $konsumen
    ) {

        $konsumen->load([
            'kendaraans' => function ($query) {

                $query->orderBy(
                    'plat_nomor'
                );
            },
        ]);


        return view(
            'admin.konsumen.edit',
            compact('konsumen')
        );

    }
)->name('admin.konsumen.edit');


    /*
    |--------------------------------------------------------------------------
    | Tambah Konsumen Baru + Banyak Kendaraan
    |--------------------------------------------------------------------------
    */

    Route::post('/konsumen', function (
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        $request->validate(
            [
                'nama_lengkap' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'no_hp' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:konsumens,no_hp',
                ],

                'kendaraan' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'kendaraan.*.plat_nomor' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'kendaraan.*.merk' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'kendaraan.*.tipe_model' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'kendaraan.*.tahun' => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 1),
                ],
            ],
            [
                'nama_lengkap.required'
                    => 'Nama konsumen wajib diisi.',

                'no_hp.required'
                    => 'Nomor HP konsumen wajib diisi.',

                'no_hp.unique'
                    => 'Nomor HP tersebut sudah terdaftar sebagai konsumen.',

                'kendaraan.required'
                    => 'Minimal satu kendaraan harus ditambahkan.',

                'kendaraan.*.plat_nomor.required'
                    => 'Plat nomor kendaraan wajib diisi.',

                'kendaraan.*.merk.required'
                    => 'Merk kendaraan wajib diisi.',

                'kendaraan.*.tipe_model.required'
                    => 'Tipe atau model kendaraan wajib diisi.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Cek Duplikasi Plat
        |--------------------------------------------------------------------------
        */

        $platDalamForm = [];


        foreach (
            $request->kendaraan
            as $data
        ) {

            $platNomor = strtoupper(
                preg_replace(
                    '/\s+/',
                    ' ',
                    trim($data['plat_nomor'])
                )
            );


            $platTanpaSpasi = str_replace(
                ' ',
                '',
                $platNomor
            );


            /*
             * Duplikat pada form yang sama.
             */
            if (
                in_array(
                    $platTanpaSpasi,
                    $platDalamForm,
                    true
                )
            ) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        "Plat nomor {$platNomor} dimasukkan lebih dari satu kali."
                    );
            }


            /*
             * Duplikat dengan database.
             */
            $platAda = Kendaraan::whereRaw(
                    "REPLACE(UPPER(plat_nomor), ' ', '') = ?",
                    [
                        $platTanpaSpasi,
                    ]
                )
                ->exists();


            if ($platAda) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        "Plat nomor {$platNomor} sudah terdaftar pada kendaraan lain."
                    );
            }


            $platDalamForm[] =
                $platTanpaSpasi;
        }


        /*
        |--------------------------------------------------------------------------
        | Simpan Konsumen dan Kendaraan
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request
            ) {

                $dataKonsumen = Konsumen::create([
                    'nama_lengkap'
                        => trim(
                            $request->nama_lengkap
                        ),

                    'no_hp'
                        => trim(
                            $request->no_hp
                        ),
                ]);


                foreach (
                    $request->kendaraan
                    as $data
                ) {

                    $dataKonsumen
                        ->kendaraans()
                        ->create([
                            'plat_nomor'
                                => strtoupper(
                                    preg_replace(
                                        '/\s+/',
                                        ' ',
                                        trim(
                                            $data['plat_nomor']
                                        )
                                    )
                                ),

                            'merk'
                                => trim(
                                    $data['merk']
                                ),

                            'tipe_model'
                                => trim(
                                    $data['tipe_model']
                                ),

                            'tahun'
                                => ! empty(
                                    $data['tahun']
                                )
                                    ? (int) $data['tahun']
                                    : null,
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Hubungkan User Pelanggan yang Sudah Register Lebih Dulu
                |--------------------------------------------------------------------------
                |
                | Contoh:
                |
                | pelanggan register dahulu
                | users.konsumen_id = NULL
                |
                | lalu admin baru membuat data konsumennya.
                |
                | Jika nomor HP sama, hubungkan otomatis.
                |
                */

                User::where(
                        'role',
                        'pelanggan'
                    )
                    ->where(
                        'no_hp',
                        $dataKonsumen->no_hp
                    )
                    ->whereNull(
                        'konsumen_id'
                    )
                    ->update([
                        'konsumen_id'
                            => $dataKonsumen->id,
                    ]);
            }
        );


        return redirect()
            ->route(
                'admin.konsumen.index'
            )
            ->with(
                'success',
                'Konsumen dan kendaraan berhasil ditambahkan.'
            );

    })->name('admin.konsumen.store');


    /*
    |--------------------------------------------------------------------------
    | Edit Konsumen + Kendaraan Existing
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/konsumen/{konsumen}',
        function (
            Request $request,
            Konsumen $konsumen
        ) {

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

                'no_hp' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:konsumens,no_hp,'
                        . $konsumen->id,
                ],

                'kendaraan' => [
                    'nullable',
                    'array',
                ],

                'kendaraan.*.plat_nomor' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'kendaraan.*.merk' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'kendaraan.*.tipe_model' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'kendaraan.*.tahun' => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 1),
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | Nomor HP Lama
            |--------------------------------------------------------------------------
            */

            $noHpLama =
                $konsumen->no_hp;


            /*
            |--------------------------------------------------------------------------
            | Validasi Kendaraan Existing
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->input(
                    'kendaraan',
                    []
                )
                as $kendaraanId => $data
            ) {

                /*
                 * Pastikan kendaraan yang dikirim
                 * memang milik konsumen tersebut.
                 */
                $kendaraan = $konsumen
                    ->kendaraans()
                    ->whereKey(
                        $kendaraanId
                    )
                    ->firstOrFail();


                $platNomor = strtoupper(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        trim(
                            $data['plat_nomor']
                        )
                    )
                );


                $platTanpaSpasi =
                    str_replace(
                        ' ',
                        '',
                        $platNomor
                    );


                /*
                 * Cek apakah plat dipakai kendaraan lain.
                 */
                $platDipakai = Kendaraan::whereRaw(
                        "REPLACE(UPPER(plat_nomor), ' ', '') = ?",
                        [
                            $platTanpaSpasi,
                        ]
                    )
                    ->where(
                        'id',
                        '!=',
                        $kendaraan->id
                    )
                    ->exists();


                if ($platDipakai) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            "Plat nomor {$platNomor} sudah digunakan kendaraan lain."
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Simpan Perubahan
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $request,
                    $konsumen,
                    $noHpLama
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Update Konsumen
                    |--------------------------------------------------------------------------
                    */

                    $konsumen->update([
                        'nama_lengkap'
                            => trim(
                                $request->nama_lengkap
                            ),

                        'no_hp'
                            => trim(
                                $request->no_hp
                            ),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Update Kendaraan Existing
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $request->input(
                            'kendaraan',
                            []
                        )
                        as $kendaraanId => $data
                    ) {

                        $kendaraan = $konsumen
                            ->kendaraans()
                            ->whereKey(
                                $kendaraanId
                            )
                            ->firstOrFail();


                        $kendaraan->update([
                            'plat_nomor'
                                => strtoupper(
                                    preg_replace(
                                        '/\s+/',
                                        ' ',
                                        trim(
                                            $data['plat_nomor']
                                        )
                                    )
                                ),

                            'merk'
                                => trim(
                                    $data['merk']
                                ),

                            'tipe_model'
                                => trim(
                                    $data['tipe_model']
                                ),

                            'tahun'
                                => ! empty(
                                    $data['tahun']
                                )
                                    ? (int) $data['tahun']
                                    : null,
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sinkron Nomor HP User Pelanggan
                    |--------------------------------------------------------------------------
                    |
                    | Jika akun pelanggan sudah terhubung melalui
                    | konsumen_id, nomor HP login ikut diperbarui.
                    |
                    */

                    $userPelanggan = User::where(
                            'role',
                            'pelanggan'
                        )
                        ->where(
                            'konsumen_id',
                            $konsumen->id
                        )
                        ->first();


                    if ($userPelanggan) {

                        $noHpBaru =
                            trim(
                                $request->no_hp
                            );


                        /*
                         * Hanya update jika nomor berubah
                         * dan belum dipakai user lain.
                         */
                        if (
                            $noHpLama
                            !==
                            $noHpBaru
                        ) {

                            $dipakaiUserLain =
                                User::where(
                                    'no_hp',
                                    $noHpBaru
                                )
                                ->where(
                                    'id',
                                    '!=',
                                    $userPelanggan->id
                                )
                                ->exists();


                            if (! $dipakaiUserLain) {

                                $userPelanggan
                                    ->update([
                                        'no_hp'
                                            => $noHpBaru,
                                    ]);
                            }
                        }
                    }
                }
            );


            return redirect()
                ->route(
                    'admin.konsumen.index'
                )
                ->with(
                    'success',
                    'Data konsumen dan kendaraan berhasil diperbarui.'
                );

        }
    )->name('admin.konsumen.update');


    /*
    |--------------------------------------------------------------------------
    | Tambah Kendaraan ke Konsumen Existing
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/konsumen/{konsumen}/kendaraan',
        function (
            Request $request,
            Konsumen $konsumen
        ) {

            /*
            |--------------------------------------------------------------------------
            | Validasi
            |--------------------------------------------------------------------------
            */

            $request->validate([
                'kendaraan' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'kendaraan.*.plat_nomor' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'kendaraan.*.merk' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'kendaraan.*.tipe_model' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'kendaraan.*.tahun' => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 1),
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | Cek Duplikasi Plat
            |--------------------------------------------------------------------------
            */

            $platDalamForm = [];


            foreach (
                $request->kendaraan
                as $data
            ) {

                $platNomor = strtoupper(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        trim(
                            $data['plat_nomor']
                        )
                    )
                );


                $platTanpaSpasi =
                    str_replace(
                        ' ',
                        '',
                        $platNomor
                    );


                /*
                 * Duplikat dalam form yang sama.
                 */
                if (
                    in_array(
                        $platTanpaSpasi,
                        $platDalamForm,
                        true
                    )
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            "Plat nomor {$platNomor} dimasukkan lebih dari satu kali."
                        );
                }


                /*
                 * Duplikat database.
                 */
                $sudahAda = Kendaraan::whereRaw(
                        "REPLACE(UPPER(plat_nomor), ' ', '') = ?",
                        [
                            $platTanpaSpasi,
                        ]
                    )
                    ->exists();


                if ($sudahAda) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            "Plat nomor {$platNomor} sudah terdaftar."
                        );
                }


                $platDalamForm[] =
                    $platTanpaSpasi;
            }


            /*
            |--------------------------------------------------------------------------
            | Simpan Kendaraan Baru
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $request,
                    $konsumen
                ) {

                    foreach (
                        $request->kendaraan
                        as $data
                    ) {

                        $konsumen
                            ->kendaraans()
                            ->create([
                                'plat_nomor'
                                    => strtoupper(
                                        preg_replace(
                                            '/\s+/',
                                            ' ',
                                            trim(
                                                $data['plat_nomor']
                                            )
                                        )
                                    ),

                                'merk'
                                    => trim(
                                        $data['merk']
                                    ),

                                'tipe_model'
                                    => trim(
                                        $data['tipe_model']
                                    ),

                                'tahun'
                                    => ! empty(
                                        $data['tahun']
                                    )
                                        ? (int) $data['tahun']
                                        : null,
                            ]);
                    }
                }
            );


            return redirect()
                ->route(
                    'admin.konsumen.index'
                )
                ->with(
                    'success',
                    'Kendaraan baru berhasil ditambahkan ke '
                    . $konsumen->nama_lengkap
                    . '.'
                );

        }
    )->name(
        'admin.konsumen.kendaraan.store'
    );


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