<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapNotaExport
{
    private const HEADERS = [
        'No', 'Nama Pelanggan', 'No. HP', 'Plat Nomor', 'Merek', 'Tipe / Model', 'Tahun',
        'Tanggal Servis', 'No. Nota', 'KM Masuk', 'KM Keluar', 'Keluhan Awal',
        'Diagnosis Awal', 'Diagnosis Akhir', 'Tindakan Servis',
        'Sparepart', 'Jenis', 'Qty SP', 'Modal / Unit', 'Jual / Unit',
        'Total Modal SP', 'Total Jual SP', 'Margin Kotor SP',
        'Nama Jasa', 'Tarif Master Saat Ekspor', 'Tarif Transaksi / Unit', 'Qty Jasa', 'Total Jasa',
        'Subtotal Nota', 'Diskon Nota', 'Total Tagihan', 'Sudah Dibayar', 'Sisa Tagihan',
        'Status Pembayaran', 'Status Servis', 'Catatan Rekomendasi',
    ];

    private Spreadsheet $book;
    private $sheet;
    private int $row = 5;
    private array $totals = [];

    public function __construct(
        private Collection $servis,
        private string $startDate,
        private string $endDate
    ) {
        $this->book = new Spreadsheet();
        $this->sheet = $this->book->getActiveSheet();
        $this->sheet->setTitle('Rekap Nota Servis');
        $this->totals = $this->emptyTotal();
    }

    private function emptyTotal(): array
    {
        return ['nota' => 0, 'sp' => 0, 'jasa' => 0, 'modal' => 0, 'jual' => 0,
            'subtotal' => 0, 'diskon' => 0, 'tagihan' => 0, 'dibayar' => 0, 'sisa' => 0];
    }

    private function merge(string $col, int $first, int $last): void
    {
        if ($last > $first) {
            $this->sheet->mergeCells("{$col}{$first}:{$col}{$last}");
        }
    }

    private function put(string $col, int $row, mixed $value): void
    {
        $this->sheet->setCellValue("{$col}{$row}", $value);
    }

    private function addTotal(array &$total, object $s): void
    {
        $n = $s->nota;
        $spModal = (float) $s->detailSpareparts->sum(fn ($x) => $x->qty * $x->harga_modal_saat_transaksi);
        $spJual = (float) $s->detailSpareparts->sum(fn ($x) => $x->qty * $x->harga_jual_saat_transaksi);
        $jasa = (float) $s->detailJasas->sum(fn ($x) => ($x->qty ?? 1) * $x->harga_saat_transaksi);
        $dibayar = (float) $n->pembayaran->sum('nominal_dialokasikan');
        $total['nota']++;
        $total['sp'] += (int) $s->detailSpareparts->sum('qty');
        $total['jasa'] += (int) $s->detailJasas->sum(fn ($x) => $x->qty ?? 1);
        $total['modal'] += $spModal;
        $total['jual'] += $spJual;
        $total['subtotal'] += (float) $n->subtotal;
        $total['diskon'] += (float) $n->diskon;
        $total['tagihan'] += (float) $n->total_biaya;
        $total['dibayar'] += $dibayar;
        $total['sisa'] += max(0, (float) $n->total_biaya - $dibayar);
    }

    private function build(): void
    {
        $sh = $this->sheet;
        $sh->mergeCells('A1:AJ1');
        $sh->setCellValue('A1', 'BABA AUTO SERVICE — REKAP NOTA SERVIS PELANGGAN');
        $sh->mergeCells('A2:AJ2');
        $sh->setCellValue('A2', 'Periode ' . Carbon::parse($this->startDate)->format('d/m/Y') . ' s.d. ' . Carbon::parse($this->endDate)->format('d/m/Y'));
        $sh->mergeCells('A3:AJ3');
        $sh->setCellValue('A3', 'Dasar periode: tanggal servis | Status pembayaran: saat ekspor ' . now()->format('d/m/Y H:i') . ' | Tarif master: nilai terkini, bukan historis');
        $sh->fromArray(self::HEADERS, null, 'A4');
        $sh->freezePane('H5');
        $sh->setAutoFilter('A4:AJ4');

        foreach ($this->servis->groupBy(fn ($s) => $s->kendaraan->konsumen->id) as $pelangganServis) {
            $awalPelanggan = $this->row;
            $totalPelanggan = $this->emptyTotal();
            foreach ($pelangganServis->groupBy(fn ($s) => $s->kendaraan_id) as $kendaraanServis) {
                $awalKendaraan = $this->row;
                foreach ($kendaraanServis as $s) {
                    $awalNota = $this->row;
                    $n = $s->nota;
                    $k = $s->kendaraan;
                    $c = $k->konsumen;
                    $parts = $s->detailSpareparts->values();
                    $jasas = $s->detailJasas->values();
                    // Sejajarkan dua daftar, jangan buat perkalian silang item!
                    $baris = max(1, $parts->count(), $jasas->count());
                    for ($i = 0; $i < $baris; $i++) {
                        $r = $this->row++;
                        $p = $parts->get($i);
                        $j = $jasas->get($i);
                        if ($p) {
                            $qty = (int) $p->qty;
                            $modal = (float) $p->harga_modal_saat_transaksi;
                            $jual = (float) $p->harga_jual_saat_transaksi;
                            $jenis = ($p->sparepart?->jenis_ketersediaan ?? 'stok') === 'po' ? 'Pre-Order' : 'Stok';
                            $this->put('P', $r, $p->sparepart?->nama_barang ?? ('Sparepart #' . $p->sparepart_id));
                            $this->put('Q', $r, $jenis);
                            $this->put('R', $r, $qty);
                            $this->put('S', $r, $modal);
                            $this->put('T', $r, $jual);
                            $this->put('U', $r, $qty * $modal);
                            $this->put('V', $r, $qty * $jual);
                            $this->put('W', $r, $qty * ($jual - $modal));
                        }
                        if ($j) {
                            $qty = (int) ($j->qty ?? 1);
                            $tarif = (float) $j->harga_saat_transaksi;
                            $this->put('X', $r, $j->jasa?->nama_jasa ?? ('Jasa #' . $j->jasa_id));
                            $this->put('Y', $r, $j->jasa ? (float) $j->jasa->harga : null);
                            $this->put('Z', $r, $tarif);
                            $this->put('AA', $r, $qty);
                            $this->put('AB', $r, $qty * $tarif);
                        }
                    }
                    $akhirNota = $this->row - 1;
                    $this->put('H', $awalNota, Carbon::parse($s->tanggal_servis)->format('d/m/Y'));
                    $this->put('I', $awalNota, $n->no_nota);
                    $this->put('J', $awalNota, $s->km_masuk);
                    $this->put('K', $awalNota, $s->km_keluar);
                    $this->put('L', $awalNota, $s->keluhan_awal);
                    $this->put('M', $awalNota, $s->diagnosa_awal);
                    $this->put('N', $awalNota, $s->diagnosa_akhir);
                    $this->put('O', $awalNota, $s->tindakan_servis);
                    $this->put('AC', $awalNota, (float) $n->subtotal);
                    $this->put('AD', $awalNota, (float) $n->diskon);
                    $this->put('AE', $awalNota, (float) $n->total_biaya);
                    $dibayar = (float) $n->pembayaran->sum('nominal_dialokasikan');
                    $this->put('AF', $awalNota, $dibayar);
                    $this->put('AG', $awalNota, max(0, (float) $n->total_biaya - $dibayar));
                    $this->put('AH', $awalNota, $n->status_pembayaran === 'lunas' ? 'Lunas' : ($dibayar > 0 ? 'Dibayar sebagian' : 'Belum lunas'));
                    $this->put('AI', $awalNota, $s->status);
                    $this->put('AJ', $awalNota, $n->catatan_nota);
                    foreach (array_merge(range('H', 'O'), ['AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ']) as $col) {
                        $this->merge($col, $awalNota, $akhirNota);
                    }
                    $this->addTotal($totalPelanggan, $s);
                    $this->addTotal($this->totals, $s);
                }
                $akhirKendaraan = $this->row - 1;
                $k = $kendaraanServis->first()->kendaraan;
                foreach (['D' => $k->plat_nomor, 'E' => $k->merk, 'F' => $k->tipe_model, 'G' => $k->tahun] as $col => $value) {
                    $this->put($col, $awalKendaraan, $value);
                    $this->merge($col, $awalKendaraan, $akhirKendaraan);
                }
            }
            $akhirPelanggan = $this->row - 1;
            $c = $pelangganServis->first()->kendaraan->konsumen;
            $this->put('A', $awalPelanggan, $this->servis->groupBy(fn ($s) => $s->kendaraan->konsumen->id)->keys()->search($c->id) + 1);
            $this->put('B', $awalPelanggan, $c->nama_lengkap);
            $this->put('C', $awalPelanggan, $c->no_hp);
            foreach (['A', 'B', 'C'] as $col) $this->merge($col, $awalPelanggan, $akhirPelanggan);
            $this->summary('TOTAL PELANGGAN: ' . $c->nama_lengkap, $totalPelanggan, true);
        }
        $this->summary('TOTAL KESELURUHAN PERIODE', $this->totals, false);
        $this->format();
    }

    private function summary(string $label, array $total, bool $pelanggan): void
    {
        $r = $this->row++;
        $this->sheet->mergeCells("A{$r}:Q{$r}");
        $this->put('A', $r, $label . ' | ' . $total['nota'] . ' nota');
        $this->put('R', $r, $total['sp']);
        $this->put('U', $r, $total['modal']);
        $this->put('V', $r, $total['jual']);
        $this->put('W', $r, $total['jual'] - $total['modal']);
        $this->put('AA', $r, $total['jasa']);
        $this->put('AB', $r, $total['subtotal'] - $total['jual']);
        $this->put('AC', $r, $total['subtotal']);
        $this->put('AD', $r, $total['diskon']);
        $this->put('AE', $r, $total['tagihan']);
        $this->put('AF', $r, $total['dibayar']);
        $this->put('AG', $r, $total['sisa']);
        $this->sheet->getStyle("A{$r}:AJ{$r}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $pelanggan ? 'E8EEF6' : 'D3EBDD']],
        ]);
    }

    private function format(): void
    {
        $sh = $this->sheet;
        $last = $this->row - 1;
        $sh->getStyle('A1:AJ1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FFFFFF');
        $sh->getStyle('A1:AJ1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('12304A');
        $sh->getRowDimension(1)->setRowHeight(30);
        $sh->getStyle('A4:AJ4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sh->getStyle('A4:AJ4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('175B76');
        $sh->getRowDimension(4)->setRowHeight(36);
        $sh->getStyle("A4:AJ{$last}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $sh->getStyle("A4:AJ{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'J') as $col) $sh->getColumnDimension($col)->setWidth(18);
        foreach (['B','L','M','N','O','P','X','AJ'] as $col) $sh->getColumnDimension($col)->setWidth(28);
        foreach (['Q', 'R', 'G', 'J', 'K', 'AA'] as $col) $sh->getColumnDimension($col)->setWidth(14);
        foreach (['AC','AD','AE','AF','AG'] as $col) $sh->getColumnDimension($col)->setWidth(20);
        foreach (['S','T','U','V','W','Y','Z','AB'] as $col) $sh->getColumnDimension($col)->setWidth(19);
        foreach (['S','T','U','V','W','Y','Z','AB','AC','AD','AE','AF','AG'] as $col) {
            $sh->getStyle("{$col}5:{$col}{$last}")->getNumberFormat()->setFormatCode('"Rp" #,##0;[Red]("Rp" #,##0)');
        }
        $sh->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    }

    public function download()
    {
        $this->build();
        $name = 'Rekap_Nota_SIRAKA_' . $this->startDate . '_sampai_' . $this->endDate . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'siraka_xlsx_');
        (new Xlsx($this->book))->save($tmp);
        $this->book->disconnectWorksheets();
        return response()->download($tmp, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}