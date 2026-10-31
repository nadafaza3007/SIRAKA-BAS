@extends('layouts.admin')
@section('title', 'Detail Laporan Inspeksi - ' . $inspeksi->nomor_inspeksi)

@section('content')
<style>
/* ============================================
   RESPONSIVE MOBILE - Tabel Tetap Tabel
   ============================================ */
@media screen and (max-width: 768px) {
    #printAreaStandar {
        padding: 10px !important;
    }

    #printAreaStandar h1 {
        font-size: 1rem !important;
    }

    #printAreaStandar h2 {
        font-size: 0.75rem !important;
    }

    #printAreaStandar .grid-cols-1 {
        gap: 6px !important;
    }

    #printAreaStandar .grid-cols-1 > div {
        font-size: 11px !important;
    }

    /* Kondisi Umum - 3 kolom di HP */
    #printAreaStandar .sm\:grid-cols-5 {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 4px !important;
    }

    #printAreaStandar .sm\:grid-cols-5 > div {
        padding: 6px !important;
        font-size: 10px !important;
    }

    /* Semua tabel */
    #printAreaStandar table {
        font-size: 10px !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    #printAreaStandar table th {
        padding: 4px 6px !important;
        font-size: 9px !important;
        font-weight: 700 !important;
    }

    #printAreaStandar table td {
        padding: 4px 6px !important;
        font-size: 10px !important;
        word-break: break-word !important;
    }

    #printAreaStandar table thead {
        display: table-header-group !important;
    }

    #printAreaStandar table tbody {
        display: table-row-group !important;
    }

    #printAreaStandar table tbody tr {
        display: table-row !important;
        margin: 0 !important;
        border: none !important;
        padding: 0 !important;
        background: transparent !important;
    }

    #printAreaStandar table tbody td {
        display: table-cell !important;
        border: 1px solid #d1d5db !important;
        padding: 3px 5px !important;
    }

    #printAreaStandar table tbody td::before {
        content: none !important;
    }

    /* Subtotal */
    #printAreaStandar table tbody tr.bg-gray-50 td {
        background: #f9fafb !important;
        font-weight: 700 !important;
    }

    /* Grand Total */
    #printAreaStandar .text-2xl {
        font-size: 1.25rem !important;
    }

    /* Tanda tangan */
    #printAreaStandar .flex.justify-between {
        font-size: 10px !important;
    }

    #printAreaStandar .flex.justify-between > div {
        margin-bottom: 40px !important;
    }
}

/* Print tetap normal */
@media print {
    #printAreaStandar .grid-cols-1 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    #printAreaStandar .sm\:grid-cols-5 {
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
    }

    #printAreaStandar table {
        min-width: 0 !important;
    }
}
</style>

@php
    $noHp = preg_replace('/[^0-9]/', '', $inspeksi->konsumen->no_hp ?? '');

    if (str_starts_with($noHp, '0')) {
        $noHp = '62' . substr($noHp, 1);
    } elseif (str_starts_with($noHp, '8')) {
        $noHp = '62' . $noHp;
    }

    $namaKonsumen = $inspeksi->konsumen->nama_lengkap ?? 'Pelanggan';
    $platInfo = $inspeksi->kendaraan ? ($inspeksi->kendaraan->plat_nomor ?? '') : '';

    $waLink = 'https://wa.me/' . $noHp . '?text=' . urlencode(
        "Halo {$namaKonsumen}, berikut kami sampaikan laporan inspeksi kendaraan {$platInfo}."
    );
@endphp

{{-- Tombol Aksi --}}
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between no-print">
    <a href="{{ route('admin.inspeksi.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-neutral-500 hover:text-white">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
    </a>
    <div class="flex gap-2">
        @if($noHp)
        <a href="{{ $waLink }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:bg-emerald-700">
            <i class="fa-brands fa-whatsapp"></i> Hubungi via WhatsApp
        </a>
        @endif
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:bg-brand-600">
            <i class="fa-solid fa-print"></i> Cetak / Print PDF
        </button>
    </div>
</div>

