<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapNotaExport;
use App\Http\Controllers\Controller;
use App\Models\RekamServis;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapNotaController extends Controller
{
    private function periode(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'required_with:end_date', 'date'],
            'end_date' => ['nullable', 'required_with:start_date', 'date', 'after_or_equal:start_date'],
        ]);

        return [
            $data['start_date'] ?? now()->startOfMonth()->toDateString(),
            $data['end_date'] ?? now()->endOfMonth()->toDateString(),
        ];
    }

    private function transaksi(string $awal, string $akhir)
    {
        // Basis filter adalah tanggal masuk SERVIS; bukan tanggal bayar.
        // Hanya servis dengan nota terbit yang masuk rekap.
        $servis = RekamServis::query()
            ->with([
                'kendaraan.konsumen',
                'nota.pembayaran',
                'detailSpareparts.sparepart',
                'detailJasas.jasa',
            ])
            ->whereHas('nota')
            ->whereDate('tanggal_servis', '>=', $awal)
            ->whereDate('tanggal_servis', '<=', $akhir)
            ->get()
            ->filter(fn ($s) => $s->kendaraan && $s->kendaraan->konsumen && $s->nota)
            ->sortBy(function ($s) {
                $c = $s->kendaraan->konsumen;
                $k = $s->kendaraan;
                return mb_strtolower((string) $c->nama_lengkap) . '|' . sprintf('%012d', $c->id)
                    . '|' . mb_strtolower((string) $k->plat_nomor) . '|' . sprintf('%012d', $k->id)
                    . '|' . Carbon::parse($s->tanggal_servis)->format('Ymd') . '|' . sprintf('%012d', $s->id);
            })->values();

        return $servis;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        [$startDate, $endDate] = $this->periode($request);
        $servis = $this->transaksi($startDate, $endDate);
        $pelanggan = $servis->groupBy(fn ($s) => $s->kendaraan->konsumen->id);
        $totalTagihan = $servis->sum(fn ($s) => (float) $s->nota->total_biaya);
        $totalDibayar = $servis->sum(fn ($s) => (float) $s->nota->pembayaran->sum('nominal_dialokasikan'));
        $totalModal = $servis->sum(fn ($s) => $s->detailSpareparts->sum(fn ($d) => $d->qty * $d->harga_modal_saat_transaksi));

        return view('admin.rekam-servis.rekap-nota', compact(
            'startDate', 'endDate', 'servis', 'pelanggan', 'totalTagihan', 'totalDibayar', 'totalModal'
        ));
    }

    public function export(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        [$startDate, $endDate] = $this->periode($request);
        $servis = $this->transaksi($startDate, $endDate);

        return (new RekapNotaExport($servis, $startDate, $endDate))->download();
    }
}