@extends('layouts.pelanggan')

@section('title', 'Rekam Servis Kendaraan Saya - SIRAKA')

@section('content')
<div class="mb-6">
    <div class="bg-gradient-to-r from-ink-900 via-neutral-900 to-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 font-extrabold text-[10px] uppercase tracking-wider">
                    Rekam Medis Kendaraan
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-white mt-2">Selamat Datang, {{ auth()->user()->name }}</h1>
                <p class="text-xs text-neutral-400 mt-1">Transparansi riwayat perawatan dan catatan rekomendasi kendaraan Anda di Baba Auto Service.</p>
            </div>
            <div class="bg-black/50 border border-neutral-800 rounded-2xl p-3.5 text-center shrink-0">
                <span class="block text-[10px] text-neutral-500 uppercase font-bold">Total Kendaraan Terdaftar</span>
                <span class="text-2xl font-black text-white">{{ isset($kendaraans) ? $kendaraans->count() : 0 }} <span class="text-xs font-semibold text-brand-400">Unit</span></span>
            </div>
        </div>

        <!-- Search Bar Plat Nomor -->
        <form method="GET" action="{{ route('pelanggan.rekam-servis') }}" class="mt-6 flex gap-2">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-neutral-500">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="plat" value="{{ $searchPlat ?? '' }}" placeholder="Cari berdasarkan nomor plat (misal: BM 1452 AA)..."
                    class="w-full pl-10 pr-4 py-2.5 bg-neutral-950/80 border border-neutral-700/80 rounded-2xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 uppercase font-bold tracking-wider">
            </div>
            @if(!empty($searchPlat))
                <a href="{{ route('pelanggan.rekam-servis') }}" class="px-3.5 py-2.5 rounded-2xl bg-neutral-800 text-neutral-300 hover:text-white text-xs font-bold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-xmark"></i>
                    <span class="hidden sm:inline">Reset</span>
                </a>
            @endif
            <button type="submit" class="px-5 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs shadow-lg shadow-brand-500/20 transition flex items-center gap-2">
                <span>Cari</span>
            </button>
        </form>
    </div>
</div>

<!-- Daftar Kendaraan & Riwayat Servis -->
@if(!isset($kendaraans) || $kendaraans->isEmpty())
    <div class="bg-ink-900 border border-neutral-800 rounded-3xl p-10 text-center">
        <div class="w-16 h-16 rounded-full bg-neutral-800/80 flex items-center justify-center text-neutral-500 text-2xl mx-auto mb-3">
            <i class="fa-solid fa-car-tunnel"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-1">Belum Ada Riwayat Servis</h3>
        <p class="text-xs text-neutral-500 max-w-md mx-auto">
            @if(!empty($searchPlat))
                Tidak ditemukan kendaraan dengan nomor plat "{{ $searchPlat }}".
            @else
                Data servis kendaraan Anda belum tercatat atau nomor HP Anda belum didaftarkan pada sistem kasir bengkel. Silakan hubungi petugas Baba Auto Service saat kunjungan servis berikutnya.
            @endif
        </p>
    </div>
@else
    <div class="space-y-6">
        @foreach($kendaraans as $kendaraan)
            <div class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 shadow-sm">
                <!-- Info Header Kendaraan -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-neutral-800 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-500 font-black text-lg">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-neutral-800 border border-neutral-700 font-black text-white text-sm tracking-wider">
                                    {{ $kendaraan->plat_nomor }}
                                </span>
                                @if($kendaraan->tahun)
                                    <span class="text-xs text-neutral-400 font-bold">Th. {{ $kendaraan->tahun }}</span>
                                @endif
                            </div>
                            <h2 class="text-xs font-semibold text-neutral-300 mt-0.5">{{ $kendaraan->merk }} &bull; {{ $kendaraan->tipe_model }}</h2>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] text-neutral-500 block uppercase font-bold">Kunjungan Servis</span>
                        <span class="text-sm font-black text-brand-400">{{ $kendaraan->rekamServis->count() }} Kali Servis</span>
                    </div>
                </div>

                <!-- Timeline Riwayat Servis Kendaraan -->
                <div class="mt-5">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-neutral-500 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-brand-500"></i>
                        <span>Riwayat Rekam Servis</span>
                    </h3>

                    @if($kendaraan->rekamServis->isEmpty())
                        <p class="text-xs text-neutral-600 italic py-3">Belum ada riwayat servis untuk unit ini.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($kendaraan->rekamServis as $servis)
                                <div class="bg-black/40 border border-neutral-800/80 rounded-2xl p-4 sm:p-5 hover:border-neutral-700 transition">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $servis->status == 'selesai' ? 'bg-emerald-500' : ($servis->status == 'siap_cetak_nota' ? 'bg-sky-500' : 'bg-amber-500') }}"></span>
                                            <span class="font-extrabold text-white text-xs">
                                                {{ \Carbon\Carbon::parse($servis->tanggal_servis)->translatedFormat('d F Y') }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-md bg-neutral-800 text-[10px] font-bold text-neutral-300">
                                                {{ number_format($servis->km_akhir, 0, ',', '.') }} KM
                                            </span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase w-fit {{ $servis->status == 'selesai' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : ($servis->status == 'siap_cetak_nota' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30') }}">
                                            {{ $servis->status == 'selesai' ? 'Selesai / Lunas' : ($servis->status == 'siap_cetak_nota' ? 'Siap Nota' : 'Sedang Dikerjakan') }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs mb-3">
                                        <div class="p-3 bg-neutral-900/60 rounded-xl border border-neutral-800/60">
                                            <span class="text-[10px] uppercase font-bold text-neutral-500 block mb-0.5">Keluhan Awal Konsumen</span>
                                            <p class="text-neutral-300 font-medium">"{{ $servis->keluhan_awal }}"</p>
                                        </div>
                                        <div class="p-3 bg-neutral-900/60 rounded-xl border border-neutral-800/60">
                                            <span class="text-[10px] uppercase font-bold text-neutral-500 block mb-0.5">Diagnosa & Tindakan Mekanik</span>
                                            <p class="text-white font-bold">{{ $servis->diagnosa_akhir ?? $servis->diagnosa_awal }}</p>
                                            @if($servis->tindakan_servis)
                                                <p class="text-[11px] text-neutral-400 mt-1">{{ $servis->tindakan_servis }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Catatan Rekomendasi Servis Lanjutan -->
                                    @if(relation_loaded($servis, 'catatanRekomendasis') && $servis->catatanRekomendasis->isNotEmpty())
                                        <div class="p-3 rounded-xl bg-amber-500/5 border border-amber-500/20 text-xs mb-3">
                                            <div class="flex items-center gap-2 text-amber-400 font-extrabold text-[11px] mb-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                <span>Rekomendasi Perbaikan Servis Berikutnya:</span>
                                            </div>
                                            <ul class="list-disc list-inside text-neutral-300 space-y-1 pl-1">
                                                @foreach($servis->catatanRekomendasis as $rek)
                                                    <li>
                                                        <span>{{ $rek->catatan }}</span>
                                                        <span class="ml-2 px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $rek->status_konfirmasi == 'sudah' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-neutral-800 text-neutral-400' }}">
                                                            {{ $rek->status_konfirmasi == 'sudah' ? 'Sudah Dikerjakan' : 'Perlu Perhatian' }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Rincian Biaya & Tombol Detail / Nota -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-2 border-t border-neutral-800/80 gap-3">
                                        <div>
                                            @if($servis->nota)
                                                <div class="text-[11px] text-neutral-400">
                                                    <span>Total Biaya:</span>
                                                    <span class="text-sm font-black text-white ml-1">Rp {{ number_format($servis->nota->total_biaya, 0, ',', '.') }}</span>
                                                    <span class="text-[10px] text-emerald-400 font-bold ml-1.5">({{ strtoupper($servis->nota->status_pembayaran) }})</span>
                                                </div>
                                            @else
                                                <span class="text-xs text-neutral-500 italic">Estimasi biaya sedang diproses mekanik.</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('pelanggan.detail-servis', $servis->id) }}" class="px-3.5 py-1.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-white text-xs font-bold transition flex items-center gap-1.5">
                                                <i class="fa-solid fa-file-lines"></i>
                                                <span>Lihat Rincian & Nota</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection