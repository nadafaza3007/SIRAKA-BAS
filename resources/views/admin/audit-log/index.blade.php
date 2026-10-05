@extends('layouts.admin')

@section('title', 'Audit Log Sistem - SIRAKA')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
        <i class="fa-solid fa-shield-halved text-brand-500"></i>
        <span>Audit Log & Jejak Aktivitas Sistem</span>
    </h2>
    <p class="text-xs text-neutral-500">Rekam jejak seluruh operasi krusial (cetak laporan, perubahan master data, pendaftaran, transaksi) per Class Diagram.</p>
</div>

<div class="bg-ink-900 rounded-3xl border border-neutral-800 shadow-sm overflow-hidden text-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-neutral-400">
            <thead class="bg-black/40 border-b border-neutral-800 font-extrabold text-neutral-500 uppercase tracking-wider text-[11px]">
                <tr>
                    <th class="p-4">Waktu</th>
                    <th class="p-4">Aktor Pengguna</th>
                    <th class="p-4">Entitas / Modul</th>
                    <th class="p-4">Aksi / Aktivitas</th>
                    <th class="p-4">Keterangan / Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800/80">
                @forelse($logs as $log)
                    <tr class="hover:bg-neutral-800/40 transition">
                        <td class="p-4 font-mono text-[11px] text-neutral-400 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="p-4 font-bold text-white">
                            {{ $log->aktor }}
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-md bg-neutral-800 border border-neutral-700 text-brand-400 font-bold text-[10px]">
                                {{ $log->entitas }}
                            </span>
                        </td>
                        <td class="p-4 font-semibold text-neutral-200">
                            {{ $log->aksi }}
                        </td>
                        <td class="p-4 text-[11px] text-neutral-400 max-w-xs truncate font-mono">
                            {{ $log->nilai_baru ?? $log->nilai_lama ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-neutral-600">Belum ada catatan log aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="p-4 border-t border-neutral-800">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection

