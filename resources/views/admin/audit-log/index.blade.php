@extends('layouts.admin')

@section('title', 'Audit Log Sistem - SIRAKA')

@section('content')

{{-- HEADER --}}
<div class="mb-6">
    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
        <i class="fa-solid fa-shield-halved text-brand-500"></i>
        <span>Audit Log & Jejak Aktivitas Sistem</span>
    </h2>

    <p class="text-xs text-neutral-500">
        Rekam jejak seluruh operasi krusial, termasuk transaksi,
        perubahan data, dan pengiriman dokumen WhatsApp.
    </p>
</div>

{{-- AUDIT LOG TABLE --}}
<div class="bg-ink-900 rounded-3xl border border-neutral-800 shadow-sm overflow-hidden text-xs">

    <div class="overflow-x-auto">

        <table class="w-full min-w-[850px] text-left text-neutral-400">

            {{-- TABLE HEADER --}}
            <thead class="bg-black/40 border-b border-neutral-800
                          font-extrabold text-neutral-500
                          uppercase tracking-wider text-[11px]">

                <tr>
                    <th class="p-4 whitespace-nowrap">
                        Waktu
                    </th>

                    <th class="p-4">
                        Aktor Pengguna
                    </th>

                    <th class="p-4">
                        Entitas / Modul
                    </th>

                    <th class="p-4">
                        Aksi / Aktivitas
                    </th>
                </tr>

            </thead>

            {{-- TABLE BODY --}}
            <tbody class="divide-y divide-neutral-800/80">

                @forelse($logs as $log)

                    @php
                        $isWhatsApp = $log->entitas === 'WhatsApp Kirima';
                    @endphp

                    <tr class="hover:bg-neutral-800/40 transition">

                        {{-- WAKTU --}}
                        <td class="p-4 font-mono text-[11px]
                                   text-neutral-400 whitespace-nowrap">

                            {{ $log->created_at
                                ? $log->created_at->format('d/m/Y H:i:s')
                                : '-' }}

                        </td>

                        {{-- AKTOR --}}
                        <td class="p-4">

                            <div class="font-bold text-white">
                                {{ $log->user?->name
                                    ?? ($log->user_id
                                        ? 'Pengguna #' . $log->user_id
                                        : 'Sistem') }}
                            </div>

                            @if($log->user?->role)
                                <div class="mt-1 text-[10px] text-neutral-500 capitalize">
                                    {{ $log->user->role }}
                                </div>
                            @endif

                        </td>

                        {{-- ENTITAS --}}
                        <td class="p-4">

                            @if($isWhatsApp)

                                <span class="inline-flex items-center gap-1
                                             px-2 py-1 rounded-md
                                             bg-emerald-500/10
                                             border border-emerald-500/20
                                             text-emerald-400
                                             font-bold text-[10px]">

                                    <i class="fa-brands fa-whatsapp"></i>
                                    {{ $log->entitas }}

                                </span>

                            @else

                                <span class="inline-flex items-center
                                             px-2 py-1 rounded-md
                                             bg-neutral-800
                                             border border-neutral-700
                                             text-brand-400
                                             font-bold text-[10px]">

                                    {{ $log->entitas }}

                                </span>

                            @endif

                        </td>

                        {{-- AKSI / AKTIVITAS --}}
                        <td class="p-4">

                            <div class="font-semibold text-neutral-200
                                        leading-relaxed break-words">

                                {{ $log->aksi }}

                            </div>

                            @if($isWhatsApp)

                                <div class="mt-2 flex items-center gap-1
                                            text-[10px] text-emerald-400">

                                    <i class="fa-solid fa-clock"></i>

                                    Aktivitas pengiriman melalui Kirima API

                                </div>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4"
                            class="p-8 text-center text-neutral-600">

                            <i class="fa-solid fa-clipboard-list
                                      text-2xl mb-2 block"></i>

                            Belum ada catatan log aktivitas.

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- PAGINATION --}}
    @if($logs->hasPages())

        <div class="p-4 border-t border-neutral-800">

            {{ $logs->links() }}

        </div>

    @endif

</div>

@endsection