{{-- Area Laporan --}}
<div id="printAreaStandar" class="mx-auto min-w-0 max-w-4xl rounded-xl bg-white p-3 text-black shadow-lg sm:p-8">

    {{-- Header Laporan --}}
    <div class="mb-6 border-b-2 border-black pb-4">
        <h1 class="text-center text-2xl font-extrabold uppercase tracking-wider">Laporan Inspeksi Kendaraan</h1>
        <p class="mt-1 text-center text-xs text-gray-600">No. Inspeksi: <span class="font-bold text-black">{{ $inspeksi->nomor_inspeksi }}</span></p>
    </div>

    {{-- Info Kendaraan --}}
    <div class="mb-6 grid grid-cols-1 gap-3 text-xs sm:grid-cols-2 sm:gap-4">
        @if($inspeksi->kendaraan)
            <div>
                <p class="font-bold">Merk/Model/Type:</p>
                <p>{{ $inspeksi->kendaraan->merk ?? '-' }} {{ $inspeksi->kendaraan->tipe_model ?? '' }}</p>
            </div>
            <div>
                <p class="font-bold">CC & Transmisi:</p>
                <p>{{ $inspeksi->kendaraan->cc ? $inspeksi->kendaraan->cc . ' cc' : '-' }} {{ $inspeksi->kendaraan->transmisi ? '(' . $inspeksi->kendaraan->transmisi_label . ')' : '' }}</p>
            </div>
            <div>
                <p class="font-bold">Tahun:</p>
                <p>{{ $inspeksi->kendaraan->tahun ?? '-' }}</p>
            </div>
            <div>
                <p class="font-bold">Plat Nomor:</p>
                <p>{{ $inspeksi->kendaraan->plat_nomor ?? '-' }}</p>
            </div>
        @else
            <div class="sm:col-span-2 rounded-lg border border-amber-500/30 bg-amber-500/5 p-3 text-center">
                <p class="font-bold text-amber-400"> Kendaraan tidak terdaftar</p>
                <p class="text-xs text-neutral-400 mt-1">Inspeksi dilakukan tanpa data kendaraan spesifik</p>
            </div>
        @endif
        <div>
            <p class="font-bold">Kilometer:</p>
            <p>{{ $inspeksi->km_kendaraan ? number_format($inspeksi->km_kendaraan, 0, ',', '.') . ' km' : '-' }}</p>
        </div>
        <div>
            <p class="font-bold">Rating Score:</p>
            <p>{{ $inspeksi->rating_score }} / 10</p>
        </div>
        <div>
            <p class="font-bold">Ex Banjir:</p>
            <p>{{ ucfirst($inspeksi->ex_banjir ?? 'tidak') }}</p>
        </div>
        <div>
            <p class="font-bold">Ex Tabrak:</p>
            <p>{{ ucfirst($inspeksi->ex_tabrak ?? 'tidak') }}</p>
        </div>
    </div>

    {{-- Kondisi Umum --}}
    <div class="mb-6 grid grid-cols-2 gap-2 text-center text-xs sm:grid-cols-5">
        @php $kondisi = ['mesin', 'transmisi', 'kaki_kaki', 'eksterior', 'interior']; @endphp
        @foreach($kondisi as $k)
            <div class="border border-gray-300 p-2">
                <p class="font-bold capitalize">{{ str_replace('_', ' ', $k) }}</p>
                <p class="mt-1 font-extrabold uppercase">{{ $inspeksi->{'kondisi_' . $k} ?? '-' }}</p>
            </div>
        @endforeach
    </div>

    {{-- Fungsi Helper untuk Render Tabel Inspeksi --}}
    @php
    $renderTable = function($title, $data, $columns) {
        if ($data->isEmpty()) return '';
        $html = '<div class="mb-6"><h2 class="mb-2 border-b border-black text-sm font-extrabold uppercase">' . $title . '</h2>';
        $html .= '<table class="w-full border-collapse text-xs"><thead><tr class="bg-gray-100">';
        foreach ($columns as $colLabel) $html .= '<th class="border border-gray-300 p-2 text-left">' . $colLabel . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ($columns as $colLabel => $colName) {
                $val = $item->{$colName} ?? '-';
                $html .= '<td class="border border-gray-300 p-2">' . $val . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    };
@endphp

    {!! $renderTable('Kelistrikan & Fitur Interior', $inspeksi->kelistrikan, ['Komponen' => 'komponen', 'Status' => 'status', 'Keterangan' => 'keterangan']) !!}
    {!! $renderTable('Kondisi Interior', $inspeksi->interior, ['Komponen' => 'komponen', 'Status' => 'status', 'Keterangan' => 'keterangan']) !!}

    {{-- Tabel Kaki-Kaki (Special: Kiri & Kanan) --}}
    @if($inspeksi->kakiKaki->isNotEmpty())
    <div class="mb-6">
        <h2 class="mb-2 border-b border-black text-sm font-extrabold uppercase">Kaki-Kaki Depan & Belakang</h2>
        <table class="w-full border-collapse text-xs">
            <thead><tr class="bg-gray-100">
                <th class="border border-gray-300 p-2 text-left">Komponen</th>
                <th class="border border-gray-300 p-2 text-center">Kiri</th>
                <th class="border border-gray-300 p-2 text-center">Kanan</th>
                <th class="border border-gray-300 p-2 text-left">Catatan</th>
            </tr></thead>
            <tbody>
                @foreach($inspeksi->kakiKaki as $item)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $item->komponen }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ $item->status_kiri }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ $item->status_kanan }}</td>
                    <td class="border border-gray-300 p-2">{{ $item->catatan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {!! $renderTable('Dokumen Kendaraan', $inspeksi->dokumen, ['Dokumen' => 'dokumen', 'Status' => 'status', 'Keterangan' => 'keterangan']) !!}
    {!! $renderTable('Exterior', $inspeksi->exterior, ['Komponen' => 'komponen', 'Status' => 'status', 'Keterangan' => 'keterangan']) !!}
    {!! $renderTable('Mesin, Transmisi, Steering & Brake', $inspeksi->mesin, ['Komponen' => 'komponen', 'Status' => 'status', 'Keterangan' => 'keterangan']) !!}

    {{-- ESTIMASI PERBAIKAN --}}
    @if($inspeksi->estimasi->isNotEmpty())
    <div class="mb-6">
        <h2 class="mb-4 border-b-2 border-black text-center text-lg font-extrabold uppercase">Estimasi Perbaikan</h2>

        @php $grandTotal = 0; @endphp
        @foreach(['mesin' => 'MESIN', 'kaki_kaki' => 'KAKI-KAKI DEPAN & BELAKANG', 'kelistrikan' => 'KELISTRIKAN', 'exterior' => 'EXTERIOR'] as $key => $title)
            @php $items = $inspeksi->estimasi->where('kategori', $key); @endphp
            @if($items->isNotEmpty())
                <div class="mb-4">
                    <h3 class="mb-2 text-xs font-extrabold uppercase text-gray-700">{{ $title }}</h3>
                    <table class="w-full border-collapse text-xs">
                        <thead><tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2 w-8">No</th>
                            <th class="border border-gray-300 p-2 text-left">Nama Part / Jasa</th>
                            <th class="border border-gray-300 p-2 text-right">Harga Part</th>
                            <th class="border border-gray-300 p-2 text-right">Jasa</th>
                            <th class="border border-gray-300 p-2 text-right">Total</th>
                        </tr></thead>
                        <tbody>
                            @php $subtotal = 0; $no = 1; @endphp
                            @foreach($items as $item)
                                @php $total = ($item->harga_part * $item->qty) + $item->harga_jasa; $subtotal += $total; @endphp
                                <tr>
                                    <td class="border border-gray-300 p-2 text-center">{{ $no++ }}</td>
                                    <td class="border border-gray-300 p-2">{{ $item->nama_item }}</td>
                                    <td class="border border-gray-300 p-2 text-right">{{ $item->harga_part > 0 ? 'Rp ' . number_format($item->harga_part, 0, ',', '.') : '-' }}</td>
                                    <td class="border border-gray-300 p-2 text-right">{{ $item->harga_jasa > 0 ? 'Rp ' . number_format($item->harga_jasa, 0, ',', '.') : '-' }}</td>
                                    <td class="border border-gray-300 p-2 text-right font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50">
                                <td colspan="4" class="border border-gray-300 p-2 text-right font-extrabold">Subtotal</td>
                                <td class="border border-gray-300 p-2 text-right font-extrabold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @php $grandTotal += $subtotal; @endphp
            @endif
        @endforeach

        <div class="mt-4 flex justify-end border-t-2 border-black pt-2">
            <div class="text-right">
                <p class="text-sm font-bold text-gray-600">GRAND TOTAL ESTIMASI</p>
                <p class="text-2xl font-extrabold text-black">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Catatan Penting --}}
    @if($inspeksi->catatan_penting || $inspeksi->saran)
    <div class="mt-8 border border-gray-300 p-4 text-xs">
        <p class="mb-2 font-extrabold">Catatan Penting & Saran:</p>
        @if($inspeksi->saran)<p><span class="font-bold">Saran:</span> {{ $inspeksi->saran }}</p>@endif
    </div>
    @endif

    {{-- Tanda Tangan / Footer --}}
    <div class="mt-12 flex justify-between text-xs">
        <div class="text-center">
            <p class="mb-16">Konsumen,</p>
            <p class="border-b border-black px-8 font-bold">{{ $inspeksi->konsumen->nama_lengkap }}</p>
        </div>
        <div class="text-center">
            <p class="mb-16">Inspektur,</p>
            <p class="border-b border-black px-8 font-bold">{{ $inspeksi->user->name ?? 'Admin' }}</p>
        </div>
    </div>
</div>

@push('scripts')
<style>
    @media print {
        body { background-color: white !important; color: black !important; }
        .no-print { display: none !important; }
        #printAreaStandar {
            box-shadow: none !important;
            max-width: 100% !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
        .bg-gray-100 { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
        .bg-gray-50 { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endpush
@endsection