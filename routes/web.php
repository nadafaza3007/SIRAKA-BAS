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
| Auth & Root Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');

Route::get('/register', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');

/*
|--------------------------------------------------------------------------
| Cek Username Secara Realtime
|--------------------------------------------------------------------------
*/
Route::get('/check-username', [\App\Http\Controllers\AuthController::class, 'checkUsername'])
    ->middleware('throttle:30,1')->name('username.check');

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

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
    Route::get('/rekam-servis', [\App\Http\Controllers\Admin\RekamServisController::class, 'index'])->name('admin.rekam-servis.index');

    // Rekap Nota Servis seluruh pelanggan (admin saja).
    // Route statis ditempatkan SEBELUM route /rekam-servis/{id}/... .
    Route::get('/rekam-servis/rekap-nota', [\App\Http\Controllers\Admin\RekapNotaController::class, 'index'])
        ->name('admin.rekam-servis.rekap-nota.index');
    Route::get('/rekam-servis/rekap-nota/export', [\App\Http\Controllers\Admin\RekapNotaController::class, 'export'])
        ->name('admin.rekam-servis.rekap-nota.export');

    Route::get('/rekam-servis/create', [\App\Http\Controllers\Admin\RekamServisController::class, 'create'])->name('admin.rekam-servis.create');

    Route::post('/rekam-servis', [\App\Http\Controllers\Admin\RekamServisController::class, 'store'])->name('admin.rekam-servis.store');

    Route::get('/rekam-servis/{id}/koreksi', [\App\Http\Controllers\Admin\RekamServisController::class, 'formKoreksi'])->name('admin.rekam-servis.koreksi-form');

    Route::post('/rekam-servis/{id}/koreksi', [\App\Http\Controllers\Admin\RekamServisController::class, 'simpanKoreksi'])->name('admin.rekam-servis.koreksi');

    Route::get('/rekam-servis/{id}/nota', [\App\Http\Controllers\Admin\RekamServisController::class, 'nota'])->name('admin.rekam-servis.nota');

    Route::post('/rekam-servis/{id}/sparepart', [\App\Http\Controllers\Admin\DetailNotaSparepartController::class, 'tambah'])->name('admin.rekam-servis.sparepart.tambah');

    Route::patch('/rekam-servis/{id}/sparepart/{detailId}/qty', [\App\Http\Controllers\Admin\DetailNotaSparepartController::class, 'ubahQty'])->name('admin.rekam-servis.sparepart.qty');

    Route::delete('/rekam-servis/{id}/sparepart/{detailId}', [\App\Http\Controllers\Admin\DetailNotaSparepartController::class, 'hapus'])->name('admin.rekam-servis.sparepart.hapus');

    Route::post('/rekam-servis/{id}/jasa', [\App\Http\Controllers\Admin\DetailNotaJasaController::class, 'tambah'])->name('admin.rekam-servis.jasa.tambah');

    Route::delete('/rekam-servis/{id}/jasa/{detailId}', [\App\Http\Controllers\Admin\DetailNotaJasaController::class, 'hapus'])->name('admin.rekam-servis.jasa.hapus');

    /*
    |--------------------------------------------------------------------------
    | 3. DATA KONSUMEN & KENDARAAN
    |--------------------------------------------------------------------------
    */
    Route::get('/konsumen', [\App\Http\Controllers\Admin\KonsumenController::class, 'index'])->name('admin.konsumen.index');

    Route::get('/konsumen/{konsumen}/edit', [\App\Http\Controllers\Admin\KonsumenController::class, 'edit'])->name('admin.konsumen.edit');

    /*
    |--------------------------------------------------------------------------
    | TAMBAH KONSUMEN BARU + BANYAK KENDARAAN
    |--------------------------------------------------------------------------
    */
    Route::post('/konsumen', [\App\Http\Controllers\Admin\KonsumenController::class, 'store'])->name('admin.konsumen.store');

    /*
    |--------------------------------------------------------------------------
    | EDIT KONSUMEN + KENDARAAN EXISTING
    |--------------------------------------------------------------------------
    */
    Route::put('/konsumen/{konsumen}', [\App\Http\Controllers\Admin\KonsumenController::class, 'update'])->name('admin.konsumen.update');

    /*
    |--------------------------------------------------------------------------
    | TAMBAH KENDARAAN KE KONSUMEN EXISTING
    |--------------------------------------------------------------------------
    */
    Route::post('/konsumen/{konsumen}/kendaraan', [\App\Http\Controllers\Admin\KonsumenController::class, 'storeKendaraan'])->name('admin.konsumen.kendaraan.store');

    /*
    |--------------------------------------------------------------------------
    | ROUTE KHUSUS ADMIN
    |--------------------------------------------------------------------------
    */
    // 4. Master Sparepart — logika dipindahkan ke SparepartController.
    Route::get('/sparepart', [\App\Http\Controllers\Admin\SparepartController::class, 'index'])
        ->name('admin.sparepart.index');

    Route::post('/sparepart', [\App\Http\Controllers\Admin\SparepartController::class, 'store'])
        ->name('admin.sparepart.store');

    Route::delete('/sparepart/{id}', [\App\Http\Controllers\Admin\SparepartController::class, 'destroy'])
        ->name('admin.sparepart.destroy');

    // 5. Master Jasa — logika dipindahkan ke JasaController.
    Route::get('/jasa', [\App\Http\Controllers\Admin\JasaController::class, 'index'])
        ->name('admin.jasa.index');

    Route::post('/jasa', [\App\Http\Controllers\Admin\JasaController::class, 'store'])
        ->name('admin.jasa.store');

    Route::delete('/jasa/{id}', [\App\Http\Controllers\Admin\JasaController::class, 'destroy'])
        ->name('admin.jasa.destroy');

    // 6. Master Diagnosa — logika dipindahkan ke DiagnosaController.
    Route::get('/diagnosa', [\App\Http\Controllers\Admin\DiagnosaController::class, 'index'])
        ->name('admin.diagnosa.index');

    Route::post('/diagnosa', [\App\Http\Controllers\Admin\DiagnosaController::class, 'store'])
        ->name('admin.diagnosa.store');

    Route::delete('/diagnosa/{id}', [\App\Http\Controllers\Admin\DiagnosaController::class, 'destroy'])
        ->name('admin.diagnosa.destroy');

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

    // 8. API Search Kendaraan — proses dipindahkan ke KendaraanController.
    Route::get('/riwayat/search-api', [\App\Http\Controllers\Admin\KendaraanController::class, 'searchApi'])
        ->name('admin.riwayat.search-api');

    // Workflows Catatan Rekomendasi & Nota
    Route::post('/rekam-servis/{id}/rekomendasi', [\App\Http\Controllers\Admin\NotaTagihanController::class, 'tambahRekomendasi'])->name('admin.rekam-servis.rekomendasi');

    // Selesaikan servis dan terbitkan SATU nota dengan status BELUM LUNAS.
    // Jangan menyimpan pembayaran di sini.
        Route::post('/rekam-servis/{id}/nota/konfirmasi', [\App\Http\Controllers\Admin\NotaTagihanController::class, 'terbitkanTagihan'])->name('admin.rekam-servis.nota.konfirmasi');

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

Route::post('/rekam-servis/{id}/selesaikan', [\App\Http\Controllers\Admin\PenyelesaianServisController::class, 'selesaikan'])->name('admin.rekam-servis.selesaikan');

    /*
|--------------------------------------------------------------------------
| LAPORAN INSPEKSI KENDARAAN
|--------------------------------------------------------------------------
*/

// 1. Daftar laporan inspeksi (GET)
Route::get('/inspeksi', [\App\Http\Controllers\Admin\LaporanInspeksiController::class, 'index'])->name('admin.inspeksi.index');

// 2. Form buat inspeksi baru (GET)
Route::get('/inspeksi/create', [\App\Http\Controllers\Admin\LaporanInspeksiController::class, 'create'])->name('admin.inspeksi.create');

Route::post('/inspeksi', [\App\Http\Controllers\Admin\LaporanInspeksiController::class, 'store'])->name('admin.inspeksi.store');

// 4. Detail inspeksi (GET) - TAMBAHKAN INI
Route::get('/inspeksi/{id}', [\App\Http\Controllers\Admin\LaporanInspeksiController::class, 'show'])->name('admin.inspeksi.show');

// 5. Hapus inspeksi (DELETE)
Route::delete('/inspeksi/{id}', [\App\Http\Controllers\Admin\LaporanInspeksiController::class, 'destroy'])->name('admin.inspeksi.destroy');
});