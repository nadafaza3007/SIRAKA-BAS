@extends('layouts.admin')

@section('title', 'Melihat Riwayat Servis - SIRAKA')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-brand-500"></i>
                <span>Smart Search & Riwayat Servis</span>
            </h2>
            <p class="text-xs text-neutral-500">Telusuri rekam medis kendaraan secara kronologis berdasarkan nomor plat atau nama konsumen (UC-07).</p>
        </div>
        <a href="{{ route('admin.rekam-servis.create') }}" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 flex items-center gap-2 w-fit">
            <i class="fa-solid fa-plus"></i>
            <span>Input Servis Baru</span>
        </a>
    </div>

    <!-- Kotak Pencarian Tunggal Sesuai UC-07 -->
    <div class="bg-ink-900 border border-neutral-800 rounded-2xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.riwayat.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-neutral-500">
                    <i class="fa-solid fa-car text-sm"></i>
                </span>
                <input type="text" name="q" id="searchRiwayatInput" value="{{ $query }}"
                    placeholder="Cari berdasarkan Nama Konsumen atau Nomor Plat..."
                    autocomplete="off"
                    class="w-full pl-10 pr-4 py-3 bg-neutral-950/80 border border-neutral-700/80 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-semibold tracking-wide">
                <div id="instantResultsDropdown" class="hidden absolute top-full left-0 right-0 mt-2 bg-ink-900 border border-neutral-700 rounded-xl shadow-2xl z-30 max-h-72 overflow-y-auto"></div>
            </div>
            <button type="submit" class="px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-xl shadow-md shadow-brand-500/20 flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Cari Riwayat</span>
            </button>
        </form>
        <span class="text-[10px] text-neutral-500 mt-2 block">Ketik minimal 2 karakter untuk pencarian instan (contoh: "BM", "Avanza", "Ahmad", "0812").</span>
    </div>
</div>

@if($query !== '' && $kendaraans->isEmpty())
    <!-- Alur Alternatif UC-07: Data Tidak Ditemukan -->
    <div class="bg-ink-900 border border-neutral-800 rounded-2xl p-8 text-center my-6">
        <div class="w-14 h-14 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center text-2xl mx-auto mb-3">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3 class="text-sm font-bold text-white mb-1">Data Tidak Ditemukan</h3>
        <p class="text-xs text-neutral-500 max-w-md mx-auto mb-4">
            Tidak ada kendaraan atau konsumen yang cocok dengan kata kunci "{{ $query }}". Periksa kembali ejaan atau daftarkan konsumen baru jika belum pernah servis.
        </p>
        <a href="{{ route('admin.konsumen.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/20">
            <i class="fa-solid fa-user-plus"></i>
            <span>Daftarkan Konsumen Baru (UC-03)</span>
        </a>
    </div>
@endif

<!-- Daftar Hasil Pencarian (Jika Lebih Dari 1) -->
@if($kendaraans->count() > 1 && !$selectedKendaraan)
    <div class="mb-6">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-neutral-400 mb-3">
            Ditemukan {{ $kendaraans->count() }} Kendaraan yang Cocok:
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($kendaraans as $k)
                <a href="{{ route('admin.riwayat.index', ['q' => $query, 'kendaraan_id' => $k->id]) }}"
                    class="p-4 rounded-2xl bg-ink-900 border border-neutral-800 hover:border-brand-500/50 hover:bg-neutral-900/60 transition group">
                    <div class="flex items-start justify-between mb-2">
                        <span class="px-2.5 py-1 rounded-lg bg-neutral-800 border border-neutral-700 text-white font-black text-xs tracking-wider group-hover:text-brand-400 transition">
                            {{ $k->plat_nomor }}
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-brand-500/10 text-brand-400 font-bold">
                            {{ $k->rekamServis->count() }}x Servis
                        </span>
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-white">{{ $k->konsumen->nama_lengkap }}</p>
                        <p class="text-neutral-500 text-[11px]">{{ $k->merk }} {{ $k->tipe_model }} {{ $k->tahun ? '('.$k->tahun.')' : '' }}</p>
                        <p class="text-neutral-500 text-[10px] mt-1"><i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $k->konsumen->no_hp }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

