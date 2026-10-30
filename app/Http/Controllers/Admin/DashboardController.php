<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Kendaraan;
use App\Models\RekamServis;
use App\Models\NotaTransaksi;
use App\Services\AnalisisSparepart;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'anggota'], true), 403);
        $isAdmin = auth()->user()->role === 'admin';
        $totalKonsumen = Konsumen::count();
        $totalKendaraan = Kendaraan::count();
        $servisHariIni = RekamServis::whereDate('tanggal_servis', today())->count();
        $servisMenunggu = RekamServis::where('status', 'menunggu_pengerjaan')->count();
        $servisBerjalan = RekamServis::whereIn('status', [
            'menunggu_pengerjaan', 'sedang_dikerjakan', 'diproses', 'dikerjakan', 'proses',
        ])->count();
        $servisSelesaiBulan = RekamServis::where('status', 'selesai')
            ->whereBetween('tanggal_servis', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $antreanTerbaru = RekamServis::with(['kendaraan.konsumen'])->latest()->take(5)->get();
        // Grafik status meliputi jumlah status servis seluruhnya saat ini.
        $statusServis = RekamServis::select('status')->selectRaw('COUNT(*) as jumlah')
            ->groupBy('status')->pluck('jumlah', 'status')->all();
        $hasilSparepart = app(AnalisisSparepart::class)->terlaris(now()->startOfMonth(), now()->endOfMonth(), 5);
        $sparepartTerlaris = $hasilSparepart['items'];
        $kalimatSparepart = app(AnalisisSparepart::class)
            ->ringkasanKalimat($sparepartTerlaris, $hasilSparepart['total']);

        $totalOmzet = null;
        $pemasukanBulan = null;
        $totalPiutang = null;
        $jumlahNotaPiutang = null;
        $grafikPemasukan = [];
        if ($isAdmin) {
            // Dipertahankan untuk kompatibilitas: nilai nota yang sudah lunas.
            $totalOmzet = (float) NotaTransaksi::where('status_pembayaran', 'lunas')->sum('total_biaya');
            $pemasukanBulan = (float) DB::table('pembayaran_notas')
                ->whereBetween('tanggal_bayar', [now()->startOfMonth(), now()->endOfMonth()])
                ->selectRaw('COALESCE(SUM(jumlah_diterima - kembalian),0) as nilai')->value('nilai');
            $alokasi = DB::table('pembayaran_notas')->select('nota_transaksi_id')
                ->selectRaw('SUM(nominal_dialokasikan) as dibayar')
                ->groupBy('nota_transaksi_id');
            $q = DB::table('nota_transaksis as n')
                ->leftJoinSub($alokasi, 'a', 'a.nota_transaksi_id', '=', 'n.id')
                ->whereRaw('(n.total_biaya - COALESCE(a.dibayar, 0)) > 0.009');
            $totalPiutang = (float) (clone $q)->selectRaw(
                'SUM(GREATEST(n.total_biaya - COALESCE(a.dibayar, 0),0)) as total'
            )->value('total');
            $jumlahNotaPiutang = (clone $q)->count('n.id');
            $pemasukanHarian = DB::table('pembayaran_notas')
                ->whereBetween('tanggal_bayar', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
                ->selectRaw('DATE(tanggal_bayar) as tanggal')
                ->selectRaw('SUM(jumlah_diterima - kembalian) as pemasukan')
                ->groupByRaw('DATE(tanggal_bayar)')->pluck('pemasukan', 'tanggal');
            for ($i = 29; $i >= 0; $i--) {
                $tgl = now()->subDays($i)->toDateString();
                $grafikPemasukan[] = ['tanggal' => $tgl, 'total' => (float) ($pemasukanHarian[$tgl] ?? 0)];
            }
        }

        return view('admin.dashboard', compact('isAdmin', 'totalKonsumen', 'totalKendaraan',
            'servisBerjalan', 'servisHariIni', 'servisMenunggu', 'servisSelesaiBulan',
            'totalOmzet', 'pemasukanBulan', 'totalPiutang', 'jumlahNotaPiutang',
            'grafikPemasukan', 'statusServis', 'antreanTerbaru', 'sparepartTerlaris',
            'kalimatSparepart'));
    }
}