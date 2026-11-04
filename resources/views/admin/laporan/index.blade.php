@extends('layouts.admin')

@section('title', 'Laporan Pemasukan - SIRAKA')

@section('content')

@php

    $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $tanggal = fn ($d) => \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y');

    $jumlahNota = $pembayaranHalaman->total();

    $maksUnitGrafik = max(1, (int) $sparepartTerlaris->max('unit_terjual'));

    $notaBertips = $pembayaran->filter(fn ($p) => (float) $p->tips > 0);

    $sumTop = (object) [

        'unit' => $sparepartTerlaris->sum('unit_terjual'),

        'omzet' => $sparepartTerlaris->sum('omzet'),

        'modal' => $sparepartTerlaris->sum('modal'),

        'laba' => $sparepartTerlaris->sum('laba_kotor'),

    ];

    $periode = [

        'Bulan ini' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],

        'Bulan lalu' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],

        '7 hari terakhir' => [now()->subDays(6)->toDateString(), now()->toDateString()],

    ];

    $grafikSparepartData = $sparepartTerlaris
    ->map(function ($sp) {
        return [
            'nama' => $sp->nama_barang,
            'unit' => (int) $sp->unit_terjual,
            'omzet' => (float) $sp->omzet,
        ];
    })
    ->values()
    ->all();

@endphp

