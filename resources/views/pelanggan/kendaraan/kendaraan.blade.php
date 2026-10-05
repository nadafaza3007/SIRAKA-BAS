@extends('layouts.pelanggan')

@section('title', 'Kendaraan')

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
            Kendaraan Saya
        </p>

        <div class="mt-1 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
                    Kendaraan
                </h1>

                <p class="mt-1 text-sm leading-6 text-slate-500">

                    @if ($kendaraans->isEmpty())

                        Belum ada kendaraan yang terhubung ke akun Anda.

                    @else

                        {{ $kendaraans->count() }}
                        kendaraan terhubung ke akun Anda berdasarkan data pelanggan di bengkel.

                    @endif

                </p>

            </div>

            @if ($kendaraans->isNotEmpty())

                <a
                    href="{{ route('pelanggan.riwayat') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                >
                    <x-icon
                        name="history"
                        class="h-4 w-4"
                    />

                    Semua Riwayat
                </a>

            @endif

        </div>

    </header>



    {{-- =========================================================
        KENDARAAN KOSONG
    ========================================================== --}}
    @if ($kendaraans->isEmpty())

        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">

            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-slate-100 text-slate-400">

                <x-icon
                    name="car"
                    class="h-7 w-7"
                />

            </div>

            <h2 class="mt-4 font-semibold text-slate-900">
                Belum Ada Kendaraan Terhubung
            </h2>

            <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                Kendaraan akan muncul di halaman ini setelah bengkel
                mencatat kendaraan atas nama Anda.
            </p>

        </div>

    @else

        {{-- =========================================================
            DAFTAR KENDARAAN
        ========================================================== --}}
        <div class="mt-8 grid gap-5 md:grid-cols-2">

            @foreach ($kendaraans as $k)

                @php
                    $terakhir = $k->servisTerakhir;
                @endphp

                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    {{-- =================================================
                        INFORMASI KENDARAAN
                    ================================================== --}}
                    <div class="p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div class="min-w-0">

                                <x-plate
                                    :nomor="$k->plat_nomor"
                                    size="lg"
                                />

                                <h2 class="mt-4 text-lg font-semibold text-slate-900">
                                    {{ $k->nama }}
                                </h2>

                                <p class="mt-0.5 text-sm text-slate-500">

                                    @if ($k->tahun)

                                        Tahun {{ $k->tahun }}

                                    @else

                                        Tahun tidak tercatat

                                    @endif

                                </p>

                            </div>


                            <x-status-badge :tone="$k->status_tone">
                                {{ $k->status_label }}
                            </x-status-badge>

                        </div>


                        {{-- =================================================
                            ALASAN PERHATIAN
                        ================================================== --}}
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

                    </div>



                    {{-- =================================================
                        INFORMASI SERVIS
                    ================================================== --}}
                    <dl class="mt-auto grid grid-cols-1 divide-y divide-slate-100 border-t border-slate-100 text-sm sm:grid-cols-3 sm:divide-x sm:divide-y-0">

                        {{-- Servis terakhir --}}
                        <div class="p-4">

                            <dt class="text-xs font-medium text-slate-500">
                                Servis terakhir
                            </dt>

                            <dd class="mt-1 font-semibold text-slate-900">

                                @if ($terakhir)

                                    {{ $terakhir->tanggal_singkat }}

                                @else

                                    -

                                @endif

                            </dd>

                        </div>


                        {{-- Kilometer --}}
                        <div class="p-4">

                            <dt class="text-xs font-medium text-slate-500">
                                Kilometer
                            </dt>

                            <dd class="mt-1 font-semibold tabular-nums text-slate-900">

                                @if ($terakhir && $terakhir->km_akhir !== null)

                                    {{ $km($terakhir->km_akhir) }}

                                @else

                                    -

                                @endif

                            </dd>

                        </div>


                        {{-- Total servis --}}
                        <div class="p-4">

                            <dt class="text-xs font-medium text-slate-500">
                                Total servis
                            </dt>

                            <dd class="mt-1 font-semibold tabular-nums text-slate-900">
                                {{ $k->rekam_servis_count ?? 0 }} kali
                            </dd>

                        </div>

                    </dl>



                    {{-- =================================================
                        TOMBOL AKSI
                    ================================================== --}}
                    <div class="border-t border-slate-100 p-4">

                        <a
                            href="{{ route('pelanggan.kendaraan.riwayat', $k) }}"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500"
                        >

                            <x-icon
                                name="history"
                                class="h-4 w-4"
                            />

                            Lihat Riwayat Servis

                        </a>

                    </div>

                </article>

            @endforeach

        </div>

    @endif

</div>

@endsection