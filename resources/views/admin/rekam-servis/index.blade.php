@extends('layouts.admin')

@section('title', 'Daftar Rekam Servis - SIRAKA')

@section('content')
<!-- Notifikasi Alert -->
@if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Daftar Rekam Servis</h2>
        <p class="text-xs text-neutral-500">Pantau proses pengerjaan servis, koreksi diagnosa fisik mekanik, dan kelola rincian nota.</p>
    </div>
    <a href="{{ route('admin.rekam-servis.create') }}" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 flex items-center gap-2 w-fit">
        <i class="fa-solid fa-plus"></i>
        <span>Input Servis Baru</span>
    </a>
</div>

<!-- Tabel Rekam Servis -->
<div class="bg-ink-900 rounded-2xl border border-neutral-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-neutral-400">
            <thead class="bg-black/40 border-b border-neutral-800 uppercase font-bold text-neutral-500">
                <tr>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Kendaraan</th>
                    <th class="p-4">Diagnosa Awal</th>
                    <th class="p-4">Diagnosa Akhir</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($servis as $item)
                    <tr class="hover:bg-neutral-800/40 transition">
                        <td class="p-4 text-neutral-300 font-mono">{{ \Carbon\Carbon::parse($item->tanggal_servis)->format('Y-m-d') }}</td>
                        <td class="p-4">
                            <div class="font-bold text-white">{{ $item->kendaraan->plat_nomor }}</div>
                            <div class="text-[11px] text-neutral-500">{{ $item->kendaraan->konsumen->nama_lengkap }}</div>
                        </td>
                        <td class="p-4 text-neutral-300">{{ $item->diagnosa_awal }}</td>
                        <td class="p-4">
                            @if($item->diagnosa_akhir)
                                <span class="font-semibold text-emerald-400">{{ $item->diagnosa_akhir }}</span>
                            @else
                                <span class="text-amber-500/80 italic font-medium">Menunggu cek fisik</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($item->status == 'menunggu_pengerjaan')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    MENUNGGU PENGERJAAN
                                </span>
                            @elseif($item->status == 'siap_cetak_nota')
                                <a href="{{ route('admin.rekam-servis.nota', $item->id) }}" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-sky-500/10 text-sky-400 border border-sky-500/20 hover:bg-sky-500/20 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-receipt"></i>
                                    <span>SIAP CETAK NOTA</span>
                                </a>
                            @elseif($item->status == 'selesai')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    SELESAI
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-neutral-800 text-neutral-400">
                                    {{ $item->status }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right space-x-1">
                            @if($item->status == 'menunggu_pengerjaan')
                                <a href="{{ route('admin.rekam-servis.koreksi-form', $item->id) }}" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-[11px] inline-flex items-center gap-1 shadow-sm transition">
                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                    <span>Koreksi Diagnosa</span>
                                </a>
                            @else
                                <a href="{{ route('admin.rekam-servis.nota', $item->id) }}" class="px-3 py-1.5 bg-neutral-800 hover:bg-neutral-700 text-white font-bold rounded-xl text-[11px] inline-flex items-center gap-1 border border-neutral-700 transition">
                                    <i class="fa-solid fa-file-invoice-dollar text-brand-400"></i>
                                    <span>Kelola Nota & Biaya</span>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-neutral-600">Belum ada riwayat rekam servis.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection