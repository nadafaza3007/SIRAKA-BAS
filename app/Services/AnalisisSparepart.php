<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Analisis penjualan sparepart.
 *
 * // terlaris(): sparepart pada tagihan yang sudah diterbitkan,
 * // periode berdasarkan tanggal_terbit_tagihan,
 * // termasuk tagihan belum lunas dan pembayaran sebagian.
 * - belumLunas()  : pemakaian sparepart pada servis yang belum dibayar,
 *                   dipantau terpisah dan TIDAK masuk omzet.
 *
 * Omzet per sparepart dihitung setelah potongan nota dibagi proporsional,
 * sehingga jumlahnya konsisten dengan "Pendapatan servis" di laporan.
 */
class AnalisisSparepart
{
    private function rentang($mulai, $selesai): array
    {
        return [
            Carbon::parse($mulai)->toDateString() . ' 00:00:00',
            Carbon::parse($selesai)->toDateString() . ' 23:59:59',
        ];
    }

    /**
     * @return array{items: Collection, total: object}
     */
    public function terlaris($mulai, $selesai, int $limit = 10): array
    {
        [$awal, $akhir] = $this->rentang($mulai, $selesai);

        // Faktor potongan nota (total_biaya / subtotal); 1 bila tanpa potongan.
        $faktor = 'COALESCE(n.total_biaya / NULLIF(n.subtotal, 0), 1)';

        $dasar = fn () => DB::table('detail_spareparts as d')
            ->join(
                'nota_transaksis as n',
                'n.rekam_servis_id',
                '=',
                'd.rekam_servis_id'
            )
            ->leftJoin(
                'spareparts as s',
                's.id',
                '=',
                'd.sparepart_id'
            )
            ->whereBetween(
                'n.tanggal_terbit_tagihan',
                [$awal, $akhir]
            );

        $items = $dasar()
            ->groupBy('d.sparepart_id')
            ->selectRaw("
                d.sparepart_id,
                MAX(s.nama_barang) as nama_barang,
                MAX(s.kode_barang) as kode_barang,
                MAX(s.jenis_ketersediaan) as jenis_ketersediaan,
                SUM(d.qty) as unit_terjual,
                COUNT(DISTINCT n.id) as frekuensi,
                SUM(d.qty * d.harga_jual_saat_transaksi * {$faktor}) as omzet,
                SUM(d.qty * d.harga_modal_saat_transaksi) as modal
            ")
            ->orderByDesc('unit_terjual')
            ->orderByDesc('omzet')
            ->orderBy('nama_barang')
            ->limit($limit)
            ->get()
            ->map(function ($r) {
                $r->nama_barang = $r->nama_barang?: 'Sparepart #' . $r->sparepart_id . ' (data dihapus)';
                $r->jenis_label = $r->jenis_ketersediaan === 'po' ? 'Pre-Order' : ($r->jenis_ketersediaan === 'stok' ? 'Stok' : 'Tidak diketahui');
                $r->unit_terjual = (int) $r->unit_terjual;
                $r->frekuensi    = (int) $r->frekuensi;
                $r->omzet        = round((float) $r->omzet);
                $r->modal        = round((float) $r->modal);
                $r->laba_kotor   = $r->omzet - $r->modal;
                return $r;
            });

        $t = $dasar()->selectRaw("
                COALESCE(SUM(d.qty), 0) as unit_terjual,
                COALESCE(SUM(d.qty * d.harga_jual_saat_transaksi * {$faktor}), 0) as omzet,
                COALESCE(SUM(d.qty * d.harga_modal_saat_transaksi), 0) as modal
            ")->first();

        $total = (object) [
            'unit_terjual' => (int) $t->unit_terjual,
            'omzet'        => round((float) $t->omzet),
            'modal'        => round((float) $t->modal),
        ];
        $total->laba_kotor = $total->omzet - $total->modal;

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Sparepart yang sudah terpakai pada servis yang belum lunas
     * (belum ada nota, atau nota belum dibayar).
     *
     * @return array{items: Collection, total: object}
     */
    public function belumLunas($mulai, $selesai, int $limit = 5): array
    {
        [$awal, $akhir] = $this->rentang($mulai, $selesai);

        $dasar = fn () => DB::table('detail_spareparts as d')
            ->join('rekam_servis as rs', 'rs.id', '=', 'd.rekam_servis_id')
            ->leftJoin('nota_transaksis as n', 'n.rekam_servis_id', '=', 'rs.id')
            ->leftJoin('spareparts as s', 's.id', '=', 'd.sparepart_id')
            ->where(function ($q) {
                $q->whereNull('n.id')->orWhere('n.status_pembayaran', '!=', 'lunas');
            })
            ->whereBetween('rs.tanggal_servis', [$awal, $akhir]);

        $items = $dasar()
            ->groupBy('d.sparepart_id')
            ->selectRaw('
                d.sparepart_id,
                MAX(s.nama_barang) as nama_barang,
                MAX(s.jenis_ketersediaan) as jenis_ketersediaan,
                SUM(d.qty) as unit,
                COUNT(DISTINCT rs.id) as jumlah_servis,
                SUM(d.qty * d.harga_jual_saat_transaksi) as nilai
            ')
            ->orderByDesc('unit')
            ->orderBy('nama_barang')
            ->limit($limit)
            ->get()
            ->map(function ($r) {
                
            $r->nama_barang = $r->nama_barang?: 'Sparepart #' . $r->sparepart_id . ' (data dihapus)';
                $r->jenis_label = $r->jenis_ketersediaan === 'po' ? 'Pre-Order' : ($r->jenis_ketersediaan === 'stok' ? 'Stok' : 'Tidak diketahui');

                $r->unit          = (int) $r->unit;
                $r->jumlah_servis = (int) $r->jumlah_servis;
                $r->nilai         = round((float) $r->nilai);
                return $r;
            });

        $t = $dasar()->selectRaw('
                COALESCE(SUM(d.qty), 0) as unit,
                COUNT(DISTINCT rs.id) as jumlah_servis,
                COALESCE(SUM(d.qty * d.harga_jual_saat_transaksi), 0) as nilai
            ')->first();

        return [
            'items' => $items,
            'total' => (object) [
                'unit'          => (int) $t->unit,
                'jumlah_servis' => (int) $t->jumlah_servis,
                'nilai'         => round((float) $t->nilai),
            ],
        ];
    }

    /**
     * Ringkasan otomatis penjualan sparepart.
     */
    public function ringkasanKalimat(
        Collection $items,
        object $total
    ): ?string {
        $top = $items->first();

        if (! $top) {
            return null;
        }

        $rupiah = fn ($nilai) => 'Rp ' .
            number_format($nilai, 0, ',', '.');

        $kalimat = "{$top->nama_barang} merupakan sparepart "
            . "terlaris pada periode ini dengan "
            . number_format($top->unit_terjual, 0, ',', '.')
            . " unit terjual dalam {$top->frekuensi} transaksi "
            . "dan menghasilkan omzet {$rupiah($top->omzet)}.";

        if ($total->omzet > 0) {
            $persentase = round(
                ($top->omzet / $total->omzet) * 100,
                1
            );

            $kalimat .= " Kontribusinya mencapai "
                . "{$persentase}% dari total omzet sparepart.";
        }

        if ($top->laba_kotor < 0) {
            $kalimat .= " Perhatian: laba kotor negatif. "
                . "Periksa kembali harga jual dan harga modal.";
        } else {
            $kalimat .= " Periksa ketersediaan atau kebutuhan "
                . "dan kebutuhan pengadaan barang ini.";
        }

        return $kalimat;
    }

}