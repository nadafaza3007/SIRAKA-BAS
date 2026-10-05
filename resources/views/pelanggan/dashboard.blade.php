@extends('layouts.pelanggan')

@section('title', 'Dashboard')

@section('content')

@php
    $km = fn ($nilai) => number_format(
        (int) $nilai,
        0,
        ',',
        '.'
    ) . ' km';
@endphp

<div class="mx-auto max-w-6xl">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <header>

        <p class="text-sm text-slate-500">
            {{ now()->locale('id')->translatedFormat('l, d F Y') }}
        </p>

        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
            Halo, {{ $namaDepan }}
        </h1>

        <p class="mt-1 text-slate-500">
            Ringkasan kondisi dan riwayat servis kendaraan Anda.
        </p>

    </header>


    {{-- =========================================================
        RINGKASAN
    ========================================================== --}}
    <section
        aria-label="Ringkasan"
        class="mt-8 grid grid-cols-1 divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:grid-cols-3 sm:divide-x sm:divide-y-0"
    >

        {{-- Total kendaraan --}}
        <div class="p-5">

            <div class="flex items-center gap-2 text-slate-500">

                <x-icon
                    name="car"
                    class="h-4 w-4"
                />

                <p class="text-sm">
                    Kendaraan terdaftar
                </p>

            </div>

            <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900">
                {{ $totalKendaraan }}
            </p>

            <p class="mt-1 text-sm text-slate-400">
                Terhubung ke akun Anda
            </p>

        </div>


        {{-- Servis terakhir --}}
        <div class="p-5">

            <div class="flex items-center gap-2 text-slate-500">

                <x-icon
                    name="history"
                    class="h-4 w-4"
                />

                <p class="text-sm">
                    Servis terakhir
                </p>

            </div>


            @if ($servisTerakhir)

                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ $servisTerakhir->tanggal_label }}
                </p>

                @if ($servisTerakhir->kendaraan)

                    <p class="mt-1 text-sm text-slate-400">

                        {{ $servisTerakhir->kendaraan->nama }}

                        <span class="mx-1">
                            •
                        </span>

                        {{ $servisTerakhir->kendaraan->plat_nomor }}

                    </p>

                @endif

            @else

                <p class="mt-2 text-xl font-bold text-slate-400">
                    Belum ada
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    Belum ada servis tercatat
                </p>

            @endif

        </div>


        {{-- Perlu servis --}}
        <div class="p-5">

            <div class="flex items-center gap-2 text-slate-500">

                <x-icon
                    name="alert"
                    class="h-4 w-4"
                />

                <p class="text-sm">
                    Perlu servis
                </p>

            </div>

            <p
                @class([
                    'mt-2 text-2xl font-bold tabular-nums',
                    'text-amber-600' => $jumlahPerluServis > 0,
                    'text-slate-900' => $jumlahPerluServis === 0,
                ])
            >
                {{ $jumlahPerluServis }}
            </p>

            <p class="mt-1 text-sm text-slate-400">

                {{ $jumlahPerluServis > 0
                    ? 'Kendaraan perlu dijadwalkan servis'
                    : 'Semua kendaraan terpantau baik'
                }}

            </p>

        </div>

    </section>



    {{-- =========================================================
        KONTEN UTAMA
    ========================================================== --}}
    <div class="mt-10 grid gap-10 lg:grid-cols-5">


        {{-- =====================================================
            KENDARAAN PERLU DIPANTAU
        ====================================================== --}}
        <section class="lg:col-span-3">

            <div class="flex items-end justify-between gap-4">

                <div>

                    <h2 class="text-lg font-semibold text-slate-900">
                        Kendaraan yang Perlu Dipantau
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Kendaraan yang perlu servis atau sedang dalam pengerjaan.
                    </p>

                </div>

                @if ($totalKendaraan > 0)

                    <a
                        href="{{ route('pelanggan.kendaraan') }}"
                        class="hidden shrink-0 text-sm font-semibold text-orange-600 transition hover:text-orange-700 sm:block"
                    >
                        Lihat kendaraan
                    </a>

                @endif

            </div>


            <div class="mt-5 space-y-4">

                @forelse ($perluDipantau as $k)

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex flex-wrap items-start justify-between gap-3">

                            <div>

                                <x-plate
                                    :nomor="$k->plat_nomor"
                                />

                                <h3 class="mt-3 font-semibold text-slate-900">
                                    {{ $k->nama_lengkap }}
                                </h3>

                                @if ($k->tahun)

                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Tahun {{ $k->tahun }}
                                    </p>

                                @endif

                            </div>


                            <x-status-badge :tone="$k->status_tone">
                                {{ $k->status_label }}
                            </x-status-badge>

                        </div>


                        @if ($k->alasan_perhatian)

                            <div class="mt-4 flex items-start gap-2 rounded-xl bg-amber-50 px-3 py-2.5">

                                <x-icon
                                    name="alert"
                                    class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                                />

                                <p class="text-sm leading-6 text-amber-800">
                                    {{ $k->alasan_perhatian }}
                                </p>

                            </div>

                        @endif


                        <div class="mt-4 flex flex-wrap items-center gap-3">

                            <a
                                href="{{ route('pelanggan.kendaraan.riwayat', $k) }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500"
                            >

                                <x-icon
                                    name="history"
                                    class="h-4 w-4"
                                />

                                Lihat riwayat servis

                            </a>

                        </div>

                    </article>


                @empty

                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-50">

                            <x-icon
                                name="check"
                                class="h-5 w-5 text-emerald-600"
                            />

                        </div>

                        <div>

                            <p class="font-semibold text-slate-900">
                                Semua kendaraan terpantau baik
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Tidak ada kendaraan yang perlu servis atau sedang
                                dikerjakan saat ini.
                            </p>

                        </div>

                    </div>

                @endforelse

            </div>

        </section>



        {{-- =====================================================
            AKTIVITAS TERBARU
        ====================================================== --}}
        <section class="lg:col-span-2">

            <div class="flex items-baseline justify-between gap-3">

                <h2 class="text-lg font-semibold text-slate-900">
                    Aktivitas Terbaru
                </h2>

                @if ($aktivitas->isNotEmpty())

                    <a
                        href="{{ route('pelanggan.riwayat') }}"
                        class="text-sm font-semibold text-orange-600 transition hover:text-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-500"
                    >
                        Lihat semua
                    </a>

                @endif

            </div>


            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                @if ($aktivitas->isEmpty())

                    <div class="py-4 text-center">

                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400">

                            <x-icon
                                name="history"
                                class="h-5 w-5"
                            />

                        </div>

                        <p class="mt-3 font-semibold text-slate-900">
                            Belum ada aktivitas servis
                        </p>

                        <p class="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">
                            Riwayat akan muncul setelah kendaraan Anda
                            diservis di bengkel.
                        </p>

                    </div>

                @else

                    <ol>

                        @foreach ($aktivitas as $s)

                            <li class="relative pb-6 pl-6 last:pb-0">

                                {{-- Garis timeline --}}
                                @unless ($loop->last)

                                    <span
                                        class="absolute bottom-0 left-[4px] top-4 w-px bg-slate-200"
                                        aria-hidden="true"
                                    ></span>

                                @endunless


                                {{-- Titik timeline --}}
                                <span
                                    @class([
                                        'absolute left-0 top-1.5 h-[9px] w-[9px] rounded-full',
                                        'bg-orange-500' => $loop->first,
                                        'bg-slate-300' => ! $loop->first,
                                    ])
                                    aria-hidden="true"
                                ></span>


                                @if ($s->kendaraan)

                                    <a
                                        href="{{ route(
                                            'pelanggan.kendaraan.riwayat',
                                            $s->kendaraan
                                        ) }}"
                                        class="block rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500"
                                    >

                                        <div class="flex flex-wrap items-center justify-between gap-2">

                                            <p class="text-xs text-slate-400">
                                                {{ $s->tanggal_label }}
                                            </p>

                                            <x-status-badge :tone="$s->status_tone">
                                                {{ $s->status_label }}
                                            </x-status-badge>

                                        </div>


                                        <p class="mt-1 line-clamp-2 text-sm font-medium text-slate-900">
                                            {{ $s->keluhan_awal }}
                                        </p>


                                        <div class="mt-2 flex flex-wrap items-center gap-2">

                                            <x-plate
                                                :nomor="$s->kendaraan->plat_nomor"
                                                size="sm"
                                            />

                                            <span class="text-sm text-slate-500">
                                                {{ $s->kendaraan->nama }}
                                            </span>

                                        </div>

                                    </a>

                                @else

                                    <div>

                                        <div class="flex flex-wrap items-center justify-between gap-2">

                                            <p class="text-xs text-slate-400">
                                                {{ $s->tanggal_label }}
                                            </p>

                                            <x-status-badge :tone="$s->status_tone">
                                                {{ $s->status_label }}
                                            </x-status-badge>

                                        </div>

                                        <p class="mt-1 line-clamp-2 text-sm font-medium text-slate-900">
                                            {{ $s->keluhan_awal }}
                                        </p>

                                    </div>

                                @endif

                            </li>

                        @endforeach

                    </ol>

                @endif

            </div>

        </section>

    </div>



    {{-- =========================================================
        INFORMASI BAWAH
    ========================================================== --}}
    <div class="mt-10 flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

        <x-icon
            name="check"
            class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
        />

        <p class="text-xs leading-5 text-slate-500">
            Data servis dicatat langsung oleh bengkel dan tidak dapat
            diubah melalui akun pelanggan.
        </p>

    </div>

</div>

@endsection