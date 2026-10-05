@extends('layouts.pelanggan')

@section('title', 'Riwayat Servis')

@section('content')

@php
    $rp = fn ($nilai) => 'Rp ' . number_format(
        (float) $nilai,
        0,
        ',',
        '.'
    );

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
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="p-5 sm:p-6">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-600">

                        <x-icon
                            name="history"
                            class="h-4 w-4"
                        />

                        Riwayat Kendaraan

                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
                        Riwayat Servis
                    </h1>

                    <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">

                        @if ($kendaraanAktif)

                            Menampilkan riwayat servis untuk

                            <span class="font-semibold text-slate-700">
                                {{ $kendaraanAktif->nama }}
                            </span>

                            dengan nomor plat

                            <span class="font-semibold text-slate-700">
                                {{ $kendaraanAktif->plat_nomor }}
                            </span>.

                        @elseif (! empty($searchPlat))

                            Hasil pencarian riwayat servis nomor plat

                            <span class="font-semibold text-slate-700">
                                "{{ $searchPlat }}"
                            </span>.

                        @else

                            Lihat seluruh riwayat servis, diagnosa,
                            tindakan mekanik, sparepart, jasa,
                            rekomendasi, dan nota kendaraan Anda.

                        @endif

                    </p>

                </div>


                <div class="shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-5 py-3">

                    <p class="text-xs font-medium text-slate-500">
                        Kendaraan Terdaftar
                    </p>

                    <p class="mt-1 text-2xl font-bold text-slate-900">

                        {{ $totalKendaraan ?? $kendaraans->count() }}

                        <span class="text-xs font-semibold text-orange-500">
                            Unit
                        </span>

                    </p>

                </div>

            </div>


            {{-- =================================================
                PENCARIAN NOMOR PLAT
            ================================================== --}}
            <form
                method="GET"
                action="{{ route('pelanggan.riwayat') }}"
                class="mt-6 flex flex-col gap-2 sm:flex-row"
            >

                <div class="relative flex-1">

                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>

                    </span>

                    <input
                        type="text"
                        name="plat"
                        value="{{ $searchPlat ?? '' }}"
                        placeholder="Cari nomor plat, contoh: BM 1234 ABC"
                        autocomplete="off"
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-semibold uppercase tracking-wide text-slate-800 outline-none transition placeholder:normal-case placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >

                </div>


                @if (! empty($searchPlat))

                    <a
                        href="{{ route('pelanggan.riwayat') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        Reset
                    </a>

                @endif


                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                >
                    Cari
                </button>

            </form>

        </div>

    </div>



    {{-- =========================================================
        FILTER KENDARAAN
    ========================================================== --}}
    @if ($kendaraans->count() > 1 && empty($searchPlat))

        <div class="mt-6">

            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Filter kendaraan
            </p>

            <nav
                class="flex gap-2 overflow-x-auto pb-2"
                aria-label="Filter kendaraan"
            >

                <a
                    href="{{ route('pelanggan.riwayat') }}"
                    @class([
                        'shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition',
                        'border-slate-900 bg-slate-900 text-white' => ! $kendaraanAktif,
                        'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' => $kendaraanAktif,
                    ])
                >
                    Semua kendaraan
                </a>


                @foreach ($kendaraans as $k)

                    @php
                        $dipilih = $kendaraanAktif
                            && (int) $kendaraanAktif->id === (int) $k->id;
                    @endphp

                    <a
                        href="{{ route('pelanggan.kendaraan.riwayat', $k) }}"
                        @class([
                            'shrink-0 rounded-full border px-4 py-2 text-sm font-semibold tabular-nums transition',
                            'border-orange-500 bg-orange-500 text-white' => $dipilih,
                            'border-slate-200 bg-white text-slate-600 hover:border-orange-300 hover:text-orange-600' => ! $dipilih,
                        ])
                    >
                        {{ $k->plat_nomor }}
                    </a>

                @endforeach

            </nav>

        </div>

    @endif



    {{-- =========================================================
        DAFTAR RIWAYAT
    ========================================================== --}}
    <div class="mt-6 space-y-4">

        @forelse ($riwayat as $s)

            @php
                $nota = $s->nota;
                $rekomendasi = $s->catatanRekomendasis;
                $kendaraanServis = $s->kendaraan;
            @endphp

            <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition open:shadow-md">

                {{-- =================================================
                    RINGKASAN
                ================================================== --}}
                <summary class="flex cursor-pointer list-none items-center gap-3 p-4 sm:gap-4 sm:p-5 [&::-webkit-details-marker]:hidden">

                    <div class="hidden h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-500 sm:grid">

                        <x-icon
                            name="car"
                            class="h-5 w-5"
                        />

                    </div>


                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">

                            <p class="font-semibold text-slate-900">
                                {{ $s->tanggal_label }}
                            </p>

                            <x-status-badge :tone="$s->status_tone">
                                {{ $s->status_label }}
                            </x-status-badge>

                        </div>


                        <p class="mt-1.5 line-clamp-1 text-sm text-slate-700">
                            {{ $s->keluhan_awal ?? 'Tidak ada keluhan tercatat' }}
                        </p>


                        @if ($kendaraanServis)

                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-slate-500">

                                <x-plate
                                    :nomor="$kendaraanServis->plat_nomor"
                                    size="sm"
                                />

                                <span>
                                    {{ $kendaraanServis->nama }}
                                </span>

                                @if ($kendaraanServis->tahun)

                                    <span>
                                        Tahun {{ $kendaraanServis->tahun }}
                                    </span>

                                @endif

                                <span class="tabular-nums">
                                    {{ $km($s->km_akhir) }}
                                </span>

                            </div>

                        @endif

                    </div>


                    <div class="hidden shrink-0 text-right sm:block">

                        <p class="font-semibold tabular-nums text-slate-900">
                            {{ $rp($s->total_biaya) }}
                        </p>

                        <p class="text-xs text-slate-400">
                            {{ $nota ? 'Total biaya' : 'Estimasi' }}
                        </p>

                    </div>


                    <x-icon
                        name="chevron"
                        class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200 group-open:rotate-180"
                    />

                </summary>



                {{-- =================================================
                    DETAIL RIWAYAT
                ================================================== --}}
                <div class="border-t border-slate-100 p-4 sm:p-5">


                    {{-- Harga mobile --}}
                    <div class="mb-5 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 sm:hidden">

                        <span class="text-xs text-slate-500">
                            {{ $nota ? 'Total biaya' : 'Estimasi biaya' }}
                        </span>

                        <span class="font-semibold tabular-nums text-slate-900">
                            {{ $rp($s->total_biaya) }}
                        </span>

                    </div>


                    <div class="grid gap-8 lg:grid-cols-2">


                        {{-- =========================================
                            INFORMASI SERVIS
                        ========================================== --}}
                        <section>

                            <h3 class="flex items-center gap-2 font-semibold text-slate-900">

                                <span class="grid h-7 w-7 place-items-center rounded-lg bg-orange-50 text-orange-500">

                                    <x-icon
                                        name="history"
                                        class="h-4 w-4"
                                    />

                                </span>

                                Informasi Servis

                            </h3>


                            <dl class="mt-4 space-y-4 text-sm">

                                <div>

                                    <dt class="text-xs font-medium text-slate-500">
                                        Keluhan awal
                                    </dt>

                                    <dd class="mt-1 leading-6 text-slate-900">
                                        {{ $s->keluhan_awal ?? '-' }}
                                    </dd>

                                </div>


                                @if ($s->diagnosa_awal)

                                    <div>

                                        <dt class="text-xs font-medium text-slate-500">
                                            Diagnosa awal
                                        </dt>

                                        <dd class="mt-1 leading-6 text-slate-900">
                                            {{ $s->diagnosa_awal }}
                                        </dd>

                                    </div>

                                @endif


                                <div>

                                    <dt class="text-xs font-medium text-slate-500">
                                        Diagnosa akhir
                                    </dt>

                                    <dd class="mt-1 leading-6 text-slate-900">
                                        {{ $s->diagnosa_akhir ?? 'Belum ada diagnosa akhir' }}
                                    </dd>

                                </div>


                                <div>
                                    <dt class="text-xs font-medium text-slate-500">
                                        Tindakan servis
                                    </dt>

                                    <dd class="mt-1 whitespace-pre-line leading-6 text-slate-900">{{ $s->tindakan_servis ?? 'Belum ada tindakan tercatat' }}</dd>
                                </div>


                                @if ($s->user)

                                    <div>

                                        <dt class="text-xs font-medium text-slate-500">
                                            Ditangani oleh
                                        </dt>

                                        <dd class="mt-1 font-medium text-slate-900">
                                            {{ $s->user->name }}
                                        </dd>

                                    </div>

                                @endif


                                <div>

                                    <dt class="text-xs font-medium text-slate-500">
                                        Kilometer
                                    </dt>

                                    <dd class="mt-1 font-medium tabular-nums text-slate-900">
                                        {{ $km($s->km_akhir) }}
                                    </dd>

                                </div>

                            </dl>

                        </section>



                        {{-- =========================================
                            RINCIAN BIAYA
                        ========================================== --}}
                        <section>

                            <h3 class="font-semibold text-slate-900">
                                Rincian Biaya
                            </h3>


                            @if (
                                $s->detailJasas->isEmpty()
                                && $s->detailSpareparts->isEmpty()
                            )

                                <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4">

                                    <p class="text-sm text-slate-500">
                                        Rincian biaya belum tersedia.
                                    </p>

                                </div>

                            @else

                                <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">

                                    <ul class="divide-y divide-slate-100">

                                        {{-- Jasa --}}
                                        @foreach ($s->detailJasas as $d)

                                            <li class="flex items-start justify-between gap-4 px-4 py-3 text-sm">

                                                <div class="min-w-0">

                                                    <p class="text-slate-700">
                                                        {{ $d->jasa->nama_jasa ?? 'Jasa servis' }}
                                                    </p>

                                                    <span class="text-xs text-slate-400">
                                                        Jasa
                                                    </span>

                                                </div>

                                                <span class="shrink-0 font-medium tabular-nums text-slate-900">
                                                    {{ $rp($d->harga_saat_transaksi) }}
                                                </span>

                                            </li>

                                        @endforeach


                                        {{-- Sparepart --}}
                                        @foreach ($s->detailSpareparts as $d)

                                            <li class="flex items-start justify-between gap-4 px-4 py-3 text-sm">

                                                <div class="min-w-0">

                                                    <p class="text-slate-700">
                                                        {{ $d->sparepart->nama_barang ?? 'Sparepart' }}
                                                    </p>

                                                    <span class="text-xs text-slate-400">

                                                        {{ $d->qty }}
                                                        ×
                                                        {{ $rp($d->harga_jual_saat_transaksi) }}

                                                    </span>

                                                </div>

                                                <span class="shrink-0 font-medium tabular-nums text-slate-900">

                                                    {{ $rp(
                                                        $d->qty
                                                        * $d->harga_jual_saat_transaksi
                                                    ) }}

                                                </span>

                                            </li>

                                        @endforeach

                                    </ul>

                                </div>

                            @endif



                            {{-- =========================================
                                NOTA
                            ========================================== --}}
                            @if ($nota)

                                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">

                                    <div class="mb-3 flex items-center justify-between gap-3">

                                        <div>

                                            <p class="text-sm font-semibold text-slate-900">
                                                Nota {{ $nota->no_nota }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $nota->tanggal_label }}
                                            </p>

                                        </div>


                                        <x-status-badge
                                            :tone="$nota->lunas ? 'green' : 'red'"
                                        >
                                            {{ $nota->lunas ? 'Lunas' : 'Belum lunas' }}
                                        </x-status-badge>

                                    </div>


                                    <dl class="space-y-2 border-t border-slate-200 pt-3 text-sm">

                                        <div class="flex justify-between gap-3 text-slate-600">

                                            <dt>
                                                Subtotal
                                            </dt>

                                            <dd class="tabular-nums">
                                                {{ $rp($nota->subtotal) }}
                                            </dd>

                                        </div>


                                        @if ((float) $nota->diskon > 0)

                                            <div class="flex justify-between gap-3 text-slate-600">

                                                <dt>
                                                    Diskon
                                                </dt>

                                                <dd class="tabular-nums text-emerald-600">
                                                    - {{ $rp($nota->diskon) }}
                                                </dd>

                                            </div>

                                        @endif


                                        <div class="flex justify-between gap-3 border-t border-slate-200 pt-2 font-semibold text-slate-900">

                                            <dt>
                                                Total
                                            </dt>

                                            <dd class="text-base tabular-nums">
                                                {{ $rp($nota->total_biaya) }}
                                            </dd>

                                        </div>

                                    </dl>

                                </div>

                            @else

                                <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-700">

                                    Nota belum diterbitkan.
                                    Total biaya yang ditampilkan merupakan
                                    estimasi berdasarkan jasa dan sparepart
                                    yang sudah tercatat.

                                </div>

                            @endif

                        </section>

                    </div>



                    {{-- =================================================
                        REKOMENDASI BENGKEL
                    ================================================== --}}
                    @if ($rekomendasi->isNotEmpty())

                        <section class="mt-8 border-t border-slate-100 pt-6">

                            <div class="flex items-center gap-2">

                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-50 text-amber-600">

                                    <x-icon
                                        name="alert"
                                        class="h-4 w-4"
                                    />

                                </span>

                                <div>

                                    <h3 class="font-semibold text-slate-900">
                                        Rekomendasi dari Bengkel
                                    </h3>

                                    <p class="text-xs text-slate-500">
                                        Catatan untuk perawatan atau servis berikutnya.
                                    </p>

                                </div>

                            </div>


                            <ul class="mt-4 space-y-2">

                                @foreach ($rekomendasi as $r)

                                    @php
                                        $statusKonfirmasi =
                                            $r->status_konfirmasi ?? 'belum';

                                        $sudahDikerjakan =
                                            $statusKonfirmasi === 'sudah';
                                    @endphp

                                    <li class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50/50 px-4 py-3">

                                        <div class="flex min-w-0 flex-1 items-start gap-3">

                                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>

                                            <p class="text-sm leading-6 text-slate-700">
                                                {{ $r->catatan }}
                                            </p>

                                        </div>


                                        <x-status-badge
                                            :tone="$sudahDikerjakan ? 'green' : 'amber'"
                                        >
                                            {{ $sudahDikerjakan
                                                ? 'Sudah Dikerjakan'
                                                : 'Perlu Perhatian'
                                            }}
                                        </x-status-badge>

                                    </li>

                                @endforeach

                            </ul>

                        </section>

                    @endif



                    {{-- =================================================
                        TOMBOL DETAIL
                    ================================================== --}}
                    @if (Route::has('pelanggan.detail-servis'))

                        <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">

                            <a
                                href="{{ route('pelanggan.detail-servis', $s->id) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                            >

                                Lihat Rincian & Nota

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>

                            </a>

                        </div>

                    @endif

                </div>

            </details>


        @empty

            {{-- =================================================
                RIWAYAT KOSONG
            ================================================== --}}
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">

                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-slate-100 text-slate-400">

                    <x-icon
                        name="history"
                        class="h-7 w-7"
                    />

                </div>


                @if (! empty($searchPlat))

                    <h3 class="mt-4 font-semibold text-slate-900">
                        Riwayat Tidak Ditemukan
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">

                        Tidak ditemukan riwayat servis untuk nomor
                        plat "{{ $searchPlat }}".

                    </p>

                    <a
                        href="{{ route('pelanggan.riwayat') }}"
                        class="mt-4 inline-flex rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600"
                    >
                        Tampilkan Semua Riwayat
                    </a>

                @elseif ($kendaraanAktif)

                    <h3 class="mt-4 font-semibold text-slate-900">
                        Belum Ada Riwayat Servis
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">

                        Belum ada riwayat servis yang tercatat untuk
                        kendaraan {{ $kendaraanAktif->plat_nomor }}.

                    </p>

                    <a
                        href="{{ route('pelanggan.riwayat') }}"
                        class="mt-4 inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Lihat Semua Kendaraan
                    </a>

                @else

                    <h3 class="mt-4 font-semibold text-slate-900">
                        Belum Ada Riwayat Servis
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">

                        Riwayat servis akan muncul setelah kendaraan
                        Anda tercatat melakukan servis di bengkel.

                    </p>

                @endif

            </div>

        @endforelse

    </div>



    {{-- =========================================================
        PAGINATION
    ========================================================== --}}
    @if ($riwayat->hasPages())

        <div class="mt-6">
            {{ $riwayat->links() }}
        </div>

    @endif

</div>

@endsection