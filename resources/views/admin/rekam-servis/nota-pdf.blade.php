@php
    $nota = $servis->nota;
    $konsumen = $servis->kendaraan->konsumen ?? null;
    $kendaraan = $servis->kendaraan ?? null;

    $rp = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

    $diagnosis = $servis->diagnosa_akhir ?: ($servis->diagnosa_awal ?: '-');
    $tindakan = $servis->tindakan_servis ?: '-';

    $statusPembayaran = strtolower((string) ($nota->status_pembayaran ?? 'belum_dibayar'));
    $lunas = $statusPembayaran === 'lunas';

    $noNota = $nota->no_nota ?? ('EST-RS-' . str_pad((string) $servis->id, 6, '0', STR_PAD_LEFT));

    $detailSpareparts = $servis->detailSpareparts ?? collect();
    $detailJasas = $servis->detailJasas ?? collect();

    $totalSparepart = 0;
    $totalJasa = 0;
    $totalDiskon = 0;

    foreach ($detailSpareparts as $item) {
    $qty = (int) ($item->qty ?? 1);

    $harga = (float) (
        $item->harga_jual_saat_transaksi ?? 0
    );

    $subtotal = $qty * $harga;

    $diskonNominal = (float) (
        $item->diskon_nominal ?? 0
    );

    $total = max(0, $subtotal - $diskonNominal);

    $totalSparepart += $total;
    $totalDiskon += $diskonNominal;
}

