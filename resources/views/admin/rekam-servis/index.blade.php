@extends('layouts.admin')

@section('title', 'Daftar Rekam Servis - SIRAKA')

@section('content')

{{-- =========================================================
    NOTIFIKASI
========================================================== --}}
@if(session('success'))
    <div class="mb-6 flex items-center justify-between rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-bold text-emerald-400 shadow-lg">

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>

        <button
            type="button"
            onclick="this.parentElement.remove()"
            class="text-emerald-400 transition hover:text-white"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>
@endif


@if(session('error'))
    <div class="mb-6 flex items-center justify-between rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-bold text-rose-400 shadow-lg">

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>

        <button
            type="button"
            onclick="this.parentElement.remove()"
            class="text-rose-400 transition hover:text-white"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>
@endif



{{-- =========================================================
    HEADER
========================================================== --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div>
        <h2 class="text-xl font-extrabold tracking-tight text-white">
            Daftar Rekam Servis
        </h2>

        <p class="mt-1 text-xs text-neutral-500">
            Pantau proses pengerjaan servis, koreksi diagnosa fisik mekanik,
            dan kelola rincian nota.
        </p>
    </div>


    <a
        href="{{ route('admin.rekam-servis.create') }}"
        class="flex w-fit items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600"
    >
        <i class="fa-solid fa-plus"></i>
        <span>Input Servis Baru</span>
    </a>

</div>



{{-- =========================================================
    TABEL REKAM SERVIS
========================================================== --}}
<div class="overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">

    <div class="overflow-x-auto">

        <table class="w-full min-w-[1050px] text-left text-xs text-neutral-400">

            <thead class="border-b border-neutral-800 bg-black/40 font-bold uppercase text-neutral-500">

                <tr>

                    <th class="w-[120px] p-4">
                        Tanggal
                    </th>

                    <th class="min-w-[180px] p-4">
                        Kendaraan
                    </th>

                    <th class="min-w-[220px] p-4">
                        Diagnosa Awal
                    </th>

                    <th class="min-w-[220px] p-4">
                        Diagnosa Akhir
                    </th>

                    <th class="min-w-[190px] p-4 text-center">
                        Status
                    </th>

                    <th class="min-w-[190px] p-4 text-right">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-neutral-800">

                @forelse ($servis as $item)

                    @php
                        $status = $item->status;

                        $statusLabel = match ($status) {
                            'menunggu_pengerjaan' => 'Menunggu Pengerjaan',
                            'sedang_dikerjakan'    => 'Sedang Dikerjakan',
                            'diproses'             => 'Sedang Diproses',
                            'siap_cetak_nota'      => 'Siap Cetak Nota',
                            'selesai'              => 'Selesai',
                            default                => ucwords(str_replace('_', ' ', $status)),
                        };

                        $statusClass = match ($status) {
                            'menunggu_pengerjaan'
                                => 'bg-amber-500/10 text-amber-400 border-amber-500/20',

                            'sedang_dikerjakan',
                            'diproses'
                                => 'bg-blue-500/10 text-blue-400 border-blue-500/20',

                            'siap_cetak_nota'
                                => 'bg-sky-500/10 text-sky-400 border-sky-500/20',

                            'selesai'
                                => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',

                            default
                                => 'bg-neutral-800 text-neutral-400 border-neutral-700',
                        };

                        $statusIcon = match ($status) {
                            'menunggu_pengerjaan' => 'fa-clock',
                            'sedang_dikerjakan'    => 'fa-screwdriver-wrench',
                            'diproses'             => 'fa-gears',
                            'siap_cetak_nota'      => 'fa-receipt',
                            'selesai'              => 'fa-circle-check',
                            default                => 'fa-circle-info',
                        };
                    @endphp


                    <tr class="transition hover:bg-neutral-800/40">

                        {{-- =========================================
                            TANGGAL
                        ========================================== --}}
                        <td class="p-4 align-middle font-mono text-neutral-300">

                            {{ \Carbon\Carbon::parse($item->tanggal_servis)->format('d/m/Y') }}

                        </td>



                        {{-- =========================================
                            KENDARAAN
                        ========================================== --}}
                        <td class="p-4 align-middle">

                            <div class="font-extrabold uppercase tracking-wide text-white">
                                {{ $item->kendaraan->plat_nomor }}
                            </div>

                            <div class="mt-1 text-[11px] text-neutral-500">
                                {{ $item->kendaraan->konsumen->nama_lengkap }}
                            </div>

                            <div class="mt-0.5 text-[10px] text-neutral-600">
                                {{ $item->kendaraan->merk }}
                                {{ $item->kendaraan->tipe_model }}
                            </div>

                        </td>



                        {{-- =========================================
                            DIAGNOSA AWAL
                        ========================================== --}}
                        <td class="p-4 align-middle">

                            <p class="leading-5 text-neutral-300">
                                {{ $item->diagnosa_awal }}
                            </p>

                        </td>



                        {{-- =========================================
                            DIAGNOSA AKHIR
                        ========================================== --}}
                        <td class="p-4 align-middle">

                            @if($item->diagnosa_akhir)

                                <div class="flex items-start gap-2">

                                    <i class="fa-solid fa-circle-check mt-0.5 text-[10px] text-emerald-400"></i>

                                    <span class="font-semibold leading-5 text-emerald-400">
                                        {{ $item->diagnosa_akhir }}
                                    </span>

                                </div>

                            @else

                                <div class="inline-flex items-center gap-1.5 text-amber-500/80">

                                    <i class="fa-solid fa-clock text-[10px]"></i>

                                    <span class="italic font-medium">
                                        Menunggu cek fisik
                                    </span>

                                </div>

                            @endif

                        </td>



                        {{-- =========================================
                            STATUS
                        ========================================== --}}
                        <td class="p-4 align-middle text-center">

                            @if($status === 'siap_cetak_nota')

                                <a
                                    href="{{ route('admin.rekam-servis.nota', $item->id) }}"
                                    class="inline-flex whitespace-nowrap items-center justify-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide transition hover:bg-sky-500/20 {{ $statusClass }}"
                                >
                                    <i class="fa-solid {{ $statusIcon }}"></i>

                                    <span>
                                        {{ $statusLabel }}
                                    </span>
                                </a>

                            @else

                                <span
                                    class="inline-flex whitespace-nowrap items-center justify-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide {{ $statusClass }}"
                                >
                                    <i class="fa-solid {{ $statusIcon }}"></i>

                                    <span>
                                        {{ $statusLabel }}
                                    </span>
                                </span>

                            @endif

                        </td>



                        {{-- =========================================
                            AKSI
                        ========================================== --}}
                        <td class="p-4 align-middle text-right">

                            @if($status === 'menunggu_pengerjaan')

                                <a
                                    href="{{ route('admin.rekam-servis.koreksi-form', $item->id) }}"
                                    class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-xl bg-brand-500 px-3 py-2 text-[11px] font-bold text-white shadow-sm transition hover:bg-brand-600"
                                >
                                    <i class="fa-solid fa-screwdriver-wrench"></i>

                                    <span>
                                        Koreksi Diagnosa
                                    </span>
                                </a>

                            @elseif($status === 'selesai')

                                <a
                                    href="{{ route('admin.rekam-servis.nota', $item->id) }}"
                                    class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-xl border border-neutral-700 bg-neutral-800 px-3 py-2 text-[11px] font-bold text-neutral-300 transition hover:bg-neutral-700 hover:text-white"
                                >
                                    <i class="fa-solid fa-eye text-emerald-400"></i>

                                    <span>
                                        Lihat Nota
                                    </span>
                                </a>

                            @else

                                <a
                                    href="{{ route('admin.rekam-servis.nota', $item->id) }}"
                                    class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-xl border border-neutral-700 bg-neutral-800 px-3 py-2 text-[11px] font-bold text-white transition hover:bg-neutral-700"
                                >
                                    <i class="fa-solid fa-file-invoice-dollar text-brand-400"></i>

                                    <span>
                                        Kelola Nota & Biaya
                                    </span>
                                </a>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="p-10 text-center"
                        >

                            <div class="mx-auto flex max-w-sm flex-col items-center">

                                <div class="grid h-12 w-12 place-items-center rounded-full bg-neutral-800 text-neutral-600">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                </div>

                                <p class="mt-3 font-bold text-neutral-400">
                                    Belum ada rekam servis
                                </p>

                                <p class="mt-1 text-[11px] text-neutral-600">
                                    Rekam servis baru akan muncul di halaman ini.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection