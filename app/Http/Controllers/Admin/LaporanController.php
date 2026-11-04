<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotaTransaksi;
use App\Models\AuditLog;
use App\Services\AnalisisSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class LaporanController extends Controller
{
    private function dataLaporan(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $startDate = $data['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $data['end_date'] ?? now()->endOfMonth()->toDateString();
        $awal = $startDate . ' 00:00:00';
        $akhir = $endDate . ' 23:59:59';
         $jumlahServisBulanan = DB::table('rekam_servis')
            ->whereBetween('tanggal_servis', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ])
            ->selectRaw("DATE_FORMAT(tanggal_servis, '%Y-%m') as bulan")
            ->selectRaw('COUNT(*) as jumlah')
            ->groupByRaw("DATE_FORMAT(tanggal_servis, '%Y-%m')")
            ->pluck('jumlah', 'bulan');

        $grafikServisLabels = [];
        $grafikServisJumlah = [];

        $bulanAwal = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $bulanAkhir = \Carbon\Carbon::parse($endDate)->startOfMonth();

        for (
            $bulan = $bulanAwal->copy();
            $bulan->lte($bulanAkhir);
            $bulan->addMonth()
        ) {
            $kunci = $bulan->format('Y-m');

            $grafikServisLabels[] = $bulan->copy()
                ->locale('id')
                ->translatedFormat('M Y');

            $grafikServisJumlah[] = (int) (
                $jumlahServisBulanan[$kunci] ?? 0
            );
        }

        // Setiap cicilan adalah satu baris kas masuk, meskipun nota belum lunas.
        $pembayaran = DB::table('pembayaran_notas as p')
            ->join('nota_transaksis as n', 'n.id', '=', 'p.nota_transaksi_id')
            ->join('rekam_servis as rs', 'rs.id', '=', 'n.rekam_servis_id')
            ->leftJoin('kendaraans as k', 'k.id', '=', 'rs.kendaraan_id')
            ->leftJoin('konsumens as c', 'c.id', '=', 'k.konsumen_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->whereBetween('p.tanggal_bayar', [$awal, $akhir])
            ->select(
                'p.*',
                'n.no_nota',
                'n.rekam_servis_id',
                'n.total_biaya as total_nota',
                'n.status_pembayaran',
                'k.plat_nomor',
                'c.nama_lengkap',
                'u.name as petugas_penerima'
            )
            ->orderByDesc('p.tanggal_bayar')->orderByDesc('p.id')->get();

        // Jangan rekonstruksi tips berdasarkan jumlah_tagihan: cicilan memakai
        // nominal_dialokasikan, sedangkan pembayaran historis punya semantik berbeda.
        $pembayaran->each(function ($p) {
            $p->nominal_dialokasikan = (float) ($p->nominal_dialokasikan ?? 0);
            $p->tips = (float) ($p->tips ?? 0);
            $p->kembalian = (float) ($p->kembalian ?? 0);
        });

        $halaman = max(1, (int) $request->query('page', 1));
        $pembayaranHalaman = new LengthAwarePaginator(
            $pembayaran->forPage($halaman, 10)->values(), $pembayaran->count(),
            10, $halaman, ['path' => $request->url(), 'query' => $request->except('page')]
        );

        $pendapatanKotor = (float) $pembayaran->sum('nominal_dialokasikan');
        $totalTips = (float) $pembayaran->sum('tips');
        $totalKembalian = (float) $pembayaran->sum('kembalian');
        $totalUangDiterima = (float) $pembayaran->sum('jumlah_diterima');
        $totalPemasukan = $totalUangDiterima - $totalKembalian;

        $ids = $pembayaran->pluck('nota_transaksi_id')->unique()->all();
        $transaksi = empty($ids) ? collect() : NotaTransaksi::with([
            'rekamServis.kendaraan.konsumen', 'rekamServis.detailSpareparts', 'pembayaran',
        ])->whereIn('id', $ids)->get();

        // PERHATIAN: modal berikut adalah modal sparepart dari nota yang mempunyai
        // pembayaran dalam periode, bukan pengeluaran kas riil dalam periode.
        // Dihitung sekali per nota, bukan sekali per cicilan.
        
        $totalModalSparepart = (float) DB::table('detail_spareparts as d')
            ->join(
                'nota_transaksis as n',
                'n.rekam_servis_id',
                '=',
                'd.rekam_servis_id'
            )
            ->whereBetween('n.tanggal_terbit_tagihan', [$awal, $akhir])
            ->sum(DB::raw('d.qty * d.harga_modal_saat_transaksi'));

        $totalModal = $totalModalSparepart;


        // Piutang per akhir periode:
        // Pembayaran dihitung hanya sampai tanggal akhir laporan.
        // Berlaku selama nominal tagihan belum mengalami revisi.
        $alokasi = DB::table('pembayaran_notas')
            ->where('tanggal_bayar', '<=', $akhir)
            ->select('nota_transaksi_id')
            ->selectRaw('SUM(nominal_dialokasikan) as dibayar')
            ->groupBy('nota_transaksi_id');
        $piutangQuery = DB::table('nota_transaksis as n')
            ->leftJoinSub($alokasi, 'a', 'a.nota_transaksi_id', '=', 'n.id')
            ->whereDate('n.tanggal_cetak', '<=', $endDate)
            ->whereRaw('(n.total_biaya - COALESCE(a.dibayar, 0)) > 0.009');
        $totalPiutang = (float) (clone $piutangQuery)->selectRaw(
            'SUM(GREATEST(0, n.total_biaya - COALESCE(a.dibayar, 0))) as nominal'
        )->value('nominal');
        $jumlahNotaPiutang = (clone $piutangQuery)->count('n.id');

        // Grafik: harian untuk interval <=31 hari, bulanan untuk rentang lebih panjang.
        $harian = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) <= 30;
        $format = $harian ? '%Y-%m-%d' : '%Y-%m';
        $groupExpr = "DATE_FORMAT(p.tanggal_bayar, '{$format}')";
        $rekapPendapatan = DB::table('pembayaran_notas as p')
            ->whereBetween('p.tanggal_bayar', [$awal, $akhir])
            ->selectRaw("{$groupExpr} as periode")
            ->selectRaw('SUM(p.jumlah_diterima - p.kembalian) as total')
            ->groupByRaw($groupExpr)->orderBy('periode')->pluck('total', 'periode');
       $grafikLabels = [];
        $grafikPemasukan = [];

        $mulai = Carbon::parse($startDate);
        $akhirGrafik = Carbon::parse($endDate);

        if ($harian) {
            $mulai->startOfDay();
            $akhirGrafik->startOfDay();
        } else {
            $mulai->startOfMonth();
            $akhirGrafik->startOfMonth();
        }

        for (
            $t = $mulai->copy();
            $t->lte($akhirGrafik);
            $harian ? $t->addDay() : $t->addMonth()
        ) {
            $key = $harian
                ? $t->format('Y-m-d')
                : $t->format('Y-m');

            $grafikLabels[] = $key;
            $grafikPemasukan[] = (float) ($rekapPendapatan[$key] ?? 0);
        }
        $grafikMetode = DB::table('pembayaran_notas')
            ->whereBetween('tanggal_bayar', [$awal, $akhir])
            ->select('metode_pembayaran')
            ->selectRaw('SUM(jumlah_diterima - kembalian) as nominal')
            ->groupBy('metode_pembayaran')->pluck('nominal', 'metode_pembayaran')->all();

        // Analisis sparepart lama tetap digunakan. Service harus diaudit untuk
        // memastikan perhitungan tanggal pelunasan tidak menggandakan cicilan.
        $analisis = app(AnalisisSparepart::class);
        $terlaris = $analisis->terlaris($startDate, $endDate, 10);
        $belumLunas = $analisis->belumLunas($startDate, $endDate, 5);
        $sparepartTerlaris = $terlaris['items'];
        $ringkasanSparepart = $terlaris['total'];
        $sparepartBelumLunas = $belumLunas['items'];
        $ringkasanBelumLunas = $belumLunas['total'];
        $kalimatSparepart = $analisis->ringkasanKalimat($sparepartTerlaris, $ringkasanSparepart);

        return compact('startDate', 'endDate', 'pembayaran', 'pembayaranHalaman', 'transaksi',
            'pendapatanKotor', 'totalTips', 'totalKembalian', 'totalUangDiterima',
            'totalPemasukan', 'totalModalSparepart', 'totalModal', 'totalPiutang',
            'jumlahNotaPiutang', 'grafikLabels', 'grafikPemasukan',
            'grafikMetode', 'sparepartTerlaris', 'ringkasanSparepart',
            'sparepartBelumLunas', 'ringkasanBelumLunas', 'kalimatSparepart', 'grafikServisLabels','grafikServisJumlah',);
    }

    public function index(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        return view('admin.laporan.index', $this->dataLaporan($request));
    }

    public function cetakPdf(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        $data = $this->dataLaporan($request);
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'entitas' => 'Laporan Pemasukan',
                'aksi' => 'Cetak / Unduh Laporan Keuangan Periode '
                    . $data['startDate'] . ' s/d ' . $data['endDate']
                    . ' | Pemasukan Rp ' . number_format($data['totalPemasukan'], 0, ',', '.')
                    . ', tips Rp ' . number_format($data['totalTips'], 0, ',', '.')
                    . ', kembalian Rp ' . number_format($data['totalKembalian'], 0, ',', '.'),
            ]);
        } catch (\Throwable $e) { report($e); }
        return view('admin.laporan.cetak', $data);
    }
}