foreach ($detailJasas as $item) {
    $qty = (int) ($item->qty ?? 1);

    $harga = (float) (
        $item->harga_saat_transaksi ?? 0
    );

    $subtotal = $qty * $harga;

    $diskonNominal = (float) (
        $item->diskon_nominal ?? 0
    );

    $total = max(0, $subtotal - $diskonNominal);

    $totalJasa += $total;
    $totalDiskon += $diskonNominal;
}

        // Samakan dengan tampilan nota di layar:
    // sub total = harga kotor, diskon = diskon total pada tagihan.
    $subTotal = (float) $servis->subtotal();
    $totalDiskon = (float) ($nota->diskon ?? 0);

    $totalTagihan = $nota
        ? (float) $nota->total_biaya
        : max(0, $subTotal - $totalDiskon);
    $totalDibayar = $nota ? (float) $nota->pembayaran->sum('nominal_dialokasikan') : 0;
    $sisaTagihan = max(0, $totalTagihan - $totalDibayar);

    $tanggalNota = $nota && $nota->created_at
        ? \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y')
        : \Carbon\Carbon::parse($servis->tanggal_servis)->format('d/m/Y');

    $jamCetak = now()->format('d/m/Y, H:i');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Servis - {{ $noNota }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 12mm 17mm 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .page {
            position: static;
        }

        /* Tabel biasa mengikuti alur halaman; jangan beri tinggi tetap. */
        table { border-spacing: 0; }

        .watermark-lunas {
            position: fixed;
            top: 38%;
            left: 17%;
            width: 66%;
            text-align: center;
            font-size: 76px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #c62828;
            opacity: 0.18;
            transform: rotate(-22deg);
            z-index: 999;
        }

        .top-meta {
            width: 100%;
            font-size: 9px;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .top-meta td {
            vertical-align: top;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #0b3954;
            padding-bottom: 7px;
            margin-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-cell {
            width: 70px;
        }

        .logo-cell img {
            width: 60px;
            height: auto;
            display: block;
        }

        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: #0b3954;
            line-height: 1.1;
            margin: 0;
        }

        .brand-subtitle {
            margin-top: 4px;
            font-size: 8.5px;
            color: #374151;
            font-weight: bold;
        }

        .brand-address {
            margin-top: 3px;
            font-size: 9px;
            color: #4b5563;
        }

        .doc-meta {
            width: 208px;
            padding-left: 12px;
            text-align: left;
        }

        .doc-meta-table { width: 100%; border-collapse: collapse; }
        .doc-meta-table td { padding: 2px 0; vertical-align: top; }
        .doc-meta-table .doc-label-cell { width: 64px; }

        .doc-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }

        .doc-value {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
            word-wrap: break-word;
        }

        .doc-status {
            margin-top: 0;
            font-size: 9px;
            color: #4b5563;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0b3954;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            border-left: 3px solid #0b3954;
            padding-left: 8px;
        }

        .info-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .info-wrap td {
            vertical-align: top;
        }

        .info-side {
            width: 50%;
        }

        .info-side-left {
            padding-right: 15px;
        }

        .info-side-right {
            padding-left: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 10px;
        }

        .info-table .label {
            width: 29%;
            color: #64748b;
        }

        .info-table .colon {
            width: 4%;
            color: #64748b;
        }

        .info-table .value {
            width: 67%;
            font-weight: bold;
            color: #111827;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.45;
        }

        .info-table .value-normal {
            font-weight: normal;
        }

        .block-gap {
            height: 8px;
        }

        .table-title {
            font-size: 12px;
            font-weight: bold;
            color: #0b3954;
            margin: 14px 0 6px 0;
            text-transform: uppercase;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }

        .data-table th,
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 5px;
            font-size: 9px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data-table th {
            background: #0b3954;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
        }

        .data-table td {
            color: #111827;
        }

        .data-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .data-table tbody tr:last-child td { background: #eff6fa; }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
        }

        .diskon-note {
            color: #dc2626;
            font-size: 9px;
            font-style: italic;
            line-height: 1.35;
        }

        .summary-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .summary-wrap td {
            vertical-align: top;
        }

        .summary-left {
            width: 50%;
            padding-right: 12px;
        }

        .summary-right {
            width: 50%;
            padding-left: 12px;
        }

        .payment-box {
            font-size: 10.5px;
        }

        .payment-box .payment-title {
            font-size: 12px;
            font-weight: bold;
            color: #0b3954;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 0;
            font-size: 11px;
            vertical-align: top;
        }

        .summary-table .sum-label {
            color: #374151;
        }

        .summary-table .sum-value {
            text-align: right;
            font-weight: bold;
            color: #111827;
        }

        .summary-table .grand-total td {
            border-top: 1.5px solid #0b3954;
            border-bottom: 1.5px solid #0b3954;
            padding-top: 6px;
            padding-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
            background: #eff6fa;
        }

        .signature-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 26px;
        }

        .signature-wrap tr { page-break-inside: avoid; }

        .signature-wrap td {
            width: 50%;
            vertical-align: bottom;
            font-size: 10.5px;
        }

        .signature-box {
            padding-top: 12px;
        }

        .signature-right {
            text-align: right;
        }

        .signature-space {
            height: 46px;
        }

        .signature-name {
            font-weight: bold;
        }

        .small-note {
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="top-meta">
            <tr>
                <td>{{ $jamCetak }}</td>
                <td style="text-align:center;">Transaksi Servis - {{ $kendaraan->plat_nomor ?? '-' }}</td>
                <td style="text-align:right;"></td>
            </tr>
        </table>

        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @php
                            $logoPath = public_path('images/logo_bengkel.jpg');
                        @endphp

                        @if(file_exists($logoPath))
                            <img src="{{ $logoPath }}" alt="Logo Bengkel">
                        @endif
                    </td>

                    <td>
                        <div class="brand-title">BABA AUTO SERVICE</div>
                        <div class="brand-subtitle">SIRAKA • SISTEM INFORMASI PERAWATAN KENDARAAN</div>
                        <div class="brand-address">Jl. Soebrantas No. 88, Panam, Pekanbaru</div>
                    </td>

                    <td class="doc-meta">
                        <table class="doc-meta-table">
                            <tr>
                                <td class="doc-label-cell doc-label">No. Nota</td>
                                <td class="doc-value">{{ $noNota }}</td>
                            </tr>
                            <tr>
                                <td class="doc-label-cell doc-label">Tanggal</td>
                                <td class="doc-value">{{ $tanggalNota }}</td>
                            </tr>
                            <tr>
                                <td class="doc-label-cell doc-label">Status</td>
                                <td class="doc-status">{{ ucfirst(str_replace('_', ' ', $statusPembayaran)) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">Tagihan Servis</div>

        <table class="info-wrap">
            <tr>
                <td class="info-side info-side-left">
                    <table class="info-table">
                        <colgroup>
                            <col style="width:29%;">
                            <col style="width:4%;">
                            <col style="width:67%;">
                        </colgroup>
                        <tr>
                            <td class="label">Nama</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $konsumen->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Telepon</td>
                            <td class="colon">:</td>
                            <td class="value value-normal">{{ $konsumen->no_hp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Diagnosis</td>
                            <td class="colon">:</td>
                            <td class="value value-normal">{{ $diagnosis }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tindakan</td>
                            <td class="colon">:</td>
                            <td class="value value-normal">{{ $tindakan }}</td>
                        </tr>
                    </table>
                </td>

                <td class="info-side info-side-right">
                    <table class="info-table">
                        <colgroup>
                            <col style="width:29%;">
                            <col style="width:4%;">
                            <col style="width:67%;">
                        </colgroup>
                        <tr>
                            <td class="label">Plat Nomor</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $kendaraan->plat_nomor ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Merk / Tipe</td>
                            <td class="colon">:</td>
                            <td class="value">{{ trim(($kendaraan->merk ?? '-') . ' ' . ($kendaraan->tipe_model ?? '')) }}</td>
                        </tr>
                        <tr>
                            <td class="label">KM Masuk</td>
                            <td class="colon">:</td>
                            <td class="value">
                                {{ $servis->km_masuk ? number_format((float) $servis->km_masuk, 0, ',', '.') . ' KM' : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">KM Keluar</td>
                            <td class="colon">:</td>
                            <td class="value">
                                {{ $servis->km_keluar ? number_format((float) $servis->km_keluar, 0, ',', '.') . ' KM' : '-' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="table-title">Biaya Suku Cadang</div>
        <table class="data-table">
            <colgroup>
                <col style="width:5%;">
                <col style="width:29%;">
                <col style="width:7%;">
                <col style="width:15%;">
                <col style="width:15%;">
                <col style="width:14%;">
                <col style="width:15%;">
            </colgroup>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Deskripsi</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                    <th>Diskon</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailSpareparts as $i => $item)
                    @php
                        $qty = (float) ($item->qty ?? $item->jumlah ?? 1);
                        $harga = (float) ($item->harga_jual_saat_transaksi ?? 0);
                        $subtotal = (float) ($item->subtotal ?? ($qty * $harga));
                        $diskonPersen = (float) ($item->diskon_persen ?? 0);
                        $diskonNominal = (float) ($item->diskon_nominal ?? 0);
                        $total = (float) ($item->total ?? ($subtotal - $diskonNominal));
                        $namaItem = $item->sparepart->nama_sparepart ?? $item->sparepart->nama ?? '-';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $namaItem }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="text-right">{{ $rp($harga) }}</td>
                        <td class="text-right">{{ $rp($subtotal) }}</td>
                        <td class="text-right">
                            @if($diskonPersen > 0 || $diskonNominal > 0)
                                <div class="diskon-note">
                                    @if($diskonPersen > 0)
                                        disc {{ number_format($diskonPersen, 2, ',', '.') }}%
                                    @endif
                                    <br>
                                    ({{ $rp($diskonNominal) }})
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right text-bold">{{ $rp($total) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center muted">Tidak ada suku cadang pada transaksi ini.</td>
                    </tr>
                @endforelse

                @if($detailSpareparts->count())
                    <tr>
                        <td colspan="4" class="text-right text-bold">Jumlah total</td>
                        <td class="text-right text-bold">{{ $rp($detailSpareparts->sum(function ($item) {
                            $qty = (float) ($item->qty ?? $item->jumlah ?? 1);
                            $harga = (float) ($item->harga_jual_saat_transaksi ?? 0);
                            return (float) ($item->subtotal ?? ($qty * $harga));
                        })) }}</td>
                        <td class="text-right text-bold">
                            {{ $rp($detailSpareparts->sum(function ($item) {
                                return (float) ($item->diskon_nominal ?? 0);
                            })) }}
                        </td>
                        <td class="text-right text-bold">{{ $rp($totalSparepart) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="table-title">Biaya Jasa Servis</div>
        <table class="data-table">
            <colgroup>
                <col style="width:5%;">
                <col style="width:38%;">
                <col style="width:8%;">
                <col style="width:17%;">
                <col style="width:15%;">
                <col style="width:17%;">
            </colgroup>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Deskripsi</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Diskon</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailJasas as $i => $item)
                    @php
                        $qty = (float) ($item->qty ?? $item->jumlah ?? 1);
                       $harga = (float) ($item->harga_saat_transaksi ?? 0);
                        $subtotal = (float) ($item->subtotal ?? ($qty * $harga));
                        $diskonPersen = (float) ($item->diskon_persen ?? 0);
                        $diskonNominal = (float) ($item->diskon_nominal ?? 0);
                        $total = (float) ($item->total ?? ($subtotal - $diskonNominal));
                        $namaItem = $item->jasa->nama_jasa ?? $item->jasa->nama ?? '-';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $namaItem }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="text-right">{{ $rp($subtotal) }}</td>
                        <td class="text-right">
                            @if($diskonPersen > 0 || $diskonNominal > 0)
                                <div class="diskon-note">
                                    @if($diskonPersen > 0)
                                        disc {{ number_format($diskonPersen, 2, ',', '.') }}%
                                    @endif
                                    <br>
                                    ({{ $rp($diskonNominal) }})
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right text-bold">{{ $rp($total) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center muted">Tidak ada jasa pada transaksi ini.</td>
                    </tr>
                @endforelse

                @if($detailJasas->count())
                    <tr>
                        <td colspan="3" class="text-right text-bold">Jumlah total</td>
                        <td class="text-right text-bold">{{ $rp($detailJasas->sum(function ($item) {
                            $qty = (float) ($item->qty ?? $item->jumlah ?? 1);
                            $harga = (float) ($item->harga_saat_transaksi ?? 0);
                            return (float) ($item->subtotal ?? ($qty * $harga));
                        })) }}</td>
                        <td class="text-right text-bold">
                            {{ $rp($detailJasas->sum(function ($item) {
                                return (float) ($item->diskon_nominal ?? 0);
                            })) }}
                        </td>
                        <td class="text-right text-bold">{{ $rp($totalJasa) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="summary-wrap">
            <tr>
                <td class="summary-left">
                    <div class="payment-box">
                        <div class="payment-title">Pembayaran</div>

                        @if(!$nota || $nota->pembayaran->isEmpty())
                            <div class="muted">Belum ada pembayaran</div>
                        @else
                            @foreach($nota->pembayaran as $bayar)
                                <div style="margin-bottom:6px;">
                                    <strong>{{ \Carbon\Carbon::parse($bayar->created_at)->format('d/m/Y') }}</strong>
                                    — {{ $rp($bayar->nominal_dialokasikan) }}
                                    @if($bayar->penerima)
                                        <span class="muted">({{ $bayar->penerima->name }})</span>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </td>

                <td class="summary-right">
                    <table class="summary-table">
                        <tr>
                            <td class="sum-label">SUB TOTAL</td>
                            <td class="sum-value">{{ $rp($subTotal) }}</td>
                        </tr>
                        <tr>
                            <td class="sum-label">DISKON</td>
                            <td class="sum-value">{{ $rp($totalDiskon) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td class="sum-label">TOTAL TAGIHAN</td>
                            <td class="sum-value">{{ $rp($totalTagihan) }}</td>
                        </tr>
                        <tr>
                            <td class="sum-label">TOTAL DIBAYAR</td>
                            <td class="sum-value">{{ $rp($totalDibayar) }}</td>
                        </tr>
                        <tr>
                            <td class="sum-label">SISA TAGIHAN</td>
                            <td class="sum-value">{{ $rp($sisaTagihan) }}</td>
                        </tr>
                        <tr>
                            <td class="sum-label">CATATAN</td>
                            <td class="sum-value">
                                @if($lunas)
                                    Lunas
                                @elseif($totalDibayar > 0)
                                    Dibayar Sebagian
                                @else
                                    Belum Dibayar
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="signature-wrap">
            <tr>
                <td>
                    <div class="signature-box">
                        Pelanggan
                        <div class="signature-space"></div>
                        <div class="signature-name">( {{ $konsumen->nama_lengkap ?? '-' }} )</div>
                    </div>
                </td>
                <td class="signature-right">
                    <div class="signature-box">
                        Hormat kami,
                        <div class="signature-space"></div>
                        <div class="signature-name">( Baba Auto Service )</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @if($lunas)
        {{-- Dirender setelah isi supaya cap berada di atas konten, tetap transparan. --}}
        <div class="watermark-lunas">LUNAS</div>
    @endif
</body>
</html>
