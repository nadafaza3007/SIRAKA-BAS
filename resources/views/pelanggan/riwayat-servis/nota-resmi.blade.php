@extends('layouts.pelanggan')
@section('title', 'Nota Resmi Bengkel - ' . $nota->no_nota)
@section('content')
@php
    $pembayaran = $nota->pembayaran;
    $totalDibayar = (int) round($pembayaran->sum('nominal_dialokasikan'));
    $sisaTagihan = max(0, (int) round($nota->total_biaya) - $totalDibayar);
    $totalTips = (int) round($pembayaran->sum('tips'));
    $totalKembalian = (int) round($pembayaran->sum('kembalian'));
    $lunas = $nota->status_pembayaran === 'lunas';
    $pekerjaanSelesai = $servis->status === 'selesai' && $servis->km_keluar !== null;
    $notaAkhir = $lunas && $pekerjaanSelesai;
    $judul = $notaAkhir ? 'NOTA PEMBAYARAN' : 'TAGIHAN SERVIS';
    $statusDok = $lunas ? 'Lunas' : ($totalDibayar > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar');
    $subtotal = (int) round($nota->subtotal);
    $diskon = (int) round($nota->diskon);
    $total = (int) round($nota->total_biaya);
    $nomor = $nota->no_nota;
    $rp = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $subSparepart = $servis->detailSpareparts->sum(fn($i) => $i->harga_jual_saat_transaksi * $i->qty);
    $barisJasa = fn($i) => max(0, $i->harga_saat_transaksi * ($i->qty ?? 1) - ($i->diskon ?? 0));
    $subJasa = $servis->detailJasas->sum(fn($i) => $barisJasa($i));
    $adaBiaya = $servis->detailSpareparts->isNotEmpty() || $servis->detailJasas->isNotEmpty();
    $bolehEdit = false;
@endphp
<div class="mx-auto max-w-5xl space-y-5">
    <div class="no-print flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('pelanggan.riwayat') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">&larr; Kembali ke Riwayat</a>
        <button type="button" onclick="window.print()" class="rounded-lg bg-orange-500 px-5 py-2 text-sm font-bold text-white">Cetak Nota A4</button>
    </div>
    @include('partials.nota-resmi')
</div>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* ================= LAYAR ================= */
:root {
    --nota-navy:#0b3954;
    --nota-garis:2px solid #0b3954;
    --nota-font:'Inter', 'Segoe UI', Arial, sans-serif;
}

/* Satu font untuk seluruh nota (layar, A4, dan thermal) */
#printArea,
#printArea *,
#printArea.nota-lunas:after {
    font-family: var(--nota-font) !important;
}

#printArea {position:relative; isolation:isolate; font-size:12px}
#printArea.nota-lunas:after {
    content:"LUNAS"; position:absolute; left:50%; top:50%;
    transform:translate(-50%,-50%) rotate(-28deg);
    font-weight:900; font-size:100px; letter-spacing:10px;
    color:#b91c1c; opacity:.10; pointer-events:none; z-index:10
}

