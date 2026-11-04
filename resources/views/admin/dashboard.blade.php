@extends('layouts.admin')

@section('title', 'Dashboard Utama - SIRAKA')

@section('content')

@php
    $statusServisArr = collect($statusServis ?? [])->toArray();
    $servisSelesaiSaatIni = (int) ($statusServisArr['selesai'] ?? 0);

    $sparepartGrafikDash = $sparepartTerlaris->map(fn ($sp) => [
        'nama'  => (string) $sp->nama_barang,
        'unit'  => (int) $sp->unit_terjual,
        'omzet' => $isAdmin ? (float) $sp->omzet : null,
    ])->values();

    // Opsional: kirim $grafikServisMasuk dari controller (format sama: periode, total)
    $grafikServisMasukData = isset($grafikServisMasuk) ? $grafikServisMasuk : [];
@endphp

<div class="mb-6">
    <h2 class="text-xl font-extrabold text-white tracking-tight">
        Panel {{ $isAdmin ? 'Owner & Manajerial' : 'Operasional Bengkel' }}
    </h2>
    <p class="text-xs text-neutral-500">
        Ringkasan operasional bengkel Baba Auto Service.
    </p>
</div>

{{-- KARTU STATISTIK --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    @foreach([
        ['Konsumen', number_format($totalKonsumen,0,',','.') . ' Orang', 'text-white'],
        ['Kendaraan Terdaftar', number_format($totalKendaraan,0,',','.') . ' Unit', 'text-white'],
        ['Servis Berjalan', number_format($servisBerjalan,0,',','.') . ' Mobil', 'text-brand-500'],
        $isAdmin
            ? ['Pemasukan Bulan Ini', 'Rp ' . number_format($pemasukanBulan,0,',','.'), 'text-emerald-400']
            : ['Servis Selesai Bulan Ini', number_format($servisSelesaiBulan,0,',','.'), 'text-emerald-400']
    ] as [$label, $nilai, $warna])

    <div class="bg-ink-900 p-4 rounded-2xl border border-neutral-800 shadow-sm">
        <span class="text-neutral-500 text-[10px] font-bold uppercase tracking-wider">
            {{ $label }}
        </span>

        <div class="text-xl font-extrabold mt-1 break-words {{ $warna }}">
            {{ $nilai }}
        </div>
    </div>

    @endforeach

</div>


{{-- ============ BARIS 1: PEMASUKAN + AKTIVITAS SERVIS (ADMIN) ============ --}}
@if($isAdmin)

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

    {{-- 1. TREN PEMASUKAN --}}
    <div class="min-w-0 flex flex-col bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">

        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-white">Tren Pemasukan Bulanan</h3>
                <p class="text-xs text-neutral-500 mt-1">
                    Uang masuk setelah kembalian, termasuk cicilan dan tips.
                </p>
            </div>
            <span id="badgePemasukan" class="hidden shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold"></span>
        </div>

        <div class="relative mt-4 h-60 min-w-0 flex-1 sm:h-72">
            <canvas id="sirakaPemasukanDash"></canvas>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 border-t border-neutral-800 pt-4">
            <div class="rounded-xl border border-neutral-800 px-4 py-3">
                <div class="text-[11px] text-neutral-500">Piutang pelanggan (sisa tagihan)</div>
                <div class="mt-1 text-lg font-extrabold text-amber-400">
                    Rp {{ number_format($totalPiutang,0,',','.') }}
                </div>
                <div class="text-[11px] text-neutral-500">{{ $jumlahNotaPiutang }} nota dengan sisa tagihan</div>
            </div>

            <div class="rounded-xl border border-neutral-800 px-4 py-3">
                <div class="text-[11px] text-neutral-500">Total nilai nota lunas (sejak awal)</div>
                <div class="mt-1 text-lg font-extrabold text-white">
                    Rp {{ number_format($totalOmzet,0,',','.') }}
                </div>
                <div class="text-[11px] text-neutral-500">Berbeda dengan uang masuk bulan ini</div>
            </div>
        </div>

    </div>


    {{-- 2. TREN AKTIVITAS SERVIS --}}
    <div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">

        <h3 class="text-sm font-bold text-white">Tren Aktivitas Servis Bulanan</h3>
        <p class="text-xs text-neutral-500 mt-1">
            Jumlah servis yang ditangani bengkel setiap bulan.
        </p>

        <div class="relative mt-4 h-60 min-w-0 sm:h-72">
            <canvas id="sirakaServisSelesaiDash"></canvas>
        </div>

        <p id="insightServis" class="mt-3 text-[11px] text-neutral-500"></p>

    </div>

</div>

@endif


{{-- ============ BARIS 2: KONDISI ANTREAN + SPAREPART ============ --}}
<div class="grid gap-4 lg:grid-cols-2 mb-6">

    {{-- 3. KONDISI ANTREAN --}}
    <div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">

        <h3 data-chart-title class="text-sm font-bold text-white">Kondisi Antrean Servis</h3>
        <p data-chart-muted class="text-xs text-neutral-500 mt-1">
            Posisi pekerjaan saat ini, bukan tren bulanan.
        </p>

        <div class="mt-4" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:24px">

            <div class="relative" style="width:176px;height:176px;flex-shrink:0">
                <canvas id="sirakaStatusDash"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <span id="totalStatus" class="text-3xl font-extrabold text-white">0</span>
                    <span class="text-[11px] text-neutral-500">total servis</span>
                </div>
            </div>

            <div style="flex:1 1 220px;min-width:0">

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl border border-neutral-800 px-3 py-2.5">
                        <div class="text-lg font-extrabold text-brand-500">{{ number_format($servisBerjalan,0,',','.') }}</div>
                        <div class="text-[11px] text-neutral-500">Sedang dikerjakan</div>
                    </div>
                    <div class="rounded-xl border border-neutral-800 px-3 py-2.5">
                        <div class="text-lg font-extrabold text-emerald-400">{{ number_format($servisSelesaiSaatIni,0,',','.') }}</div>
                        <div class="text-[11px] text-neutral-500">Sudah selesai</div>
                    </div>
                </div>

                <ul id="legendStatus" class="mt-4 space-y-2 text-xs"></ul>

            </div>

        </div>

    </div>


    {{-- 4. SPAREPART TERLARIS --}}
    <div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">

        <div class="flex items-start justify-between gap-3 mb-4">

            <div class="min-w-0">
                <h3 class="text-sm font-bold text-white">5 Sparepart Terlaris Bulan Ini</h3>
                <p class="text-[11px] text-neutral-500 mt-0.5">
                    {{ now()->translatedFormat('F Y') }} · hanya dari transaksi lunas
                </p>
            </div>

            @if($isAdmin)
            <a href="{{ route('admin.laporan.index', [
                    'start_date' => now()->startOfMonth()->toDateString(),
                    'end_date'   => now()->endOfMonth()->toDateString()
                ]) }}"
               class="shrink-0 text-xs font-bold text-brand-500 hover:underline">
                Analisis lengkap
            </a>
            @endif

        </div>

        @if($sparepartGrafikDash->isNotEmpty())

            <div class="relative min-w-0" style="height: {{ max(220, $sparepartGrafikDash->count() * 62 + 50) }}px">
                <canvas id="sirakaSparepartDash"></canvas>
            </div>

            <p class="mt-3 text-[11px] text-neutral-500">
                Arahkan kursor ke batang untuk melihat nama lengkap@if($isAdmin) dan omzet@endif.
            </p>

        @else

            <p class="py-6 text-center text-xs text-neutral-500">
                Belum ada sparepart dari nota lunas bulan ini.
            </p>

        @endif

    </div>

