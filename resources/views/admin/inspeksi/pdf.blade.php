@php
    $namaKonsumen = $inspeksi->nama_pelanggan_inspeksi;
    $noHp = $inspeksi->nomor_wa_pelanggan_inspeksi;

    $platInfo = $inspeksi->plat_nomor_inspeksi
        ?: ($inspeksi->kendaraan?->plat_nomor ?? '-');

    $merkModel = trim(
        ($inspeksi->merk_inspeksi ?? $inspeksi->kendaraan?->merk ?? '') . ' ' .
        ($inspeksi->tipe_model_inspeksi ?? $inspeksi->kendaraan?->tipe_model ?? '')
    ) ?: '-';

    $rp = fn($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');

    $bagian = [
        [
            'judul' => 'Kelistrikan & Fitur Interior',
            'data' => $inspeksi->kelistrikan,
            'kolom' => [
                'Komponen' => 'komponen',
                'Status' => 'status',
                'Keterangan' => 'keterangan'
            ]
        ],
        [
            'judul' => 'Kondisi Interior',
            'data' => $inspeksi->interior,
            'kolom' => [
                'Komponen' => 'komponen',
                'Status' => 'status',
                'Keterangan' => 'keterangan'
            ]
        ],
        [
            'judul' => 'Dokumen Kendaraan',
            'data' => $inspeksi->dokumen,
            'kolom' => [
                'Dokumen' => 'dokumen',
                'Status' => 'status',
                'Keterangan' => 'keterangan'
            ]
        ],
        [
            'judul' => 'Exterior',
            'data' => $inspeksi->exterior,
            'kolom' => [
                'Komponen' => 'komponen',
                'Status' => 'status',
                'Keterangan' => 'keterangan'
            ]
        ],
        [
            'judul' => 'Mesin, Transmisi, Steering & Brake',
            'data' => $inspeksi->mesin,
            'kolom' => [
                'Komponen' => 'komponen',
                'Status' => 'status',
                'Keterangan' => 'keterangan'
            ]
        ],
    ];

    $kategoriEstimasi = [
        'engine' => 'ENGINE',
        'kaki_kaki' => 'KAKI-KAKI DEPAN & BELAKANG',
        'kelistrikan' => 'KELISTRIKAN',
        'exterior' => 'EXTERIOR',
        'interior' => 'INTERIOR',
    ];

    $grandTotal = (float) $inspeksi->estimasi->sum(
        fn($item) =>
            ((float) $item->harga_part * (float) $item->qty)
            + (float) $item->harga_jasa
    );

    $logoPath = public_path('images/logo_bengkel.jpg');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>Laporan Inspeksi {{ $inspeksi->nomor_inspeksi }}</title>

    <style>
        @page {
            margin: 14mm 12mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 9px;
            line-height: 1.45;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid #0b3954;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header-table,
        .info,
        .condition,
        .sign {
            width: 100%;
            border-collapse: collapse;
        }

        .logo {
            width: 62px;
            vertical-align: middle;
        }

        .logo img {
            width: 54px;
            height: 54px;
        }

        .brand {
            vertical-align: middle;
        }

        .brand-name {
            color: #0b3954;
            font-size: 19px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .brand-sub {
            color: #475569;
            font-size: 8px;
            font-weight: bold;
            margin-top: 3px;
        }

        .brand-address {
            color: #64748b;
            font-size: 8px;
            margin-top: 2px;
        }

        .doc-meta {
            width: 180px;
            vertical-align: middle;
        }

        .doc-meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-meta td {
            padding: 2px 0;
        }

        .doc-meta td:first-child {
            width: 58px;
            color: #64748b;
        }

        .doc-meta td:last-child {
            font-weight: bold;
        }

        h1 {
            text-align: center;
            color: #0b3954;
            font-size: 16px;
            margin: 0 0 3px;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 14px;
            font-size: 8px;
        }

        .section-title {
            color: #0b3954;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-left: 3px solid #0b3954;
            padding-left: 7px;
            margin: 14px 0 7px;
        }

        .info {
            margin-bottom: 10px;
        }

        .info td {
            width: 50%;
            padding: 3px 8px 3px 0;
            vertical-align: top;
        }

        .label {
            display: block;
            color: #64748b;
            font-size: 7px;
            text-transform: uppercase;
        }

        .value {
            display: block;
            font-weight: bold;
            font-size: 9px;
        }

        .condition {
            margin: 10px 0 14px;
        }

        .condition td {
            width: 20%;
            border: 1px solid #cbd5e1;
            padding: 7px 4px;
            text-align: center;
        }

        .condition .name {
            display: block;
            color: #64748b;
            font-size: 7px;
            text-transform: uppercase;
        }

        .condition .status {
            display: block;
            font-weight: bold;
            margin-top: 3px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0 12px;
        }

        table.data th {
            background: #0b3954;
            color: white;
            font-weight: bold;
            text-align: left;
        }

        table.data th,
        table.data td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: top;
        }

        table.data tr:nth-child(even) td {
            background: #f8fafc;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .total-row td {
            background: #eff6fa;
            font-weight: bold;
        }

        .grand {
            text-align: right;
            border-top: 2px solid #0b3954;
            padding-top: 8px;
            margin-top: 8px;
        }

        .grand-label {
            color: #64748b;
            font-weight: bold;
            font-size: 9px;
        }

        .grand-value {
            color: #0b3954;
            font-size: 17px;
            font-weight: bold;
        }

        .note {
            border: 1px solid #cbd5e1;
            padding: 9px;
            margin-top: 14px;
        }

        .sign {
            margin-top: 35px;
        }

        .sign td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .sign-space {
            height: 55px;
        }

        .sign-name {
            display: inline-block;
            border-top: 1px solid #172033;
            padding: 5px 18px 0;
            font-weight: bold;
        }

        .muted {
            color: #64748b;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo">
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo">
                    @endif
                </td>

                <td class="brand">
                    <div class="brand-name">BABA AUTO SERVICE</div>

                    <div class="brand-sub">
                        SIRAKA • SISTEM INFORMASI PERAWATAN KENDARAAN
                    </div>

                    <div class="brand-address">
                        Jl. Soebrantas No. 88, Panam, Pekanbaru
                    </div>
                </td>

                <td class="doc-meta">
                    <table>
                        <tr>
                            <td>No. Inspeksi</td>
                            <td>{{ $inspeksi->nomor_inspeksi }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>
                                {{ $inspeksi->tanggal_inspeksi?->format('d/m/Y') ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Inspektur</td>
                            <td>{{ $inspeksi->user?->name ?? 'Admin' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <h1>Laporan Inspeksi Kendaraan</h1>

    <div class="subtitle">
        Dokumen hasil pemeriksaan kendaraan Baba Auto Service
    </div>

    {{-- IDENTITAS --}}
    <div class="section-title">
        Identitas Konsumen & Kendaraan
    </div>

    <table class="info">
        <tr>
            <td>
                <span class="label">Nama Konsumen</span>
                <span class="value">{{ $namaKonsumen ?: '-' }}</span>
            </td>

            <td>
                <span class="label">Nomor WhatsApp</span>
                <span class="value">{{ $noHp ?: '-' }}</span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">Merk / Model / Tipe</span>
                <span class="value">{{ $merkModel }}</span>
            </td>

            <td>
                <span class="label">Plat Nomor</span>
                <span class="value">{{ $platInfo }}</span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">CC & Transmisi</span>
                <span class="value">
                    {{ $inspeksi->cc_inspeksi ?: '-' }}
                    {{ $inspeksi->transmisi_inspeksi
                        ? '(' . ucfirst($inspeksi->transmisi_inspeksi) . ')'
                        : '' }}
                </span>
            </td>

            <td>
                <span class="label">Tahun Kendaraan</span>
                <span class="value">
                    {{ $inspeksi->tahun_inspeksi ?: ($inspeksi->kendaraan?->tahun ?? '-') }}
                </span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">Kilometer</span>
                <span class="value">
                    {{ $inspeksi->km_kendaraan
                        ? number_format($inspeksi->km_kendaraan, 0, ',', '.') . ' km'
                        : '-' }}
                </span>
            </td>

            <td>
                <span class="label">Rating Inspeksi</span>
                <span class="value">{{ $inspeksi->rating_score }}/10</span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">Riwayat Banjir</span>
                <span class="value">
                    {{ ucfirst($inspeksi->ex_banjir ?? 'tidak') }}
                </span>
            </td>

            <td>
                <span class="label">Riwayat Tabrak</span>
                <span class="value">
                    {{ ucfirst($inspeksi->ex_tabrak ?? 'tidak') }}
                </span>
            </td>
        </tr>
    </table>

    {{-- KONDISI UMUM --}}
    <div class="section-title">
        Ringkasan Kondisi Kendaraan
    </div>

    <table class="condition">
        <tr>
            @foreach(['mesin', 'transmisi', 'kaki_kaki', 'eksterior', 'interior'] as $kondisi)
                <td>
                    <span class="name">
                        {{ str_replace('_', ' ', $kondisi) }}
                    </span>

                    <span class="status">
                        {{ $inspeksi->{'kondisi_' . $kondisi} ?? '-' }}
                    </span>
                </td>
            @endforeach
        </tr>
    </table>

    {{-- DETAIL INSPEKSI --}}
    @foreach($bagian as $section)

        @if($section['data']->isNotEmpty())

            <div class="section-title">
                {{ $section['judul'] }}
            </div>

            <table class="data">
                <thead>
                    <tr>
                        @foreach($section['kolom'] as $judul => $field)
                            <th>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach($section['data'] as $item)
                        <tr>
                            @foreach($section['kolom'] as $field)
                                <td>{{ $item->{$field} ?: '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @endif

    @endforeach

    {{-- KAKI-KAKI --}}
    @if($inspeksi->kakiKaki->isNotEmpty())

        <div class="section-title">
            Kaki-Kaki Depan & Belakang
        </div>

        <table class="data">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th class="center">Kiri</th>
                    <th class="center">Kanan</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @foreach($inspeksi->kakiKaki as $item)
                    <tr>
                        <td>{{ $item->komponen ?: '-' }}</td>
                        <td class="center">{{ $item->status_kiri ?: '-' }}</td>
                        <td class="center">{{ $item->status_kanan ?: '-' }}</td>
                        <td>{{ $item->catatan ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

    {{-- ESTIMASI PERBAIKAN --}}
    <div class="section-title">
        Estimasi Perbaikan
    </div>

    @forelse($kategoriEstimasi as $key => $judul)

        @php
            $items = $inspeksi->estimasi->where('kategori', $key);
        @endphp

        @if($items->isNotEmpty())

            <div style="font-weight:bold; color:#475569; margin:8px 0 4px;">
                {{ $judul }}
            </div>

            <table class="data">
                <thead>
                    <tr>
                        <th class="center" style="width:7%">No</th>
                        <th>Nama Part / Jasa</th>
                        <th class="right" style="width:18%">Harga Part</th>
                        <th class="right" style="width:18%">Jasa</th>
                        <th class="right" style="width:20%">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $subtotalKategori = 0;
                    @endphp

                    @foreach($items as $index => $item)

                        @php
                            $totalItem =
                                ((float) $item->harga_part * (float) $item->qty)
                                + (float) $item->harga_jasa;

                            $subtotalKategori += $totalItem;
                        @endphp

                        <tr>
                            <td class="center">{{ $index + 1 }}</td>

                            <td>{{ $item->nama_item }}</td>

                            <td class="right">
                                {{ $item->harga_part > 0
                                    ? $rp($item->harga_part * $item->qty)
                                    : '-' }}
                            </td>

                            <td class="right">
                                {{ $item->harga_jasa > 0
                                    ? $rp($item->harga_jasa)
                                    : '-' }}
                            </td>

                            <td class="right">
                                {{ $rp($totalItem) }}
                            </td>
                        </tr>

                    @endforeach

                    <tr class="total-row">
                        <td colspan="4" class="right">
                            Subtotal {{ $judul }}
                        </td>

                        <td class="right">
                            {{ $rp($subtotalKategori) }}
                        </td>
                    </tr>
                </tbody>
            </table>

        @endif

    @empty
        <p class="muted">Belum terdapat estimasi perbaikan.</p>
    @endforelse

    @if($inspeksi->estimasi->isNotEmpty())
        <div class="grand">
            <div class="grand-label">
                GRAND TOTAL ESTIMASI
            </div>

            <div class="grand-value">
                {{ $rp($grandTotal) }}
            </div>
        </div>
    @else
        <p class="muted">Belum terdapat rincian estimasi perbaikan.</p>
    @endif

    {{-- CATATAN --}}
    @if($inspeksi->catatan_penting || $inspeksi->saran)
        <div class="note">
            <strong>Catatan Penting & Saran</strong>
            <br>

            @if($inspeksi->saran)
                {{ $inspeksi->saran }}
            @endif
        </div>
    @endif

    {{-- TANDA TANGAN --}}
    <table class="sign">
        <tr>
            <td>
                Konsumen

                <div class="sign-space"></div>

                <span class="sign-name">
                    {{ $namaKonsumen ?: '-' }}
                </span>
            </td>

            <td>
                Inspektur

                <div class="sign-space"></div>

                <span class="sign-name">
                    {{ $inspeksi->user?->name ?? 'Admin' }}
                </span>
            </td>
        </tr>
    </table>

</body>
</html>