<!-- Detail Kronologis Rekam Medis Kendaraan Terpilih (UC-07) -->
@if($selectedKendaraan)
    <div class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-sm">
        <!-- Header Info Kendaraan & Pemilik -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-800 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-brand-500/10 border border-brand-500/30 flex items-center justify-center text-brand-500 text-2xl font-black shrink-0">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-white tracking-wider">{{ $selectedKendaraan->plat_nomor }}</h2>
                        <span class="text-xs text-neutral-400 font-bold">({{ $selectedKendaraan->merk }} {{ $selectedKendaraan->tipe_model }})</span>
                    </div>
                    <p class="text-xs text-neutral-400 mt-0.5">
                        Pemilik: <span class="text-white font-bold">{{ $selectedKendaraan->konsumen->nama_lengkap }}</span> &bull; 
                        WhatsApp: <span class="text-brand-400 font-medium">{{ $selectedKendaraan->konsumen->no_hp }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <span class="text-[10px] text-neutral-500 uppercase font-bold block">Total Servis</span>
                    <span class="text-base font-black text-brand-400">{{ $selectedKendaraan->rekamServis->count() }} Kunjungan</span>
                </div>
                <a href="{{ route('admin.rekam-servis.create') }}?kendaraan_id={{ $selectedKendaraan->id }}" class="px-3.5 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-wrench"></i>
                    <span>Servis Baru</span>
                </a>
            </div>
        </div>

        <!-- Timeline Kronologis Rekam Servis (Dari yang Terbaru) -->
        <div class="mt-6">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-neutral-400 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-timeline text-brand-500"></i>
                <span>Riwayat Rekam Servis Kronologis (Terbaru &rarr; Terlama)</span>
            </h3>

            @if($selectedKendaraan->rekamServis->isEmpty())
                <div class="p-8 text-center text-neutral-500 text-xs italic bg-black/40 rounded-2xl border border-neutral-800">
                    Kendaraan ini belum pernah melakukan servis. Silakan input servis baru.
                </div>
            @else
                <div class="space-y-5">
                    @foreach($selectedKendaraan->rekamServis as $servis)
                        <div class="p-5 rounded-2xl bg-black/40 border border-neutral-800 hover:border-neutral-700 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-neutral-800/80 mb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full {{ $servis->status == 'selesai' ? 'bg-emerald-500' : ($servis->status == 'siap_cetak_nota' ? 'bg-sky-500' : 'bg-amber-500') }}"></span>
                                    <span class="font-extrabold text-sm text-white">
                                        {{ \Carbon\Carbon::parse($servis->tanggal_servis)->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-lg bg-neutral-800 text-xs font-black text-neutral-200">
                                        {{ number_format($servis->km_akhir, 0, ',', '.') }} KM
                                    </span>
                                    @if($servis->user)
                                        <span class="text-[10px] text-neutral-500">oleh {{ $servis->user->name }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $servis->status == 'selesai' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : ($servis->status == 'siap_cetak_nota' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30') }}">
                                        {{ str_replace('_', ' ', $servis->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs mb-4">
                                <div class="p-3.5 bg-neutral-900/60 rounded-xl border border-neutral-800">
                                    <span class="text-[10px] uppercase font-bold text-neutral-500 block mb-1">Keluhan Awal Konsumen</span>
                                    <p class="text-neutral-300 font-medium leading-relaxed">"{{ $servis->keluhan_awal }}"</p>
                                    <span class="text-[10px] text-brand-400 block mt-2">Diagnosa Awal: {{ $servis->diagnosa_awal }}</span>
                                </div>
                                <div class="p-3.5 bg-neutral-900/60 rounded-xl border border-neutral-800">
                                    <span class="text-[10px] uppercase font-bold text-emerald-500 block mb-1">Diagnosa Akhir & Tindakan Mekanik</span>
                                    <p class="text-white font-bold leading-relaxed">{{ $servis->diagnosa_akhir ?? 'Menunggu pemeriksaan fisik' }}</p>
                                    @if($servis->tindakan_servis)
                                        <p class="text-[11px] text-neutral-400 mt-1.5 leading-relaxed">{{ $servis->tindakan_servis }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Rincian Sparepart & Jasa -->
                            @if($servis->detailSpareparts->isNotEmpty() || $servis->detailJasas->isNotEmpty())
                                <div class="mb-4 text-xs">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500 block mb-1.5">Suku Cadang & Jasa Terpasang:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($servis->detailSpareparts as $dsp)
                                            <span class="px-2.5 py-1 rounded-lg bg-neutral-900 border border-neutral-800 text-neutral-300 text-[11px]">
                                                <i class="fa-solid fa-box text-brand-400 mr-1 text-[9px]"></i>
                                                {{ $dsp->sparepart->nama_barang ?? 'Item' }} ({{ $dsp->qty }}x)
                                            </span>
                                        @endforeach
                                        @foreach($servis->detailJasas as $dj)
                                            <span class="px-2.5 py-1 rounded-lg bg-neutral-900 border border-neutral-800 text-sky-300 text-[11px]">
                                                <i class="fa-solid fa-wrench text-sky-400 mr-1 text-[9px]"></i>
                                                {{ $dj->jasa->nama_jasa ?? 'Jasa' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Catatan Rekomendasi Servis Lanjutan (UC-06) -->
                            @if($servis->catatanRekomendasis->isNotEmpty())
                                <div class="p-3 rounded-xl bg-amber-500/5 border border-amber-500/25 text-xs mb-3">
                                    <span class="text-[10px] font-extrabold uppercase text-amber-400 block mb-1">
                                        <i class="fa-solid fa-bell mr-1"></i> Catatan Rekomendasi Servis Lanjutan:
                                    </span>
                                    <ul class="space-y-1 text-neutral-300">
                                        @foreach($servis->catatanRekomendasis as $rek)
                                            <li class="flex items-center justify-between">
                                                <span>&bull; {{ $rek->catatan }}</span>
                                                <span class="text-[10px] font-bold uppercase {{ $rek->status_konfirmasi == 'sudah' ? 'text-emerald-400' : 'text-neutral-500' }}">
                                                    [{{ $rek->status_konfirmasi == 'sudah' ? 'Sudah Dikerjakan' : 'Belum Dikonfirmasi' }}]
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Aksi & Nota -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-3 border-t border-neutral-800/80 gap-3">
                                <div>
                                    @if($servis->nota)
                                        <span class="text-xs text-neutral-400">Total Transaksi: <strong class="text-white">Rp {{ number_format($servis->nota->total_biaya, 0, ',', '.') }}</strong> ({{ strtoupper($servis->nota->status_pembayaran) }})</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($servis->status == 'menunggu_pengerjaan')
                                        <a href="{{ route('admin.rekam-servis.koreksi-form', $servis->id) }}" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-bold text-xs">
                                            Koreksi Diagnosa (UC-05)
                                        </a>
                                    @elseif($servis->status == 'siap_cetak_nota' || $servis->status == 'selesai')
                                        <a href="{{ route('admin.rekam-servis.nota', $servis->id) }}" class="px-3 py-1.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-lg font-bold text-xs flex items-center gap-1.5">
                                            <i class="fa-solid fa-receipt text-brand-400"></i>
                                            <span>Lihat / Cetak Nota Transaksi (UC-08)</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

@push('scripts')
<script>
    const searchInput = document.getElementById('searchRiwayatInput');
    const dropdown = document.getElementById('instantResultsDropdown');
    let timer = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timer);
        const val = this.value.trim();
        if (val.length < 2) {
            dropdown.innerHTML = '';
            dropdown.classList.add('hidden');
            return;
        }

        timer = setTimeout(() => {
            fetch(`{{ route('admin.riwayat.search-api') }}?q=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        dropdown.innerHTML = '<div class="p-3 text-xs text-neutral-500 text-center">Tidak ada kendaraan yang cocok.</div>';
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    data.forEach(item => {
                        html += `
                            <a href="{{ route('admin.riwayat.index') }}?q=${encodeURIComponent(item.plat_nomor)}&kendaraan_id=${item.id}"
                               class="flex items-center justify-between p-3 hover:bg-neutral-800 text-xs border-b border-neutral-800/60 last:border-b-0 transition">
                                <div>
                                    <span class="font-black text-white tracking-wide text-xs bg-neutral-800 px-2 py-0.5 rounded border border-neutral-700">${item.plat_nomor}</span>
                                    <span class="text-neutral-300 font-bold ml-2">${item.konsumen_nama}</span>
                                    <span class="text-neutral-500 text-[11px] block mt-0.5">${item.merk} ${item.tipe_model}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-brand-400 font-bold">${item.total_servis}x Servis</span>
                                    <span class="text-[10px] text-neutral-500 block">${item.km_terakhir.toLocaleString('id-ID')} KM</span>
                                </div>
                            </a>
                        `;
                    });
                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');
                });
        }, 250);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection

