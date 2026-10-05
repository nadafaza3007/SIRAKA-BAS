@extends('layouts.admin')

@section('title', 'Dashboard Utama - SIRAKA')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-extrabold text-white tracking-tight">Panel Owner & Manajerial</h2>
    <p class="text-xs text-neutral-500">Ringkasan operasional bengkel Baba Auto Service.</p>
</div>

<!-- 4 Kartu Statistik -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-ink-900 p-4 rounded-2xl border border-neutral-800 shadow-sm">
        <span class="text-neutral-500 text-[10px] font-bold uppercase tracking-wider">Konsumen</span>
        <div class="text-xl font-extrabold text-white mt-1">{{ $totalKonsumen }} Orang</div>
    </div>
    <div class="bg-ink-900 p-4 rounded-2xl border border-neutral-800 shadow-sm">
        <span class="text-neutral-500 text-[10px] font-bold uppercase tracking-wider">Kendaraan Terdaftar</span>
        <div class="text-xl font-extrabold text-white mt-1">{{ $totalKendaraan }} Unit</div>
    </div>
    <div class="bg-ink-900 p-4 rounded-2xl border border-neutral-800 shadow-sm">
        <span class="text-neutral-500 text-[10px] font-bold uppercase tracking-wider">Servis Berjalan</span>
        <div class="text-xl font-extrabold text-brand-500 mt-1">{{ $servisBerjalan }} Mobil</div>
    </div>
    <div class="bg-ink-900 p-4 rounded-2xl border border-neutral-800 shadow-sm">
        <span class="text-neutral-500 text-[10px] font-bold uppercase tracking-wider">Total Pendapatan</span>
        <div class="text-xl font-extrabold text-emerald-400 mt-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
    </div>
</div>

<!-- Tabel Antrean Servis Terbaru -->
<div class="bg-ink-900 rounded-2xl border border-neutral-800 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-white">Antrean Servis Terbaru</h3>
        <a href="{{ route('admin.rekam-servis.create') }}" class="text-xs font-bold text-brand-500 hover:text-brand-400 hover:underline">
            + Input Servis Baru
        </a>
    </div>
    <div class="overflow-x-auto -mx-5 px-5">
        <table class="w-full text-left text-xs text-neutral-400 min-w-[640px]">
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
                        {{ $item->kendaraan->plat_nomor }}
                        <span class="text-neutral-500 font-normal">({{ $item->kendaraan->konsumen->nama_lengkap }})</span>
                    </td>
                    <td class="p-3">{{ $item->keluhan_awal }}</td>
                    <td class="p-3 font-semibold text-brand-400">{{ $item->diagnosa_awal }}</td>
                    <td class="p-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $item->status == 'selesai' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                            {{ str_replace('_', ' ', $item->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-neutral-600">Belum ada aktivitas servis tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection