@extends('layouts.pelanggan')

@section('title', 'Kendaraan')

@section('content')

@php
    $km = fn ($nilai) => number_format((int) $nilai, 0, ',', '.') . ' km';
@endphp

<div class="space-y-6 sm:space-y-7">

    {{-- ===== Header halaman ===== --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <span class="inline-flex items-center gap-2 rounded-full bg-brandbg px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-brandfg">
                <x-icon name="car" class="h-3.5 w-3.5" />
                Kendaraan Saya
            </span>
            <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-main sm:text-3xl">Kendaraan</h1>
            <p class="mt-1.5 max-w-2xl text-sm leading-6 text-muted">
                @if ($kendaraans->isEmpty())
                    Belum ada kendaraan yang terhubung ke akun Anda.
                @else
                    {{ $kendaraans->count() }} kendaraan terhubung ke akun Anda berdasarkan data pelanggan di bengkel.
                @endif
            </p>
        </div>

        @if ($kendaraans->isNotEmpty())
            <a href="{{ route('pelanggan.riwayat') }}"
               class="inline-flex min-h-11 w-fit shrink-0 items-center justify-center gap-2 rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-bold text-main shadow-sm transition hover:border-brand-500/50 hover:text-brandfg">
                <x-icon name="history" class="h-4 w-4" />
                Semua Riwayat
            </a>
        @endif
    </header>

    @if ($kendaraans->isEmpty())

        {{-- ===== Kosong ===== --}}
        <div class="rounded-2xl border border-dashed border-line bg-surface px-6 py-12 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-surface2 text-soft">
                <x-icon name="car" class="h-7 w-7" />
            </div>
            <h2 class="mt-4 font-bold text-main">Belum Ada Kendaraan Terhubung</h2>
            <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-muted">
                Kendaraan akan muncul di halaman ini setelah bengkel mencatat kendaraan atas nama Anda.
            </p>
        </div>

    @else

        {{-- ===== Daftar kendaraan: kartu setinggi sama ===== --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach ($kendaraans as $k)
                @php $terakhir = $k->servisTerakhir; @endphp

                <article class="flex h-full flex-col rounded-2xl border border-line bg-surface shadow-sm transition hover:-translate-y-0.5 hover:border-brand-500/40 hover:shadow-soft">

                    {{-- Identitas kendaraan --}}
                    <div class="flex-1 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <x-plate :nomor="$k->plat_nomor" size="lg" />
                                <h2 class="mt-4 text-lg font-extrabold tracking-tight text-main">{{ $k->nama }}</h2>
                                <p class="mt-0.5 text-sm text-muted">
                                    {{ $k->tahun ? 'Tahun ' . $k->tahun : 'Tahun tidak tercatat' }}
                                </p>
                            </div>
                            <x-status-badge :tone="$k->status_tone">{{ $k->status_label }}</x-status-badge>
                        </div>

                        @if ($k->alasan_perhatian)
                            <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-warnline bg-warnbg px-3.5 py-3">
                                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-warnfg" />
                                <p class="text-sm leading-6 text-warnfg">{{ $k->alasan_perhatian }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Ringkasan servis --}}
                    <dl class="mx-5 grid grid-cols-3 divide-x divide-line rounded-xl bg-surface2 sm:mx-6">
                        <div class="min-w-0 px-3 py-3 sm:px-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Servis terakhir</dt>
                            <dd class="mt-1 text-sm font-bold text-main">{{ $terakhir ? $terakhir->tanggal_singkat : '-' }}</dd>
                        </div>
                        <div class="min-w-0 px-3 py-3 sm:px-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Kilometer</dt>
                            <dd class="mt-1 text-sm font-bold tabular-nums text-main">
                                {{ ($terakhir && $terakhir->km_akhir !== null) ? $km($terakhir->km_akhir) : '-' }}
                            </dd>
                        </div>
                        <div class="min-w-0 px-3 py-3 sm:px-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Total servis</dt>
                            <dd class="mt-1 text-sm font-bold tabular-nums text-main">{{ $k->rekam_servis_count ?? 0 }} kali</dd>
                        </div>
                    </dl>

                    {{-- Aksi --}}
                    <div class="p-5 pt-4 sm:p-6 sm:pt-4">
                        <a href="{{ route('pelanggan.kendaraan.riwayat', $k) }}"
                           class="flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
                            <x-icon name="history" class="h-4 w-4" />
                            Lihat Riwayat Servis
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

    @endif
</div>

@endsection