/* Kop: logo + nama bengkel */
.nota-kop {
    display:flex; align-items:center; gap:18px;
    padding-bottom:14px; border-bottom:3px solid var(--nota-navy);
}
.nota-logo {width:80px; height:80px; object-fit:contain; flex:none}
.nota-logo-cadangan {
    width:84px; height:84px; flex:none; border-radius:50%;
    background:var(--nota-navy); color:#fff; font-weight:900; font-size:24px;
    align-items:center; justify-content:center;
}
.nota-nama {
    font-size:26px; font-weight:900; letter-spacing:.04em; line-height:1.1;
    color:var(--nota-navy); margin:0;
}
.nota-tagline {margin:5px 0 0; font-size:12px; font-weight:600; color:#475569}
.nota-alamat {margin:3px 0 0; font-size:11px; color:#64748b}

/* Judul + nomor/tanggal */
.nota-judul {
    display:flex; flex-wrap:wrap; align-items:flex-end;
    justify-content:space-between; gap:12px; padding:18px 0 14px;
}
.nota-judul-teks {
    margin:0; font-size:26px; font-weight:900; letter-spacing:.02em;
    color:var(--nota-navy); line-height:1.1;
}
.nota-meta {display:flex}
.nota-meta-sel {display:flex; flex-direction:column; gap:2px; padding:0 14px}
.nota-meta-sel + .nota-meta-sel {border-left:1px solid #94a3b8}
.nota-meta-sel span {font-size:10px; letter-spacing:.06em; color:#64748b}
.nota-meta-sel strong {font-size:13px; color:#0f172a}

/* Info pelanggan & kendaraan: "label : isi" */
.nota-info {display:grid; grid-template-columns:1fr 1fr; gap:4px 36px}
.kv-blok {margin:0}
.kv {display:grid; grid-template-columns:92px 1fr; column-gap:8px; line-height:1.7}
.kv dt {display:flex; justify-content:space-between; color:#475569}
.kv dt:after {content:":"}
.kv dd {margin:0; min-width:0; overflow-wrap:anywhere}

/* Seksi & tabel */
.nota-seksi {margin-top:18px}
.nota-seksi-judul {
    margin:0 0 6px; font-size:12px; font-weight:900;
    text-transform:uppercase; letter-spacing:.04em; color:var(--nota-navy);
}
.nota-table-wrap {width:100%; overflow-x:auto}

/* Semua garis tabel TEBAL (2px) */
.nota-table {
    width:100%; min-width:480px; border-collapse:collapse;
    border:var(--nota-garis); font-size:12px; text-align:left;
}
.nota-table th {
    background:var(--nota-navy); color:#fff; padding:7px 10px;
    font-size:11px; font-weight:700; letter-spacing:.03em;
    border:var(--nota-garis);
}
.nota-table td {
    padding:7px 10px; border:var(--nota-garis); vertical-align:top;
}
.nota-table .col-no {width:42px; text-align:center}
.nota-table tfoot td {
    background:#fff; border:var(--nota-garis);
    font-weight:800; padding:9px 10px;
}
.nota-table tfoot .ttl-label {text-align:center}
.nota-ket-kecil {margin-top:2px; font-size:10px; font-weight:400; color:#64748b}
.nota-kosong {
    margin-top:18px; padding:24px 0; text-align:center; font-size:12px;
    color:#64748b; border:2px dashed #94a3b8; border-radius:8px;
}

/* Metode pembayaran + total */
.nota-bottom {
    display:grid; grid-template-columns:1fr 1fr; gap:28px;
    margin-top:22px; break-inside:avoid; page-break-inside:avoid;
}
.nota-bottom-kiri p {margin:0; line-height:1.6}
.nota-abu {color:#64748b}
.nota-jarak {margin-top:10px !important}
.nota-total-box {width:100%; max-width:340px; margin-left:auto}
.tot {display:flex; justify-content:space-between; gap:16px; padding:3px 0}
.tot span {color:#475569}
.tot strong {text-align:right}
.tot-utama {
    margin:4px 0; padding:6px 0; font-size:13px;
    border-top:2px solid var(--nota-navy); border-bottom:2px solid var(--nota-navy);
}
.tot-utama span {color:#0f172a; font-weight:800}
.tot-catatan {margin-top:4px}

/* Tanda tangan */
.nota-ttd {
    display:flex; justify-content:space-between; gap:24px;
    margin-top:34px; break-inside:avoid; page-break-inside:avoid;
}
.ttd-kolom {min-width:180px}
.ttd-kolom p {margin:0}
.ttd-ruang {height:100px}
.ttd-kanan {text-align:right}
.ttd-nama {font-weight:800}

@media (max-width:640px) {
    .nota-info, .nota-bottom {grid-template-columns:1fr}
    .nota-total-box {max-width:none}
    .nota-kop {gap:12px}
    .nota-logo, .nota-logo-cadangan {width:64px; height:64px}
    .nota-nama {font-size:20px}
}

/* Catatan rekomendasi (panel data akhir) */
.catatan-rekomendasi {
    display:flex; flex-direction:column; align-items:stretch;
    justify-content:flex-start; gap:4px;
}
.catatan-rekomendasi__judul,
.catatan-rekomendasi__isi {display:block; margin:0; padding:0;}
.catatan-rekomendasi__judul {
    font-size:11px; font-weight:700; line-height:16px; color:#a3a3a3;
}
.catatan-rekomendasi__isi {
    font-size:12px; line-height:18px; color:#e5e5e5;
    white-space:pre-line; overflow-wrap:anywhere;
}

/* ================= CETAK ================= */
@media print {
    @page {margin:10mm}
    body * {visibility:hidden !important}
    #printArea,#printArea * {
        visibility:visible !important;
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
    #printArea {
        position:absolute !important; top:0 !important; left:0 !important;
        width:100% !important; max-width:none !important;
        padding:0 !important; margin:0 !important;
        border:0 !important; border-radius:0 !important;
        background:white !important; box-shadow:none !important;
        font-size:11px;
    }
    #printArea .no-print,
    #printArea .no-print * {display:none !important; visibility:hidden !important}

    #printArea .nota-info {grid-template-columns:1fr 1fr !important}
    #printArea .nota-bottom {grid-template-columns:1fr 1fr !important}
    #printArea .nota-table-wrap {overflow:visible !important}
    #printArea .nota-table {min-width:0 !important}
    #printArea .nota-table thead {display:table-header-group}
    #printArea .nota-table tr {break-inside:avoid; page-break-inside:avoid}
    #printArea .nota-table th,
    #printArea .nota-table td {padding:5px 8px; overflow-wrap:anywhere}
    #printArea .nota-judul {padding:14px 0 10px}
    #printArea .nota-seksi {margin-top:14px}
    #printArea .nota-bottom {margin-top:16px}
    #printArea .nota-ttd {margin-top:22px}
    #printArea .ttd-ruang {height:28mm}

    /* ===== Thermal 72mm =====
       Semua selector diawali "body.print-thermal #printArea" agar
       spesifisitasnya setara dengan aturan A4 di atas (selector ber-ID)
       dan menang karena urutannya di bawah. */
    body.print-thermal #printArea {
        width:72mm !important; max-width:72mm !important;
        font-size:10px !important; line-height:1.4 !important;
    }
    body.print-thermal #printArea * {
        color:#000 !important; background:transparent !important;
    }
    body.print-thermal #printArea.nota-lunas:after {content:none !important}

    /* Kop: logo + nama bengkel, rata tengah */
    body.print-thermal #printArea .nota-kop {
        display:flex !important; flex-direction:column !important;
        align-items:center !important; gap:3px !important; text-align:center !important;
        padding:0 0 6px !important; border-bottom:1px dashed #555 !important;
    }
    body.print-thermal #printArea .nota-logo,
    body.print-thermal #printArea .nota-logo-cadangan {
        width:16mm !important; height:16mm !important;
    }
    body.print-thermal #printArea .nota-logo {
        display:block !important; margin:0 auto !important;
        filter:grayscale(1) contrast(1.4);
    }
    body.print-thermal #printArea .nota-logo-cadangan {
        border:1px solid #000 !important; font-size:12px !important;
    }
    body.print-thermal #printArea .nota-nama {font-size:13px !important; letter-spacing:0 !important}
    body.print-thermal #printArea .nota-tagline,
    body.print-thermal #printArea .nota-alamat {
        font-size:8px !important; line-height:1.3 !important; margin:2px 0 0 !important;
    }

    /* Judul + nomor/tanggal */
    body.print-thermal #printArea .nota-judul {
        display:block !important; padding:8px 0 6px !important; text-align:center !important;
        border-bottom:1px dashed #555 !important;
    }
    body.print-thermal #printArea .nota-judul-teks {
        font-size:12px !important; letter-spacing:.06em !important;
    }
    body.print-thermal #printArea .nota-meta {display:block !important; margin-top:6px !important}
    body.print-thermal #printArea .nota-meta-sel {
        flex-direction:row !important; justify-content:space-between !important;
        align-items:baseline !important; gap:6px !important;
        padding:1px 0 !important; border:0 !important;
    }
    body.print-thermal #printArea .nota-meta-sel span {
        font-size:9px !important; letter-spacing:0 !important; white-space:nowrap !important;
    }
    body.print-thermal #printArea .nota-meta-sel strong {
        font-size:10px !important; text-align:right !important; overflow-wrap:anywhere !important;
    }

    /* Info pelanggan & kendaraan: 1 kolom, ditumpuk */
    body.print-thermal #printArea .nota-info {
        display:block !important; padding:0 !important;
    }
    body.print-thermal #printArea .kv-blok {
        margin:0 !important; padding:6px 0 !important;
        border-bottom:1px dashed #aaa !important;
    }
    body.print-thermal #printArea .kv {
        grid-template-columns:20mm 1fr !important; column-gap:4px !important;
        line-height:1.45 !important;
    }
    body.print-thermal #printArea .kv dd {text-align:left !important}

    /* Tabel jadi daftar baris */
    body.print-thermal #printArea .nota-seksi {margin-top:8px !important}
    body.print-thermal #printArea .nota-seksi-judul {
        font-size:10px !important; margin:0 0 4px !important;
        padding-bottom:3px !important; border-bottom:1px solid #000 !important;
    }
    body.print-thermal #printArea .nota-table-wrap {overflow:visible !important}
    body.print-thermal #printArea .nota-table {border:0 !important; min-width:0 !important}
    body.print-thermal #printArea .nota-table thead,
    body.print-thermal #printArea .nota-table .col-no {display:none !important}
    body.print-thermal #printArea .nota-table,
    body.print-thermal #printArea .nota-table tbody,
    body.print-thermal #printArea .nota-table tfoot {
        display:block !important; width:100% !important;
    }
    body.print-thermal #printArea .nota-table tbody tr {
        display:block !important; padding:5px 0 !important;
        border-bottom:1px dashed #aaa !important;
    }
    body.print-thermal #printArea .nota-table td {
        display:flex !important; width:100% !important;
        justify-content:space-between !important; gap:8px !important;
        padding:1px 0 !important; border:0 !important;
        font-size:10px !important; text-align:right !important;
    }
    body.print-thermal #printArea .nota-table td.col-desk {
        display:block !important; font-weight:700 !important; text-align:left !important;
        padding-bottom:2px !important;
    }
    body.print-thermal #printArea .nota-table td[data-label]:before {
        content:attr(data-label); text-align:left !important;
    }
    body.print-thermal #printArea .nota-ket-kecil {font-size:9px !important}
    body.print-thermal #printArea .nota-table tfoot tr {
        display:flex !important; justify-content:space-between !important;
        padding:5px 0 0 !important; border:0 !important;
    }
    body.print-thermal #printArea .nota-table tfoot td {
        width:auto !important; font-weight:800 !important; padding:0 !important;
    }

    /* Pembayaran + total */
    body.print-thermal #printArea .nota-bottom {
        display:block !important; margin-top:10px !important;
    }
    body.print-thermal #printArea .nota-bottom-kiri {margin-bottom:8px !important}
    body.print-thermal #printArea .nota-total-box {
        max-width:100% !important; margin:8px 0 0 !important;
        padding:6px 0 0 !important; border-top:1px dashed #555 !important;
    }
    body.print-thermal #printArea .tot {padding:2px 0 !important; gap:8px !important}
    body.print-thermal #printArea .tot-utama {
        margin:3px 0 !important; padding:4px 0 !important; font-size:11px !important;
        border-top:1px dashed #555 !important; border-bottom:1px dashed #555 !important;
    }
    body.print-thermal #printArea .nota-ttd {display:none !important}
}
</style>
@endsection
