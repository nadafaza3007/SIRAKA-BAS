@extends('layouts.pelanggan')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-6 sm:space-y-7">

    {{-- ===== Sapaan utama (gelap di kedua tema, selaras topbar admin) ===== --}}
    <section class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 via-brand-500 to-brand-600 px-5 py-6 text-white shadow-soft sm:px-7 sm:py-7 dark:from-brand-600 dark:via-brand-600 dark:to-brand-700">
        <div class="pointer-events-none absolute -right-10 -top-24 h-64 w-64 rounded-full bg-white/15 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 right-1/3 h-56 w-56 rounded-full bg-brand-700/30 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/80">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Halo, <span class="font-accent text-[1.15em] font-normal">{{ $namaDepan }}</span></h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-white/85">Pantau kendaraan, catatan rekomendasi, dan perkembangan servis Anda dalam satu tempat.</p>
            </div>
            <a href="{{ route('pelanggan.riwayat') }}" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full bg-white px-4 py-2.5 text-xs font-bold text-brand-700 shadow-sm transition hover:bg-brand-50">
                <x-icon name="history" class="h-4 w-4" />
                Lihat riwayat servis
            </a>
        </div>
    </section>

    {{-- ===== Ringkasan: 3 kartu setara tinggi ===== --}}
    <section aria-label="Ringkasan akun dan kendaraan" class="grid grid-cols-1 gap-4 md:grid-cols-3">

        {{-- Kendaraan terdaftar --}}
        <article class="flex h-full flex-col rounded-2xl border border-line bg-surface p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-soft">
            <div class="flex flex-1 items-start gap-4">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-brandbg text-brandfg">
                    <x-icon name="car" class="h-6 w-6" />
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Kendaraan terdaftar</p>
                    <p class="mt-1 text-3xl font-extrabold leading-tight tracking-tight text-main tabular-nums">{{ $totalKendaraan }}</p>
                    <p class="mt-0.5 text-xs text-muted">Terhubung ke akun Anda</p>
                </div>
            </div>
            <a href="{{ route('pelanggan.kendaraan') }}" class="mt-4 inline-flex items-center gap-1.5 border-t border-line pt-4 text-xs font-bold text-brandfg transition hover:text-brand-500">
                Lihat kendaraan <span aria-hidden="true">→</span>
            </a>
        </article>

        {{-- Servis terakhir --}}
        <article class="flex h-full flex-col rounded-2xl border border-line bg-surface p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-soft">
            <div class="flex flex-1 items-start gap-4">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-infobg text-infofg">
                    <x-icon name="history" class="h-6 w-6" />
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Servis terakhir</p>
                    @if ($servisTerakhir)
                        <p class="mt-1 text-2xl font-extrabold leading-tight tracking-tight text-main">{{ $servisTerakhir->tanggal_label }}</p>
                        @if ($servisTerakhir->kendaraan)
                            <p class="mt-0.5 truncate text-xs text-muted">{{ $servisTerakhir->kendaraan->nama }} <span class="mx-1">•</span> {{ $servisTerakhir->kendaraan->plat_nomor }}</p>
                        @else
                            <p class="mt-0.5 text-xs text-muted">Informasi kendaraan tidak tersedia</p>
                        @endif
                    @else
                        <p class="mt-1 text-2xl font-extrabold leading-tight tracking-tight text-soft">Belum ada</p>
                        <p class="mt-0.5 text-xs text-muted">Belum ada servis tercatat</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('pelanggan.riwayat') }}" class="mt-4 inline-flex items-center gap-1.5 border-t border-line pt-4 text-xs font-bold text-brandfg transition hover:text-brand-500">
                Buka riwayat <span aria-hidden="true">→</span>
            </a>
        </article>

        {{-- Perlu perhatian --}}
        <article class="flex h-full flex-col rounded-2xl border border-line bg-surface p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-soft">
            <div class="flex flex-1 items-start gap-4">
                <span @class([
                    'grid h-12 w-12 shrink-0 place-items-center rounded-xl',
                    'bg-warnbg text-warnfg' => $jumlahPerluServis > 0,
                    'bg-okbg text-okfg' => $jumlahPerluServis === 0,
                ])>
                    <x-icon :name="$jumlahPerluServis > 0 ? 'alert' : 'check'" class="h-6 w-6" />
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Perlu perhatian</p>
                    <p @class([
                        'mt-1 text-3xl font-extrabold leading-tight tracking-tight tabular-nums',
                        'text-warnfg' => $jumlahPerluServis > 0,
                        'text-main' => $jumlahPerluServis === 0,
                    ])>{{ $jumlahPerluServis }}</p>
                    <p class="mt-0.5 text-xs text-muted">
                        {{ $jumlahPerluServis > 0 ? 'Kendaraan memerlukan servis berkala' : 'Tidak ada pengingat servis berkala' }}
                    </p>
                </div>
            </div>
            <p class="mt-4 border-t border-line pt-4 text-xs font-medium text-soft">Berdasarkan pengingat servis kendaraan</p>
        </article>
    </section>

    {{-- ===== Rekomendasi bengkel ===== --}}
    <section class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm">
        <div class="flex flex-col gap-3 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brandbg text-brandfg">
                    <x-icon name="history" class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-base font-extrabold tracking-tight text-main">Catatan Rekomendasi Bengkel</h2>
                    <p class="mt-0.5 text-xs leading-5 text-muted">Saran perawatan atau perbaikan dari kunjungan servis sebelumnya.</p>
                </div>
            </div>
            <a href="{{ route('pelanggan.riwayat') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-brandfg transition hover:text-brand-500">
                Lihat riwayat <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-3 p-5 sm:p-6 {{ ($rekomendasiTerbaru ?? collect())->count() > 1 ? 'lg:grid-cols-2' : '' }}">
            @forelse(($rekomendasiTerbaru ?? collect()) as $r)
                <article class="flex h-full flex-col rounded-xl border border-line border-l-4 border-l-brand-500 bg-surface2 p-4 sm:p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-main">{{ $r->merk }} {{ $r->tipe_model }}</p>
                            <p class="mt-1 text-xs font-semibold text-muted">{{ $r->plat_nomor }}</p>
                        </div>
                        <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full border border-line bg-surface px-2.5 py-1 text-[11px] font-semibold text-muted">
                            <x-icon name="history" class="h-3 w-3" />
                            {{ \Carbon\Carbon::parse($r->tanggal_servis)->format('d/m/Y') }}
                        </span>
                    </div>
                    <p class="mt-3 flex-1 whitespace-pre-line text-sm leading-6 text-main/90">{{ $r->catatan_nota }}</p>
                    <a href="{{ route('pelanggan.detail-servis', $r->servis_id) }}" class="mt-3 inline-flex w-fit items-center gap-1.5 text-xs font-bold text-brandfg transition hover:text-brand-500">
                        Lihat detail servis <span aria-hidden="true">→</span>
                    </a>
                </article>
            @empty
                <div class="flex items-center gap-3 rounded-xl bg-surface2 px-4 py-4">
                    <x-icon name="check" class="h-4 w-4 shrink-0 text-soft" />
                    <p class="text-sm leading-6 text-muted">Tidak ada rekomendasi yang masih terbuka.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ===== Kendaraan & aktivitas: dua kolom setara tinggi ===== --}}
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

        {{-- Kendaraan yang perlu dipantau --}}
        <section class="flex min-w-0 flex-col lg:col-span-7">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-extrabold tracking-tight text-main">Kendaraan yang Perlu Dipantau</h2>
                    <p class="mt-1 text-sm leading-5 text-muted">Pengingat servis berkala atau kendaraan yang sedang dikerjakan.</p>
                </div>
                @if ($totalKendaraan > 0)
                    <a href="{{ route('pelanggan.kendaraan') }}" class="hidden shrink-0 items-center gap-1 text-xs font-bold text-brandfg transition hover:text-brand-500 sm:inline-flex">
                        Semua kendaraan <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>

            <div class="flex-1 space-y-4">
                @forelse ($perluDipantau as $k)
                    <article class="rounded-2xl border border-line bg-surface p-5 shadow-sm transition hover:border-brand-500/40 hover:shadow-soft sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <x-plate :nomor="$k->plat_nomor" />
                                <h3 class="mt-3 text-base font-bold text-main">{{ $k->nama_lengkap }}</h3>
                                @if ($k->tahun)
                                    <p class="mt-1 text-xs text-muted">Tahun {{ $k->tahun }}</p>
                                @endif
                            </div>
                            <x-status-badge :tone="$k->status_tone">{{ $k->status_label }}</x-status-badge>
                        </div>

                        @if ($k->alasan_perhatian)
                            <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-warnline bg-warnbg px-3.5 py-3">
                                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-warnfg" />
                                <p class="text-sm leading-6 text-warnfg">{{ $k->alasan_perhatian }}</p>
                            </div>
                        @endif

                        <div class="mt-5 border-t border-line pt-4">
                            <a href="{{ route('pelanggan.kendaraan.riwayat', $k) }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
                                <x-icon name="history" class="h-4 w-4" />
                                Lihat riwayat servis
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="flex items-center rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6">
                        <div class="flex items-start gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-okbg text-okfg">
                                <x-icon name="check" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-bold text-main">Tidak ada kendaraan yang perlu perhatian.</p>
                                <p class="mt-1 text-sm leading-6 text-muted">Saat ini tidak ada pengingat servis berkala atau kendaraan yang sedang dikerjakan.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Aktivitas terbaru --}}
        <section class="flex min-w-0 flex-col lg:col-span-5">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-extrabold tracking-tight text-main">Aktivitas Terbaru</h2>
                    <p class="mt-1 text-sm leading-5 text-muted">Perkembangan kunjungan servis Anda.</p>
                </div>
                @if ($aktivitas->isNotEmpty())
                    <a href="{{ route('pelanggan.riwayat') }}" class="shrink-0 text-xs font-bold text-brandfg transition hover:text-brand-500">Lihat semua</a>
                @endif
            </div>

            <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6">
                @if ($aktivitas->isEmpty())
                    <div class="flex flex-col items-center justify-center py-4 text-center">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-surface2 text-soft">
                            <x-icon name="history" class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm font-bold text-main">Belum ada aktivitas servis</p>
                        <p class="mx-auto mt-1 max-w-xs text-xs leading-5 text-muted">Riwayat akan muncul setelah kendaraan Anda diservis di bengkel.</p>
                    </div>
                @else
                    <ol>
                        @foreach ($aktivitas as $s)
                            <li class="relative pb-6 pl-7 last:pb-0">
                                @unless ($loop->last)
                                    <span class="absolute bottom-0 left-[5px] top-3 w-px bg-line" aria-hidden="true"></span>
                                @endunless
                                <span @class([
                                    'absolute left-0 top-1.5 h-3 w-3 rounded-full ring-4 ring-surface',
                                    'bg-brand-500' => $loop->first,
                                    'bg-soft' => ! $loop->first,
                                ]) aria-hidden="true"></span>

                                @if ($s->kendaraan)
                                    <a href="{{ route('pelanggan.kendaraan.riwayat', $s->kendaraan) }}" class="group block rounded-lg">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-[11px] font-semibold text-soft">{{ $s->tanggal_label }}</p>
                                            <x-status-badge :tone="$s->status_tone">{{ $s->status_label }}</x-status-badge>
                                        </div>
                                        <p class="mt-2 line-clamp-2 text-sm font-semibold leading-5 text-main transition group-hover:text-brand-500">{{ $s->keluhan_awal }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <x-plate :nomor="$s->kendaraan->plat_nomor" size="sm" />
                                            <span class="text-xs text-muted">{{ $s->kendaraan->nama }}</span>
                                        </div>
                                    </a>
                                @else
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-[11px] font-semibold text-soft">{{ $s->tanggal_label }}</p>
                                            <x-status-badge :tone="$s->status_tone">{{ $s->status_label }}</x-status-badge>
                                        </div>
                                        <p class="mt-2 line-clamp-2 text-sm font-semibold leading-5 text-main">{{ $s->keluhan_awal }}</p>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    </div>

    {{-- ===== Catatan transparansi ===== --}}
    <div class="flex items-start gap-3 rounded-xl border border-line bg-surface2 px-4 py-3.5">
        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-soft" />
        <p class="text-xs leading-5 text-muted">Data servis dicatat langsung oleh bengkel dan tidak dapat diubah melalui akun pelanggan.</p>
    </div>
</div>
@endsection