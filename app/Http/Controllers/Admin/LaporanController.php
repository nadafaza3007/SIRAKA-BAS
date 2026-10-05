<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotaTransaksi;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transaksi = NotaTransaksi::with(['rekamServis.kendaraan.konsumen', 'rekamServis.detailSpareparts'])
            ->whereBetween('tanggal_cetak', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $pendapatanKotor = $transaksi->sum('total_biaya');
        $totalModalSparepart = $transaksi->sum(function ($nota) {
            return $nota->totalModalSparepart();
        });
        $labaBersih = $pendapatanKotor - $totalModalSparepart;

        return view('admin.laporan.index', compact(
            'startDate', 'endDate', 'transaksi', 'pendapatanKotor', 'totalModalSparepart', 'labaBersih'
        ));
    }

    public function cetakPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transaksi = NotaTransaksi::with(['rekamServis.kendaraan.konsumen', 'rekamServis.detailSpareparts'])
            ->whereBetween('tanggal_cetak', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $pendapatanKotor = $transaksi->sum('total_biaya');
        $totalModal = $transaksi->sum(function ($nota) {
            return $nota->totalModalSparepart();
        });
        $labaBersih = $pendapatanKotor - $totalModal;

        // Perekaman Audit Log Otomatis Sesuai Pengujian
        AuditLog::create([
            'user_id' => auth()->id(),
            'entitas' => 'Laporan Pemasukan',
            'aksi' => "Cetak / Unduh Laporan Keuangan Periode {$startDate} s/d {$endDate}",
            'keterangan' => "Total Omzet: Rp {$pendapatanKotor}, Laba Bersih: Rp {$labaBersih}",
        ]);

        return view('admin.laporan.cetak', compact(
            'startDate', 'endDate', 'transaksi', 'pendapatanKotor', 'totalModal', 'labaBersih'
        ));
    }
}