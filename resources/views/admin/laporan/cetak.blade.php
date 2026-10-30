<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Pemasukan SIRAKA — {{ $startDate }} s.d. {{ $endDate }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f3f5; color: #222; font: 12px Arial, Helvetica, sans-serif; }
        .actions { max-width: 1100px; margin: 18px auto; display: flex; justify-content: space-between; gap: 12px; }
        .actions a, .actions button { border: 1px solid #bbb; border-radius: 5px; padding: 9px 14px; background: #fff; color: #222; text-decoration: none; cursor: pointer; font-size: 12px; }
        .actions button { background: #222; color: #fff; }
        .paper { max-width: 1100px; margin: 0 auto 30px; background: #fff; padding: 36px 40px; box-shadow: 0 3px 18px #0002; }
        .header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 12px; }
        .header h1 { margin: 0 0 4px; font-size: 19px; letter-spacing: .5px; }
        .header p { margin: 3px 0; font-size: 11px; }
        .report-title { text-align: center; margin: 20px 0; }
        .report-title h2 { margin: 0 0 6px; text-transform: uppercase; font-size: 15px; }
        .report-title p { margin: 3px 0; color: #555; }
        .section { margin-top: 22px; }
        .section h3 { margin: 0 0 9px; font-size: 12px; text-transform: uppercase; }
        .note { color: #555; font-size: 10px; line-height: 1.5; margin: 6px 0 0; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        th, td { border: 1px solid #bbb; padding: 7px 8px; vertical-align: top; }
        th { background: #efefef; font-weight: bold; text-align: left; }
        .num { text-align: right; white-space: nowrap; }
        .total td { font-weight: bold; background: #f4f4f4; }
        .signature { margin-top: 35px; display: flex; justify-content: flex-end; }
        .signature > div { text-align: center; width: 230px; line-height: 1.6; }
        .signature .signline { margin-top: 58px; border-top: 1px solid #222; padding-top: 3px; }
        .muted { color: #555; }

        @media (max-width: 700px) {
            .paper { padding: 16px; }
            .actions { padding: 0 12px; }
            .table-wrap table { min-width: 760px; }
        }
        @page { size: A4 landscape; margin: 12mm; }
        @media print {
            body { background: #fff; font-size: 11px; }
            .actions { display: none !important; }
            .paper { max-width: none; margin: 0; padding: 0; box-shadow: none; }
            .table-wrap { overflow: visible; }
            .table-wrap table { min-width: 0 !important; }
            .section { break-inside: auto; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
            h3 { break-after: avoid; }
            table { font-size: 9px; }
            th, td { padding: 5px 6px; }
            a { color: inherit; }
        }
    </style>
</head>
<body>
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $sumTop = (object) [
        'unit' => $sparepartTerlaris->sum('unit_terjual'),
        'omzet' => $sparepartTerlaris->sum('omzet'),
        'modal' => $sparepartTerlaris->sum('modal'),
        'laba' => $sparepartTerlaris->sum('laba_kotor'),
    ];
@endphp

<div class="actions">
    <a href="{{ route('admin.laporan.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}">← Kembali ke laporan</a>
    <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<main class="paper">
    <div class="header">
        <h1>BABA AUTO SERVICE</h1>
        <p>SISTEM INFORMASI REKAM KENDARAAN &amp; ADMINISTRASI (SIRAKA)</p>
    </div>

    <div class="report-title">
        <h2>Laporan Pemasukan Bengkel</h2>
        <p>Periode {{ \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y') }}</p>
        <p style="margin-top: 5px;">Tanggal cetak: {{ now()->format('d/m/Y H:i') }} · Petugas: {{ auth()->user()->name ?? '-' }}</p>
    </div>

    <section class="section">
        <h3>A. Rekapitulasi Keuangan</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Uraian</th><th class="num" style="width: 23%;">Jumlah</th></tr>
                </thead>
                <tbody>
                    <tr><td>Penerimaan servis (sparepart dan jasa, termasuk cicilan)</td><td class="num">{{ $rp($pendapatanKotor) }}</td></tr>
                    <tr><td>Tips pelanggan</td><td class="num">{{ $rp($totalTips) }}</td></tr>
                    <tr class="total"><td>Total pemasukan</td><td class="num">{{ $rp($totalPemasukan) }}</td></tr>
                    <tr><td>Modal sparepart pada nota yang menerima pembayaran dalam periode</td><td class="num">{{ $rp($totalModalSparepart) }}</td></tr>
                </tbody>
            </table>
        </div>
        <p class="note">
            Uang diterima: {{ $rp($totalUangDiterima) }}; kembalian: {{ $rp($totalKembalian) }}.
            Total pemasukan mencakup penerimaan servis dan tips. Modal sparepart dihitung sekali per nota yang mempunyai pembayaran dalam periode,
            bukan pengeluaran kas pembelian sparepart pada periode tersebut. Laporan ini tidak menghitung laba bersih bengkel.
        </p>
    </section>

    <section class="section">
        <h3>B. Rincian Pembayaran ({{ $pembayaran->count() }} Pembayaran)</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Bayar</th>
                        <th>No. Nota</th>
                        <th>Pelanggan / Plat Nomor</th>
                        <th>Petugas Penerima</th>
                        <th>Metode</th>
                        <th class="num">Untuk Tagihan</th>
                        <th class="num">Diterima</th>
                        <th class="num">Kembalian</th>
                        <th class="num">Tips</th>
                        <th class="num">Pemasukan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->no_nota }}</td>
                            <td>{{ $p->nama_lengkap ?? '-' }}<br><span class="muted">{{ $p->plat_nomor ?? '-' }}</span></td>
                            <td>{{ $p->petugas_penerima ?? 'Petugas tidak tersedia' }}</td>
                            <td>{{ strtoupper($p->metode_pembayaran ?? '-') }}</td>
                            <td class="num">{{ $rp($p->nominal_dialokasikan) }}</td>
                            <td class="num">{{ $rp($p->jumlah_diterima) }}</td>
                            <td class="num">{{ $rp($p->kembalian) }}</td>
                            <td class="num">{{ $rp($p->tips) }}</td>
                            <td class="num">{{ $rp($p->jumlah_diterima - $p->kembalian) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align: center;">Tidak ada pembayaran untuk periode ini.</td></tr>
                    @endforelse
                    @if($pembayaran->isNotEmpty())
                        <tr class="total">
                            <td colspan="5">TOTAL</td>
                            <td class="num">{{ $rp($pendapatanKotor) }}</td>
                            <td class="num">{{ $rp($totalUangDiterima) }}</td>
                            <td class="num">{{ $rp($totalKembalian) }}</td>
                            <td class="num">{{ $rp($totalTips) }}</td>
                            <td class="num">{{ $rp($totalPemasukan) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <section class="section">
        <h3>C. Analisis Penjualan Sparepart (10 Terlaris)</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th>Nama Sparepart</th>
                        <th class="num">Jumlah Terjual</th>
                        <th class="num">Jumlah Tagihan</th>
                        <th class="num">Omzet</th>
                        <th class="num">Modal</th>
                        <th class="num">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sparepartTerlaris as $i => $sp)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $sp->nama_barang }} @if($sp->kode_barang)<span class="muted">({{ $sp->kode_barang }})</span>@endif</td>
                            <td class="num">{{ $sp->unit_terjual }}</td>
                            <td class="num">{{ $sp->frekuensi }}</td>
                            <td class="num">{{ $rp($sp->omzet) }}</td>
                            <td class="num">{{ $rp($sp->modal) }}</td>
                            <td class="num">{{ $rp($sp->laba_kotor) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center;">Tidak ada penjualan sparepart pada periode ini.</td></tr>
                    @endforelse
                    @if($sparepartTerlaris->isNotEmpty())
                        <tr class="total">
                            <td colspan="2">Total 10 teratas</td>
                            <td class="num">{{ $sumTop->unit }}</td>
                            <td></td>
                            <td class="num">{{ $rp($sumTop->omzet) }}</td>
                            <td class="num">{{ $rp($sumTop->modal) }}</td>
                            <td class="num">{{ $rp($sumTop->laba) }}</td>
                        </tr>
                        <tr class="total">
                            <td colspan="2">Seluruh sparepart periode ini</td>
                            <td class="num">{{ $ringkasanSparepart->unit_terjual }}</td>
                            <td></td>
                            <td class="num">{{ $rp($ringkasanSparepart->omzet) }}</td>
                            <td class="num">{{ $rp($ringkasanSparepart->modal) }}</td>
                            <td class="num">{{ $rp($ringkasanSparepart->laba_kotor) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <p class="note">
            Nilai penjualan sparepart berasal dari tagihan yang diterbitkan dalam periode, termasuk tagihan yang belum lunas. Diskon nota dialokasikan secara proporsional.
            Pada periode tanggal servis yang sama terdapat {{ $ringkasanBelumLunas->unit }} sparepart terpakai dalam
            {{ $ringkasanBelumLunas->jumlah_servis }} servis belum lunas
            (nilai sebelum diskon {{ $rp($ringkasanBelumLunas->nilai) }}).
        </p>
    </section>

    <div class="signature">
        <div>
            Pekanbaru, {{ now()->locale('id')->translatedFormat('d F Y') }}<br>
            Kepala Bengkel / Owner
            <div class="signline">Baba Auto Service</div>
        </div>
    </div>
</main>
</body>
</html>