</div>


{{-- ANTREAN SERVIS TERBARU --}}
<div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-white">Antrean Servis Terbaru</h3>

        <a href="{{ route('admin.rekam-servis.create') }}"
           class="text-xs font-bold text-brand-500 hover:underline">
            + Input Servis Baru
        </a>
    </div>

    <div class="overflow-x-auto -mx-5 px-5">

        <table class="w-full min-w-[640px] text-left text-xs text-neutral-400">

            <thead class="bg-black/40 border-b border-neutral-800 font-bold text-neutral-500">
                <tr>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Kendaraan</th>
                    <th class="p-3">Keluhan Awal</th>
                    <th class="p-3">Diagnosa Awal</th>
                    <th class="p-3 text-center">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-neutral-800">

                @forelse($antreanTerbaru as $item)

                    <tr class="hover:bg-neutral-800/40 transition">

                        <td class="p-3">{{ $item->tanggal_servis }}</td>

                        <td class="p-3 font-bold text-white">
                            {{ $item->kendaraan?->plat_nomor ?? '-' }}
                            <span class="text-neutral-500 font-normal">
                                ({{ $item->kendaraan?->konsumen?->nama_lengkap ?? '-' }})
                            </span>
                        </td>

                        <td class="p-3">{{ $item->keluhan_awal }}</td>

                        <td class="p-3 text-brand-400 font-semibold">{{ $item->diagnosa_awal }}</td>

                        <td class="p-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                {{ $item->status === 'selesai'
                                    ? 'bg-emerald-500/10 text-emerald-400'
                                    : 'bg-amber-500/10 text-amber-400' }}">
                                {{ str_replace('_',' ',$item->status) }}
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="p-8 text-center text-neutral-600">
                            Belum ada aktivitas servis tercatat.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof Chart === 'undefined') return;

    // =========================================================
    // HELPER
    // =========================================================
    const rupiah = x => 'Rp ' + Number(x || 0).toLocaleString('id-ID');

    const rupiahRingkas = v => {
        v = Number(v || 0);
        if (v >= 1e9) return 'Rp ' + (v / 1e9).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' M';
        if (v >= 1e6) return 'Rp ' + (v / 1e6).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' jt';
        if (v >= 1e3) return 'Rp ' + (v / 1e3).toLocaleString('id-ID', {maximumFractionDigits: 0}) + ' rb';
        return 'Rp ' + v;
    };

    const parseRgb = s => (s.match(/[\d.]+/g) || [0, 0, 0, 1]).map(Number);

    const withAlpha = (s, a) => {
        const [r, g, b] = parseRgb(s);
        return `rgba(${r},${g},${b},${a})`;
    };

    const luminance = s => {
        const [r, g, b] = parseRgb(s);
        return (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    };

    // Cari warna latar kartu yang sebenarnya (naik ke elemen induk)
    const latarKartu = el => {
        while (el) {
            const c = getComputedStyle(el).backgroundColor;
            const p = parseRgb(c);
            if (p.length < 4 || p[3] > 0.5) return c;
            el = el.parentElement;
        }
        return 'rgb(23,23,23)';
    };

    // =========================================================
    // PALET MENGIKUTI TEMA (LIGHT / DARK)
    // Warna teks diambil dari elemen kartu sendiri, jadi selalu
    // sama dengan yang dipakai layout pada mode aktif.
    // =========================================================
    const elJudul = document.querySelector('[data-chart-title]');
    const elRedup = document.querySelector('[data-chart-muted]');

    const palet = () => {
        const kuat  = getComputedStyle(elJudul).color;
        const redup = getComputedStyle(elRedup).color;
        const latar = latarKartu(elJudul);
        const terang = luminance(latar) > 0.55;

        return {
            terang,
            kuat, redup, latar,
            grid:    terang ? 'rgba(0,0,0,.09)' : 'rgba(255,255,255,.09)',
            oranye:  terang ? '#ea580c' : '#fb923c',
            biru:    terang ? '#2563eb' : '#60a5fa',
            tipBg:   terang ? '#ffffff' : '#0a0a0a',
            tipBord: terang ? '#d4d4d4' : '#404040',
            tipText: terang ? '#171717' : '#f5f5f5'
        };
    };

    const tooltipDasar = p => ({
        backgroundColor: p.tipBg,
        borderColor: p.tipBord,
        borderWidth: 1,
        titleColor: p.tipText,
        bodyColor: p.tipText,
        padding: 10,
        cornerRadius: 10,
        boxPadding: 4
    });

    const warnaStatus = (k, i, terang) => {
        k = k.toLowerCase();
        const cadangan = terang
            ? ['#2563eb', '#7c3aed', '#0d9488', '#db2777']
            : ['#60a5fa', '#a78bfa', '#2dd4bf', '#f472b6'];
        if (k.includes('selesai'))                          return terang ? '#059669' : '#34d399';
        if (k.includes('batal'))                            return terang ? '#dc2626' : '#f87171';
        if (k.includes('proses') || k.includes('kerja'))    return terang ? '#ea580c' : '#fb923c';
        if (k.includes('tunggu') || k.includes('antre'))    return terang ? '#d97706' : '#fbbf24';
        return cadangan[i % cadangan.length];
    };

    const gambarUlang = (id, config) => {
        const el = document.getElementById(id);
        if (!el) return;
        const lama = Chart.getChart(el);
        if (lama) lama.destroy();
        new Chart(el, config);
    };

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 11;

    // =========================================================
    // DATA
    // =========================================================
    const status = @json($statusServisArr);
    const kunci  = Object.keys(status);
    const nilai  = Object.values(status).map(Number);
    const total  = nilai.reduce((a, b) => a + b, 0);
    const nama   = kunci.map(k => {
        const t = k.replaceAll('_', ' ');
        return t.charAt(0).toUpperCase() + t.slice(1);
    });

    const spareparts = @json($sparepartGrafikDash);

    document.getElementById('totalStatus').textContent = total.toLocaleString('id-ID');

    @if($isAdmin)

    const formatBulan = periode => {
        const [tahun, bulan] = periode.split('-').map(Number);
        return new Intl.DateTimeFormat('id-ID', { month: 'short', year: '2-digit' })
            .format(new Date(tahun, bulan - 1, 1));
    };

    const pemasukan = @json($grafikPemasukan);
    const selesai   = @json($grafikServisSelesai);
    const masuk     = @json($grafikServisMasukData);

    const petaSelesai = Object.fromEntries(selesai.map(x => [x.periode, Number(x.total)]));
    const petaMasuk   = Object.fromEntries(masuk.map(x => [x.periode, Number(x.total)]));
    const periode = [...new Set([...Object.keys(petaSelesai), ...Object.keys(petaMasuk)])].sort();

    // Badge perubahan: bandingkan dua bulan PENUH terakhir (bulan berjalan dilewati)
    (function () {
        const now = new Date();
        const bulanIni = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
        const data = pemasukan.filter(x => x.periode !== bulanIni);
        if (data.length < 2) return;

        const akhir = data[data.length - 1];
        const awal  = data[data.length - 2];
        if (!(Number(awal.total) > 0)) return;

        const persen = (Number(akhir.total) - Number(awal.total)) / Number(awal.total) * 100;
        const naik = persen >= 0;
        const badge = document.getElementById('badgePemasukan');

        badge.textContent = (naik ? '+' : '') +
            persen.toLocaleString('id-ID', {maximumFractionDigits: 1}) + '% · ' +
            formatBulan(akhir.periode) + ' vs ' + formatBulan(awal.periode);
        badge.style.background = naik ? 'rgba(16,185,129,.14)' : 'rgba(239,68,68,.14)';
        badge.style.color      = naik ? '#059669' : '#dc2626';
        badge.classList.remove('hidden');
    })();

    // Insight bulan tersibuk
    const kunciSelesai = Object.keys(petaSelesai);
    if (kunciSelesai.length) {
        const puncak = kunciSelesai.reduce((a, b) => petaSelesai[a] >= petaSelesai[b] ? a : b);
        document.getElementById('insightServis').textContent =
            `Bulan tersibuk: ${formatBulan(puncak)} dengan ${petaSelesai[puncak]} servis selesai.`;
    }

    @endif

    // =========================================================
    // GAMBAR SEMUA GRAFIK (dipanggil ulang saat tema berganti)
    // =========================================================
    function render() {

        const p = palet();
        Chart.defaults.color = p.redup;

        // ---------- Donut kondisi antrean ----------
        const warna = kunci.map((k, i) => warnaStatus(k, i, p.terang));
        const legend = document.getElementById('legendStatus');

        if (total === 0) {
            legend.innerHTML = '<li class="text-neutral-500">Belum ada data servis.</li>';
        } else {
            legend.innerHTML = kunci.map((k, i) => `
                <li class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2 text-neutral-500">
                        <span class="h-2.5 w-2.5 rounded-full" style="background:${warna[i]}"></span>${nama[i]}
                    </span>
                    <span class="font-bold text-white">${nilai[i].toLocaleString('id-ID')}
                        <span class="font-medium text-neutral-500">(${Math.round(nilai[i] / total * 100)}%)</span>
                    </span>
                </li>`).join('');
        }

        gambarUlang('sirakaStatusDash', {
            type: 'doughnut',
            data: {
                labels: nama,
                datasets: [{
                    data: total === 0 ? [1] : nilai,
                    backgroundColor: total === 0 ? [p.grid] : warna,
                    borderColor: p.latar,
                    borderWidth: 3,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDasar(p),
                        enabled: total !== 0,
                        callbacks: { label: c => ` ${c.label}: ${c.parsed} servis` }
                    }
                }
            }
        });

        // ---------- Sparepart terlaris ----------
        if (spareparts.length) {

            const duaBaris = nama => {
                const batas = 22;
                const hasil = [''];
                for (const k of String(nama).split(/\s+/)) {
                    const i = hasil.length - 1;
                    const gab = (hasil[i] + ' ' + k).trim();
                    if (gab.length <= batas) hasil[i] = gab;
                    else if (hasil.length < 2) hasil.push(k);
                    else { hasil[1] = (hasil[1] + ' ' + k).slice(0, batas - 1) + '…'; break; }
                }
                return hasil;
            };

            const labelUnit = {
                id: 'labelUnitSparepart',
                afterDatasetsDraw(chart) {
                    const ctx = chart.ctx, area = chart.chartArea;
                    ctx.save();
                    ctx.font = '700 11px ' + Chart.defaults.font.family;
                    ctx.textBaseline = 'middle';
                    chart.getDatasetMeta(0).data.forEach((bar, i) => {
                        const t = spareparts[i].unit + ' unit';
                        const w = ctx.measureText(t).width;
                        if (area.right - bar.x >= w + 10) {
                            ctx.textAlign = 'left';
                            ctx.fillStyle = p.kuat;
                            ctx.fillText(t, bar.x + 7, bar.y);
                        } else {
                            ctx.textAlign = 'right';
                            ctx.fillStyle = '#ffffff';
                            ctx.fillText(t, bar.x - 7, bar.y);
                        }
                    });
                    ctx.restore();
                }
            };

            gambarUlang('sirakaSparepartDash', {
                type: 'bar',
                data: {
                    labels: spareparts.map(sp => duaBaris(sp.nama)),
                    datasets: [{
                        label: 'Unit terjual',
                        data: spareparts.map(sp => sp.unit),
                        backgroundColor: spareparts.map((_, i) =>
                            i === 0 ? p.oranye : (p.terang ? '#fdba74' : 'rgba(251,146,60,.55)')),
                        borderRadius: 6,
                        barThickness: 20,
                        maxBarThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { right: 8 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...tooltipDasar(p),
                            displayColors: false,
                            callbacks: {
                                title: items => spareparts[items[0].dataIndex].nama,
                                label: item => {
                                    const sp = spareparts[item.dataIndex];
                                    const out = ['Terjual: ' + sp.unit + ' unit'];
                                    if (sp.omzet !== null) out.push('Omzet: ' + rupiah(sp.omzet));
                                    return out;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            suggestedMax: Math.max(...spareparts.map(sp => sp.unit)) + 1,
                            grid: { color: p.grid },
                            border: { display: false },
                            ticks: { color: p.redup, precision: 0, stepSize: 1 }
                        },
                        y: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: p.kuat, autoSkip: false, font: { size: 11 } }
                        }
                    }
                },
                plugins: [labelUnit]
            });
        }

        @if($isAdmin)

        // ---------- Tren pemasukan ----------
        gambarUlang('sirakaPemasukanDash', {
            type: 'line',
            data: {
                labels: pemasukan.map(x => formatBulan(x.periode)),
                datasets: [{
                    label: 'Pemasukan',
                    data: pemasukan.map(x => x.total),
                    borderColor: p.oranye,
                    borderWidth: 2.5,
                    fill: true,
                    backgroundColor: ctx => {
                        const a = ctx.chart.chartArea;
                        const [r, g, b] = parseRgb(p.oranye.startsWith('#')
                            ? `rgb(${parseInt(p.oranye.slice(1,3),16)},${parseInt(p.oranye.slice(3,5),16)},${parseInt(p.oranye.slice(5,7),16)})`
                            : p.oranye);
                        if (!a) return `rgba(${r},${g},${b},.15)`;
                        const gr = ctx.chart.ctx.createLinearGradient(0, a.top, 0, a.bottom);
                        gr.addColorStop(0, `rgba(${r},${g},${b},${p.terang ? .28 : .35})`);
                        gr.addColorStop(1, `rgba(${r},${g},${b},0)`);
                        return gr;
                    },
                    cubicInterpolationMode: 'monotone',
                    pointRadius: 3.5,
                    pointBackgroundColor: p.oranye,
                    pointBorderColor: p.latar,
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDasar(p),
                        callbacks: { label: c => ' ' + rupiah(c.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: p.redup } },
                    y: {
                        beginAtZero: true,
                        grid: { color: p.grid },
                        border: { display: false },
                        ticks: { color: p.redup, callback: v => rupiahRingkas(v) }
                    }
                }
            }
        });

        // ---------- Tren aktivitas servis ----------
        const dataset = [];

        if (masuk.length) {
            dataset.push({
                label: 'Servis masuk',
                data: periode.map(k => petaMasuk[k] ?? 0),
                backgroundColor: p.biru,
                borderRadius: 6,
                maxBarThickness: 22
            });
        }

        dataset.push({
            label: 'Servis selesai',
            data: periode.map(k => petaSelesai[k] ?? 0),
            backgroundColor: p.oranye,
            borderRadius: 6,
            maxBarThickness: 22
        });

        gambarUlang('sirakaServisSelesaiDash', {
            type: 'bar',
            data: { labels: periode.map(formatBulan), datasets: dataset },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: dataset.length > 1,
                        align: 'end',
                        labels: {
                            color: p.kuat,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        ...tooltipDasar(p),
                        callbacks: { label: c => ` ${c.dataset.label}: ${c.parsed.y} servis` }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: p.redup } },
                    y: {
                        beginAtZero: true,
                        grid: { color: p.grid },
                        border: { display: false },
                        ticks: { color: p.redup, precision: 0 }
                    }
                }
            }
        });

        @endif
    }

    render();

    // =========================================================
    // GAMBAR ULANG SAAT USER GANTI TEMA
    // =========================================================
    let terakhir = palet().terang + '|' + palet().redup;
    let tunda = null;

    const cekTema = () => {
        clearTimeout(tunda);
        tunda = setTimeout(() => {
            const p = palet();
            const sekarang = p.terang + '|' + p.redup;
            if (sekarang !== terakhir) {
                terakhir = sekarang;
                render();
            }
        }, 450);
    };

    const opsi = { attributes: true, attributeFilter: ['class', 'data-theme', 'data-bs-theme', 'style'] };
    new MutationObserver(cekTema).observe(document.documentElement, opsi);
    new MutationObserver(cekTema).observe(document.body, opsi);

    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', cekTema);
    }

});
</script>

@endpush