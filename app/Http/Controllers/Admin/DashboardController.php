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
        abort_unless(
            auth()->check() &&
            in_array(auth()->user()->role, ['admin', 'anggota'], true),
            403
        );

        $isAdmin = auth()->user()->role === 'admin';

        // Statistik utama dashboard.
        $totalKonsumen = Konsumen::count();
        $totalKendaraan = Kendaraan::count();

        $servisHariIni = RekamServis::whereDate(
            'tanggal_servis',
            today()
        )->count();

        $servisMenunggu = RekamServis::where(
            'status',
            'menunggu_pengerjaan'
        )->count();

        $servisBerjalan = RekamServis::whereIn('status', [
            'menunggu_pengerjaan',
            'sedang_dikerjakan',
            'diproses',
            'dikerjakan',
            'proses',
        ])->count();

        $servisSelesaiBulan = RekamServis::where('status', 'selesai')
            ->whereBetween('tanggal_servis', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->count();

        // Antrean servis terbaru.
        $antreanTerbaru = RekamServis::with([
            'kendaraan.konsumen'
        ])->latest()->take(5)->get();

        // Grafik status seluruh servis.
        $statusServis = RekamServis::select('status')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->all();

        // Analisis sparepart terlaris bulan ini.
        $hasilSparepart = app(AnalisisSparepart::class)
            ->terlaris(now()->startOfMonth(), now()->endOfMonth(), 5);

        $sparepartTerlaris = $hasilSparepart['items'];

        $kalimatSparepart = app(AnalisisSparepart::class)
            ->ringkasanKalimat(
                $sparepartTerlaris,
                $hasilSparepart['total']
            );

        // Variabel keuangan dan grafik.
        $totalOmzet = null;
        $pemasukanBulan = null;
        $totalPiutang = null;
        $jumlahNotaPiutang = null;

        $grafikPemasukan = [];
        $grafikServisSelesai = [];

        if ($isAdmin) {

            // Total nilai nota yang sudah lunas.
            $totalOmzet = (float) NotaTransaksi::where(
                'status_pembayaran',
                'lunas'
            )->sum('total_biaya');

            // Pemasukan bulan ini.
            // Mengikuti perhitungan lama:
            // jumlah diterima dikurangi kembalian.
            $pemasukanBulan = (float) DB::table('pembayaran_notas')
                ->whereBetween('tanggal_bayar', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->selectRaw(
                    'COALESCE(SUM(jumlah_diterima - kembalian), 0) as nilai'
                )
                ->value('nilai');

            // Menghitung total pembayaran yang sudah dialokasikan
            // ke setiap nota untuk menghitung piutang.
            $alokasi = DB::table('pembayaran_notas')
                ->select('nota_transaksi_id')
                ->selectRaw('SUM(nominal_dialokasikan) as dibayar')
                ->groupBy('nota_transaksi_id');

            $q = DB::table('nota_transaksis as n')
                ->leftJoinSub(
                    $alokasi,
                    'a',
                    'a.nota_transaksi_id',
                    '=',
                    'n.id'
                )
                ->whereRaw(
                    '(n.total_biaya - COALESCE(a.dibayar, 0)) > 0.009'
                );

            $totalPiutang = (float) (clone $q)
                ->selectRaw(
                    'SUM(GREATEST(n.total_biaya - COALESCE(a.dibayar, 0), 0)) as total'
                )
                ->value('total');

            $jumlahNotaPiutang = (clone $q)->count('n.id');

            // =========================================
            // DATA GRAFIK 12 BULAN TERAKHIR
            // =========================================

            $awalGrafik = now()->startOfMonth()->subMonths(11);
            $akhirGrafik = now()->endOfMonth();

            // Grafik 1: Pemasukan bulanan.
            // Menghitung pembayaran yang diterima
            // setelah dikurangi kembalian.
            $pemasukanBulanan = DB::table('pembayaran_notas')
                ->whereBetween('tanggal_bayar', [
                    $awalGrafik,
                    $akhirGrafik
                ])
                ->selectRaw(
                    "DATE_FORMAT(tanggal_bayar, '%Y-%m') as periode"
                )
                ->selectRaw(
                    'COALESCE(SUM(jumlah_diterima - kembalian), 0) as total'
                )
                ->groupByRaw(
                    "DATE_FORMAT(tanggal_bayar, '%Y-%m')"
                )
                ->pluck('total', 'periode');

            // Grafik 2: Jumlah servis selesai bulanan.
            // Hanya servis dengan status selesai.
            $servisSelesaiBulanan = RekamServis::where(
                'status',
                'selesai'
            )
                ->whereBetween('tanggal_servis', [
                    $awalGrafik->copy()->startOfDay(),
                    $akhirGrafik->copy()->endOfDay()
                ])
                ->selectRaw(
                    "DATE_FORMAT(tanggal_servis, '%Y-%m') as periode"
                )
                ->selectRaw('COUNT(*) as total')
                ->groupByRaw(
                    "DATE_FORMAT(tanggal_servis, '%Y-%m')"
                )
                ->pluck('total', 'periode');

            // Mengisi 12 bulan agar bulan tanpa data
            // tetap ditampilkan dengan nilai nol.
            for ($i = 11; $i >= 0; $i--) {

                $bulan = now()
                    ->startOfMonth()
                    ->subMonths($i)
                    ->format('Y-m');

                $grafikPemasukan[] = [
                    'periode' => $bulan,
                    'total' => (float) (
                        $pemasukanBulanan[$bulan] ?? 0
                    ),
                ];

                $grafikServisSelesai[] = [
                    'periode' => $bulan,
                    'total' => (int) (
                        $servisSelesaiBulanan[$bulan] ?? 0
                    ),
                ];
            }
        }

        return view('admin.dashboard', compact(
            'isAdmin',
            'totalKonsumen',
            'totalKendaraan',
            'servisBerjalan',
            'servisHariIni',
            'servisMenunggu',
            'servisSelesaiBulan',
            'totalOmzet',
            'pemasukanBulan',
            'totalPiutang',
            'jumlahNotaPiutang',
            'grafikPemasukan',
            'grafikServisSelesai',
            'statusServis',
            'antreanTerbaru',
            'sparepartTerlaris',
            'kalimatSparepart'
        ));
    }
}