<div class="mx-auto max-w-7xl space-y-5 pb-10">

    {{-- Judul dan tindakan --}}

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h1 class="text-xl font-bold text-white">Laporan Pemasukan</h1>

            <p class="mt-1 text-xs text-neutral-400">Periode {{ $tanggal($startDate) }} sampai {{ $tanggal($endDate) }}</p>

        </div>

        <a href="{{ route('admin.laporan.cetak-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"

           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white hover:bg-brand-600">

            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF

        </a>

    </div>

    {{-- Filter --}}

    <form method="GET" action="{{ route('admin.laporan.index') }}" class="rounded-xl border border-neutral-800 bg-ink-900 p-4">

        <div class="mb-3 flex flex-wrap gap-2">

            @foreach($periode as $label => [$awal, $akhir])

                <a href="{{ route('admin.laporan.index', ['start_date' => $awal, 'end_date' => $akhir]) }}"

                   class="rounded-md border px-3 py-1.5 text-xs {{ $startDate === $awal && $endDate === $akhir ? 'border-brand-500 text-brand-400' : 'border-neutral-700 text-neutral-300 hover:border-neutral-500' }}">{{ $label }}</a>

            @endforeach

        </div>

        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">

            <label class="block text-xs text-neutral-300">Tanggal mulai

                <input type="date" name="start_date" value="{{ $startDate }}" required class="mt-1 block w-full rounded-lg border border-neutral-700 bg-neutral-900 p-2.5 text-sm text-white">

            </label>

            <label class="block text-xs text-neutral-300">Tanggal akhir

                <input type="date" name="end_date" value="{{ $endDate }}" min="{{ $startDate }}" required class="mt-1 block w-full rounded-lg border border-neutral-700 bg-neutral-900 p-2.5 text-sm text-white">

            </label>

            <button type="submit" class="rounded-lg border border-neutral-600 bg-neutral-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-neutral-700">Tampilkan</button>

        </div>

    </form>

    {{-- Keterangan periode --}}

    <div class="rounded-lg border border-neutral-800 bg-ink-900 px-4 py-3 text-xs leading-5 text-neutral-400">

        Laporan memuat semua penerimaan pembayaran, termasuk cicilan yang belum lunas, selama periode terpilih. Tanggal transaksi mengacu pada tanggal pembayaran, bukan tanggal pekerjaan servis.

    </div>

    {{-- Ringkasan utama --}}

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" aria-label="Ringkasan periode">

        @foreach([

            ['Total pemasukan', $totalPemasukan, 'text-emerald-400'],

            ['Penerimaan servis', $pendapatanKotor, 'text-white'],

            ['Tips pelanggan', $totalTips, 'text-amber-400']

        ] as [$label, $nilai, $warna])

            <div class="rounded-xl border border-neutral-800 bg-ink-900 p-4">

                <p class="text-xs text-neutral-400">{{ $label }}</p>

                <p class="mt-2 break-words text-xl font-bold {{ $warna }}">{{ $rupiah($nilai) }}</p>

            </div>

        @endforeach

    </section>

    {{-- Grafik ringkasan kas per tanggal + metode pembayaran --}}

    <section class="grid gap-4 lg:grid-cols-2">

        <div class="min-w-0 rounded-xl border border-neutral-800 bg-ink-900 p-3 sm:p-5 lg:col-span-2">

            <h2 class="text-sm font-bold text-white">Tren Pemasukan Bengkel</h2>

            <p class="mb-4 mt-1 text-xs text-neutral-400">Berdasarkan tanggal uang diterima, termasuk setiap cicilan.</p>

            <div class="relative h-64 min-w-0 sm:h-80"><canvas id="sirakaTrendLaporan"></canvas></div>

        </div>

        <div class="min-w-0 rounded-xl border border-neutral-800 bg-ink-900 p-3 sm:p-5">

            <h2 class="text-sm font-bold text-white">

                Jumlah Servis per Bulan

            </h2>

            <p class="mb-4 mt-1 text-xs text-neutral-400">

                Jumlah pekerjaan servis berdasarkan tanggal masuk

                kendaraan pada periode laporan.

            </p>

            <div class="relative h-64 w-full sm:h-72">

                <canvas id="sirakaServisBulanan"></canvas>

            </div>

        </div>

        <div class="min-w-0 rounded-xl border border-neutral-800 bg-ink-900 p-3 sm:p-5">

            <h2 class="text-sm font-bold text-white">Komposisi Metode Pembayaran</h2>

            <p class="mb-4 mt-1 text-xs text-neutral-400">Proporsi kas diterima setelah kembalian.</p>

            <div class="relative h-64 min-w-0 sm:h-72"><canvas id="sirakaMetodeLaporan"></canvas></div>

        </div>

    </section>

    <section class="grid gap-3 sm:grid-cols-2">

        <div class="rounded-xl border border-neutral-800 bg-ink-900 p-4">

            <p class="text-xs text-neutral-400">Piutang pada Akhir Periode</p>

            <p class="mt-2 text-xl font-bold text-amber-400">{{ $rupiah($totalPiutang) }}</p>

        </div>

        <div class="rounded-xl border border-neutral-800 bg-ink-900 p-4">

            <p class="text-xs text-neutral-400">Nota dengan Sisa Tagihan</p>

            <p class="mt-2 text-xl font-bold text-white">{{ number_format($jumlahNotaPiutang,0,',','.') }} nota</p>

        </div>

        <p class="text-xs text-neutral-500 sm:col-span-2">Piutang dihitung dari tagihan yang terbit sampai akhir periode dikurangi pembayaran sampai tanggal tersebut. Revisi tagihan di masa depan memerlukan riwayat nominal.</p>

    </section>

    {{-- Rekap keuangan dalam tabel yang lazim dipakai laporan --}}

    <section class="overflow-hidden rounded-xl border border-neutral-800 bg-ink-900">

        <div class="border-b border-neutral-800 px-4 py-3"><h2 class="text-sm font-bold text-white">A. Rekapitulasi Keuangan</h2></div>

        <div class="border-b border-neutral-800 px-4 py-3 text-xs leading-5 text-neutral-400">

            <details class="group">

                <summary class="flex cursor-pointer items-center justify-between gap-3 font-semibold text-neutral-200">

                    <span><i class="fa-solid fa-circle-info mr-2 text-brand-400"></i>Lihat dasar perhitungan keuangan</span>

                    <i class="fa-solid fa-chevron-down text-neutral-500 transition group-open:rotate-180"></i>

                </summary>

                <ol class="mt-3 list-decimal space-y-2 pl-5 text-neutral-400">

                    <li><strong class="text-neutral-200">Penerimaan servis:</strong> bagian pembayaran yang dialokasikan ke tagihan jasa dan suku cadang pada periode ini.</li>

                    <li><strong class="text-neutral-200">Tip pelanggan:</strong> uang tambahan yang diberikan pelanggan dan tidak dikembalikan.</li>

                    <li><strong class="text-neutral-200">Total pemasukan:</strong> pendapatan servis ditambah tip pelanggan, sesuai uang diterima dikurangi kembalian.</li>

                </ol>

            </details>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[480px] text-sm">

                <thead class="bg-neutral-900 text-xs text-neutral-400"><tr><th class="px-4 py-3 text-left">Uraian</th><th class="px-4 py-3 text-right">Jumlah</th></tr></thead>

                <tbody class="divide-y divide-neutral-800 text-neutral-200">

                    <tr><td class="px-4 py-2.5">Penerimaan pelunasan/cicilan jasa dan sparepart</td><td class="px-4 py-2.5 text-right">{{ $rupiah($pendapatanKotor) }}</td></tr>

                    <tr><td class="px-4 py-2.5">Tip pelanggan</td><td class="px-4 py-2.5 text-right">{{ $rupiah($totalTips) }}</td></tr>

                    <tr class="bg-neutral-800/40 font-semibold"><td class="px-4 py-3">Total pemasukan</td><td class="px-4 py-3 text-right text-emerald-400">{{ $rupiah($totalPemasukan) }}</td></tr>

                    <tr><td class="px-4 py-2.5">Modal sparepart pada nota dengan pembayaran periode ini</td><td class="px-4 py-2.5 text-right">{{ $rupiah($totalModalSparepart) }}</td></tr>

                </tbody>

            </table>

        </div>

        <div class="border-t border-neutral-800 px-4 py-3 text-xs leading-5 text-neutral-400">

            <p>Uang diterima: <strong class="text-neutral-200">{{ $rupiah($totalUangDiterima) }}</strong> · Kembalian: <strong class="text-neutral-200">{{ $rupiah($totalKembalian) }}</strong>.</p>

        </div>

    </section>

    {{-- Pembayaran --}}

    <section id="rincian-pembayaran" class="scroll-mt-5 overflow-hidden rounded-xl border border-neutral-800 bg-ink-900">

        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-800 px-4 py-3">

            <h2 class="text-sm font-bold text-white">B. Rincian Pembayaran</h2>

            <span class="text-xs text-neutral-400">{{ $jumlahNota }} pembayaran tercatat</span>

        </div>

        <p class="border-b border-neutral-800 px-4 py-3 text-xs leading-5 text-neutral-400"><strong class="text-neutral-200">Keterangan:</strong> Pemasukan = uang diterima − kembalian (termasuk tip).</p>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-xs">

                <thead class="bg-neutral-900 text-neutral-400"><tr>

                    <th class="px-3 py-3 text-left">Tanggal</th><th class="px-3 py-3 text-left">No. nota</th><th class="px-3 py-3 text-left">Pelanggan / Kendaraan</th><th class="px-3 py-3 text-left">Petugas Penerima</th><th class="px-3 py-3 text-left">Metode</th>

                    <th class="px-3 py-3 text-right">Untuk Tagihan</th><th class="px-3 py-3 text-right">Diterima</th><th class="px-3 py-3 text-right">Kembalian</th><th class="px-3 py-3 text-right">Tip</th><th class="px-3 py-3 text-right">Pemasukan</th>

                </tr></thead>

                <tbody class="divide-y divide-neutral-800 text-neutral-200">

                    @forelse($pembayaranHalaman as $p)

                    <tr>

                        <td class="whitespace-nowrap px-3 py-3">{{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y H:i') }}</td>

                        <td class="px-3 py-3 font-medium">{{ $p->no_nota }}</td>

                        <td class="px-3 py-3">{{ $p->nama_lengkap ?? '-' }}<div class="text-neutral-500">{{ $p->plat_nomor ?? '-' }}</div></td>

                        <td class="px-3 py-3">{{ $p->petugas_penerima ?? 'Petugas tidak tersedia' }}</td>

                        <td class="px-3 py-3">{{ strtoupper($p->metode_pembayaran ?? '-') }}</td>

                        <td class="whitespace-nowrap px-3 py-3 text-right">{{ $rupiah($p->nominal_dialokasikan) }}</td>

                        <td class="whitespace-nowrap px-3 py-3 text-right">{{ $rupiah($p->jumlah_diterima) }}</td>

                        <td class="whitespace-nowrap px-3 py-3 text-right">{{ $rupiah($p->kembalian) }}</td>

                        <td class="whitespace-nowrap px-3 py-3 text-right text-amber-400">{{ $rupiah($p->tips) }}</td>

                        <td class="whitespace-nowrap px-3 py-3 text-right font-semibold">{{ $rupiah($p->jumlah_diterima - $p->kembalian) }}</td>

                    </tr>

                    @empty

                    <tr><td colspan="10" class="px-4 py-8 text-center text-neutral-500">Belum ada pembayaran pada periode ini.</td></tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($pembayaranHalaman->hasPages())

            <nav aria-label="Halaman rincian pembayaran" class="flex flex-col gap-3 border-t border-neutral-800 px-4 py-3 text-xs text-neutral-300 sm:flex-row sm:items-center sm:justify-between">

                <p>Menampilkan {{ $pembayaranHalaman->firstItem() }}–{{ $pembayaranHalaman->lastItem() }} dari {{ $pembayaranHalaman->total() }} transaksi</p>

                <div class="flex flex-wrap items-center gap-1.5">

                    @if($pembayaranHalaman->onFirstPage())

                        <span class="rounded-md border border-neutral-800 px-3 py-2 text-neutral-600">Sebelumnya</span>

                    @else

                        <a href="{{ $pembayaranHalaman->previousPageUrl() }}#rincian-pembayaran" class="rounded-md border border-neutral-700 px-3 py-2 hover:border-brand-500 hover:text-white">Sebelumnya</a>

                    @endif

                    @foreach($pembayaranHalaman->getUrlRange(max(1, $pembayaranHalaman->currentPage() - 2), min($pembayaranHalaman->lastPage(), $pembayaranHalaman->currentPage() + 2)) as $nomor => $url)

                        <a href="{{ $url }}#rincian-pembayaran" aria-label="Halaman {{ $nomor }}" @if($nomor === $pembayaranHalaman->currentPage()) aria-current="page" @endif class="rounded-md border px-3 py-2 {{ $nomor === $pembayaranHalaman->currentPage() ? 'border-brand-500 bg-brand-500/10 font-bold text-brand-400' : 'border-neutral-700 text-neutral-300 hover:border-brand-500' }}">{{ $nomor }}</a>

                    @endforeach

                    @if($pembayaranHalaman->hasMorePages())

                        <a href="{{ $pembayaranHalaman->nextPageUrl() }}#rincian-pembayaran" class="rounded-md border border-neutral-700 px-3 py-2 hover:border-brand-500 hover:text-white">Berikutnya</a>

                    @else

                        <span class="rounded-md border border-neutral-800 px-3 py-2 text-neutral-600">Berikutnya</span>

                    @endif

                </div>

            </nav>

        @endif

    </section>

    {{-- Tip tetap ditampilkan, tetapi tidak jadi dashboard kedua --}}

    <section class="overflow-hidden rounded-xl border border-neutral-800 bg-ink-900">

        <div class="border-b border-neutral-800 px-4 py-3"><h2 class="text-sm font-bold text-white">C. Rincian Tip</h2></div>

        <p class="px-4 pt-3 text-xs leading-5 text-neutral-400">Bagian ini mencatat uang tambahan yang diberikan pelanggan di luar jumlah tagihan dan tidak dikembalikan.</p>

        <div class="flex flex-wrap gap-x-8 gap-y-2 px-4 py-3 text-xs text-neutral-300">

            <span>Total tip: <strong class="text-amber-400">{{ $rupiah($totalTips) }}</strong></span>

            <span>Pembayaran dengan tip: <strong class="text-white">{{ $notaBertips->count() }}</strong></span>

        </div>

        @if($notaBertips->isNotEmpty())

            <div class="overflow-x-auto border-t border-neutral-800"><table class="w-full min-w-[550px] text-xs">

                <thead class="bg-neutral-900 text-neutral-400"><tr><th class="px-4 py-2 text-left">Tanggal</th><th class="px-4 py-2 text-left">Nota</th><th class="px-4 py-2 text-left">Pelanggan</th><th class="px-4 py-2 text-right">Tip</th></tr></thead>

                <tbody class="divide-y divide-neutral-800 text-neutral-200">

                @foreach($notaBertips as $p)<tr><td class="px-4 py-2">{{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') }}</td><td class="px-4 py-2">{{ $p->no_nota }}</td><td class="px-4 py-2">{{ $p->nama_lengkap ?? '-' }}</td><td class="px-4 py-2 text-right text-amber-400">{{ $rupiah($p->tips) }}</td></tr>@endforeach

                </tbody>

            </table></div>

        @else

            <p class="border-t border-neutral-800 px-4 py-4 text-xs text-neutral-500">Belum ada tip pada periode ini.</p>

        @endif

    </section>

    {{-- Analisis sparepart tanpa grafik berulang --}}

    <section class="overflow-hidden rounded-xl border border-neutral-800 bg-ink-900">

        <div class="border-b border-neutral-800 px-4 py-3">

            <h2 class="text-sm font-bold text-white">D. Analisis Penjualan Sparepart — 10 Terlaris</h2>

            <p class="mt-1 text-xs text-neutral-500">Berdasarkan sparepart dalam tagihan yang diterbitkan pada periode laporan, terlepas dari status pembayarannya. Jenis Stok/Pre-Order mengikuti kategori Master Sparepart saat laporan dibuat.</p>

        </div>

        <div class="border-b border-neutral-800 px-4 py-3 text-xs leading-5 text-neutral-400">

            <details class="group">

                <summary class="flex cursor-pointer items-center justify-between gap-3 font-semibold text-neutral-200">

                    <span><i class="fa-solid fa-circle-info mr-2 text-brand-400"></i>Lihat dasar perhitungan penjualan</span>

                    <i class="fa-solid fa-chevron-down text-neutral-500 transition group-open:rotate-180"></i>

                </summary>

                <ol class="mt-3 list-decimal space-y-2 pl-5 text-neutral-400">

                    <li><strong class="text-neutral-200">Unit terjual:</strong> jumlah seluruh unit suku cadang yang terjual pada periode ini.</li>

                    <li><strong class="text-neutral-200">Jumlah tagihan:</strong> banyaknya tagihan terbit yang mencantumkan suku cadang tersebut.</li>

                    <li><strong class="text-neutral-200">Nilai penjualan:</strong> nilai jual suku cadang setelah memperhitungkan bagian diskon pada nota.</li>

                    <li><strong class="text-neutral-200">Modal:</strong> harga modal saat transaksi dikalikan jumlah unit.</li>

                    <li><strong class="text-neutral-200">Laba kotor:</strong> nilai penjualan dikurangi modal suku cadang, sebelum biaya operasional.</li>

                </ol>

            </details>

        </div>

        @if(!empty($kalimatSparepart))

            <p class="border-b border-neutral-800 px-4 py-3 text-xs leading-5 text-neutral-300">{{ $kalimatSparepart }}</p>

        @endif

        <div class="border-b border-neutral-800 px-4 py-5 sm:px-5">

            <h3 class="mb-1 text-sm font-semibold text-white">Grafik Penjualan Sparepart</h3>

            <p class="mb-4 text-xs text-neutral-500">Batang lebih panjang berarti jumlah terjual lebih banyak. Arahkan kursor untuk melihat nilai penjualan.</p>

            @if($sparepartTerlaris->isNotEmpty())

                <div class="relative w-full" style="height: {{ max(200, $sparepartTerlaris->count() * 42 + 55) }}px;">

                    <canvas id="sirakaSparepartLaporan"></canvas>

                </div>

                <p class="mt-3 text-xs text-neutral-500">

                    Diagram menunjukkan 10 sparepart terlaris

                    berdasarkan jumlah unit terjual.

                    Arahkan kursor ke batang untuk melihat nilai penjualan.

                </p>

            @else

                <p class="rounded-lg border border-dashed border-neutral-700 p-5 text-center text-xs text-neutral-500">

                    Belum ada penjualan sparepart dengan tagihan terbit pada periode ini.

                </p>

            @endif

        </div>

        <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-xs">

            <thead class="bg-neutral-900 text-neutral-400"><tr><th class="px-3 py-3 text-left">No.</th><th class="px-3 py-3 text-left">Nama Sparepart</th><th class="px-3 py-3 text-left">Jenis</th><th class="px-3 py-3 text-right">Jumlah terjual</th><th class="px-3 py-3 text-right">Jumlah tagihan</th><th class="px-3 py-3 text-right">Nilai penjualan</th><th class="px-3 py-3 text-right">Modal</th><th class="px-3 py-3 text-right">Laba kotor</th></tr></thead>

            <tbody class="divide-y divide-neutral-800 text-neutral-200">

                @forelse($sparepartTerlaris as $i => $sp)

                    <tr><td class="px-3 py-3">{{ $i + 1 }}</td><td class="px-3 py-3 font-medium">{{ $sp->nama_barang }} @if($sp->kode_barang)<span class="block font-normal text-neutral-500">{{ $sp->kode_barang }}</span>@endif</td><td class="px-3 py-3">{{ $sp->jenis_label }}</td><td class="px-3 py-3 text-right">{{ number_format($sp->unit_terjual,0,',','.') }}</td><td class="px-3 py-3 text-right">{{ $sp->frekuensi }}</td><td class="px-3 py-3 text-right">{{ $rupiah($sp->omzet) }}</td><td class="px-3 py-3 text-right">{{ $rupiah($sp->modal) }}</td><td class="px-3 py-3 text-right">{{ $rupiah($sp->laba_kotor) }}</td></tr>

                @empty

                    <tr><td colspan="8" class="px-4 py-8 text-center text-neutral-500">Belum ada penjualan sparepart pada periode ini.</td></tr>

                @endforelse

            </tbody>

            @if($sparepartTerlaris->isNotEmpty())

            <tfoot class="bg-neutral-800/40 text-white"><tr class="font-semibold"><td colspan="3" class="px-3 py-3">10 teratas</td><td class="px-3 py-3 text-right">{{ number_format($sumTop->unit,0,',','.') }}</td><td></td><td class="px-3 py-3 text-right">{{ $rupiah($sumTop->omzet) }}</td><td class="px-3 py-3 text-right">{{ $rupiah($sumTop->modal) }}</td><td class="px-3 py-3 text-right">{{ $rupiah($sumTop->laba) }}</td></tr>

            <tr><td colspan="3" class="px-3 py-3">Seluruh sparepart periode ini</td><td class="px-3 py-3 text-right">{{ number_format($ringkasanSparepart->unit_terjual,0,',','.') }}</td><td></td><td class="px-3 py-3 text-right">{{ $rupiah($ringkasanSparepart->omzet) }}</td><td class="px-3 py-3 text-right">{{ $rupiah($ringkasanSparepart->modal) }}</td><td class="px-3 py-3 text-right">{{ $rupiah($ringkasanSparepart->laba_kotor) }}</td></tr></tfoot>

            @endif

        </table></div>

    </section>

    <section class="overflow-hidden rounded-xl border border-neutral-800 bg-ink-900">

        <div class="border-b border-neutral-800 px-4 py-3"><h2 class="text-sm font-bold text-white">E. Sparepart pada Servis yang Belum Lunas</h2><p class="mt-1 text-xs leading-5 text-neutral-500">Menampilkan sparepart pada servis dalam periode yang dipilih dan saat ini belum lunas, termasuk servis yang tagihannya belum diterbitkan. Nilai yang ditampilkan merupakan nilai sparepart, bukan total piutang pelanggan.</p></div>

        <p class="px-4 py-3 text-xs text-neutral-300">{{ number_format($ringkasanBelumLunas->unit,0,',','.') }} jumlah terpakai dari {{ $ringkasanBelumLunas->jumlah_servis }} servis · nilai tercatat {{ $rupiah($ringkasanBelumLunas->nilai) }}</p>

        @if($sparepartBelumLunas->isNotEmpty())

        <div class="overflow-x-auto border-t border-neutral-800"><table class="w-full min-w-[430px] text-xs"><thead class="bg-neutral-900 text-neutral-400"><tr><th class="px-4 py-2 text-left">Sparepart</th><th class="px-4 py-2 text-left">Jenis</th><th class="px-4 py-2 text-right">Jumlah terpakai</th><th class="px-4 py-2 text-right">Nilai tercatat</th></tr></thead><tbody class="divide-y divide-neutral-800 text-neutral-200">@foreach($sparepartBelumLunas as $sp)<tr><td class="px-4 py-2">{{ $sp->nama_barang }}</td><td class="px-4 py-2">{{ $sp->jenis_label }}</td><td class="px-4 py-2 text-right">{{ $sp->unit }}</td><td class="px-4 py-2 text-right">{{ $rupiah($sp->nilai) }}</td></tr>@endforeach</tbody></table></div>

        @endif

    </section>

</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const rupiah = nilai => 'Rp ' + Number(nilai || 0).toLocaleString('id-ID');
    const warnaTeks = '#a3a3a3';
    const warnaGrid = 'rgba(163,163,163,0.10)';
    const uangMasuk = @json($grafikPemasukan);
    const labelTanggal = @json($grafikLabels);
    const jumlahServis = @json($grafikServisJumlah);
    const bulanServis = @json($grafikServisLabels);
    const komposisi = @json($grafikMetode);
    const spareparts = @json($grafikSparepartData);

    const tooltipDasar = {
        backgroundColor: '#262626',
        titleColor: '#fff',
        bodyColor: '#e5e5e5',
        padding: 12,
        cornerRadius: 9,
        displayColors: false,
    };
    const sumbuAngka = {
        beginAtZero: true,
        grid: { color: warnaGrid, drawTicks: false },
        border: { display: false },
        ticks: { color: warnaTeks, padding: 10, maxTicksLimit: 6, callback: v => Number(v).toLocaleString('id-ID') }
    };
    const sumbuLabel = {
        grid: { display: false },
        border: { display: false },
        ticks: { color: warnaTeks, maxRotation: 0, autoSkip: true, maxTicksLimit: 8, padding: 10 }
    };

    const tren = document.getElementById('sirakaTrendLaporan');
    if (tren) {
        const ctx = tren.getContext('2d');
        const gradien = ctx.createLinearGradient(0, 0, 0, 310);
        gradien.addColorStop(0, 'rgba(52,211,153,.27)');
        gradien.addColorStop(1, 'rgba(52,211,153,0)');
        new Chart(ctx, {
            type: 'line',
            data: { labels: labelTanggal, datasets: [{
                label: 'Pemasukan', data: uangMasuk, borderColor: '#34d399',
                backgroundColor: gradien, borderWidth: 2.5, fill: true,
                tension: 0.36, pointRadius: labelTanggal.length > 35 ? 0 : 2.5,
                pointHoverRadius: 6, pointBackgroundColor: '#34d399',
                pointBorderColor: '#171717', pointBorderWidth: 2,
            }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: {
                    ...tooltipDasar,
                    callbacks: { label: c => 'Pemasukan: ' + rupiah(c.parsed.y) }
                } },
                scales: { x: sumbuLabel, y: { ...sumbuAngka, ticks: {
                    ...sumbuAngka.ticks, callback: v => v >= 1000000 ? (v / 1000000).toLocaleString('id-ID') + ' jt' : v >= 1000 ? (v / 1000).toLocaleString('id-ID') + ' rb' : v
                } } }
            }
        });
    }

    const servis = document.getElementById('sirakaServisBulanan');
    if (servis) new Chart(servis, {
        type: 'bar',
        data: { labels: bulanServis, datasets: [{
            label: 'Jumlah Servis', data: jumlahServis,
            backgroundColor: 'rgba(249,115,22,.75)', hoverBackgroundColor: '#fb923c',
            borderRadius: 7, borderSkipped: false, maxBarThickness: 46,
            categoryPercentage: .7, barPercentage: .8,
        }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                ...tooltipDasar, callbacks: { label: c => 'Jumlah servis: ' + c.parsed.y.toLocaleString('id-ID') }
            } },
            scales: { x: sumbuLabel, y: { ...sumbuAngka, ticks: { ...sumbuAngka.ticks, precision: 0, stepSize: 1 } } }
        }
    });

    const donut = document.getElementById('sirakaMetodeLaporan');
    if (donut) {
        const namaMetode = Object.keys(komposisi).map(x => x.toUpperCase());
        const angkaMetode = Object.values(komposisi).map(Number);
        const punyaData = angkaMetode.some(v => v > 0);
        new Chart(donut, {
            type: 'doughnut',
            data: {
                labels: punyaData ? namaMetode : ['Belum ada pembayaran'],
                datasets: [{
                    data: punyaData ? angkaMetode : [1],
                    backgroundColor: punyaData ? ['#f97316','#38bdf8','#34d399','#a78bfa'] : ['#404040'],
                    borderColor: '#171717', borderWidth: 4, hoverOffset: 6, borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '73%',
                plugins: {
                    legend: { position: 'bottom', labels: {
                        color: '#d4d4d4', usePointStyle: true, pointStyle: 'circle',
                        padding: 15, boxWidth: 9, font: { size: 11 }
                    } },
                    tooltip: { ...tooltipDasar, callbacks: {
                        label: c => punyaData ? c.label + ': ' + rupiah(c.parsed) : 'Belum ada pembayaran'
                    } }
                }
            }
        });
    }

    const sparepart = document.getElementById('sirakaSparepartLaporan');
    if (sparepart && spareparts.length) {
        const lebarLabel = window.innerWidth < 640 ? 18 : 30;
        const pendekkan = (teks, maks) => teks.length > maks ? teks.slice(0, maks - 1) + '…' : teks;
        new Chart(sparepart, {
            type: 'bar',
            data: {
                labels: spareparts.map(sp => pendekkan(String(sp.nama), lebarLabel)),
                datasets: [{
                    label: 'Jumlah Terjual', data: spareparts.map(sp => sp.unit),
                    backgroundColor: spareparts.map((_, i) => i === 0 ? '#fb923c' : 'rgba(249,115,22,.65)'),
                    hoverBackgroundColor: '#fdba74', borderRadius: 6,
                    borderSkipped: false, maxBarThickness: 24,
                    categoryPercentage: .75, barPercentage: .85,
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                layout: { padding: { right: 24 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tooltipDasar, callbacks: {
                        title: c => spareparts[c[0].dataIndex].nama,
                        label: c => [
                            'Jumlah terjual: ' + spareparts[c.dataIndex].unit.toLocaleString('id-ID'),
                            'Nilai penjualan: ' + rupiah(spareparts[c.dataIndex].omzet),
                        ]
                    } }
                },
                scales: {
                    x: { ...sumbuAngka, ticks: { ...sumbuAngka.ticks, precision: 0 } },
                    y: { ...sumbuLabel, ticks: { ...sumbuLabel.ticks, autoSkip: false, font: { size: 11 } } }
                }
            }
        });
    }
});
</script>

@endpush

@endsection