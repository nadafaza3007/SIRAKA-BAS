@extends('layouts.pelanggan')

@section('title', 'Riwayat Servis')

@section('content')

@php
    $rp = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');
    $km = fn ($nilai) => number_format((int) $nilai, 0, ',', '.') . ' km';
@endphp

<div class="space-y-6 sm:space-y-7">

    {{-- ===== Header + pencarian ===== --}}
    <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-2 rounded-full bg-brandbg px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-brandfg">
                    <x-icon name="history" class="h-3.5 w-3.5" />
                    Riwayat Kendaraan
                </span>
                <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-main sm:text-3xl">Riwayat Servis</h1>
                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-muted">
                    @if ($kendaraanAktif)
                        Menampilkan riwayat servis untuk
                        <span class="font-bold text-main">{{ $kendaraanAktif->nama }}</span>
                        dengan nomor plat
                        <span class="font-bold text-main">{{ $kendaraanAktif->plat_nomor }}</span>.
                    @elseif (! empty($searchPlat))
                        Hasil pencarian riwayat servis nomor plat
                        <span class="font-bold text-main">"{{ $searchPlat }}"</span>.
                    @else
                        Lihat seluruh riwayat servis, diagnosa, tindakan mekanik, sparepart, jasa, rekomendasi, dan nota kendaraan Anda.
                    @endif
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-3 rounded-xl bg-surface2 px-4 py-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brandbg text-brandfg">
                    <x-icon name="car" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Kendaraan terdaftar</p>
                    <p class="text-xl font-extrabold leading-tight tabular-nums text-main">
                        {{ $totalKendaraan ?? $kendaraans->count() }}
                        <span class="text-xs font-bold text-brandfg">Unit</span>
                    </p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('pelanggan.riwayat') }}" class="mt-5 flex flex-col gap-2 border-t border-line pt-5 sm:flex-row">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-soft">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </span>
                <input type="text" name="plat" value="{{ $searchPlat ?? '' }}"
                       placeholder="Cari nomor plat, contoh: BM 1234 ABC" autocomplete="off"
                       class="min-h-11 w-full rounded-xl border border-line bg-surface py-2.5 pl-10 pr-4 text-sm font-semibold uppercase tracking-wide text-main outline-none transition placeholder:font-normal placeholder:normal-case placeholder:tracking-normal placeholder:text-soft focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>

            @if (! empty($searchPlat))
                <a href="{{ route('pelanggan.riwayat') }}"
                   class="inline-flex min-h-11 items-center justify-center rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-bold text-muted transition hover:bg-surface2 hover:text-main">
                    Reset
                </a>
            @endif

            <button type="submit"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-500 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
                Cari
            </button>
        </form>
    </section>

    {{-- ===== Filter kendaraan ===== --}}
    @if ($kendaraans->count() > 1 && empty($searchPlat))
        <div>
            <p class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-soft">Filter kendaraan</p>
            <nav class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-2" aria-label="Filter kendaraan">
                <a href="{{ route('pelanggan.riwayat') }}"
                   @class([
                       'shrink-0 rounded-full border px-4 py-2 text-sm font-bold transition',
                       'border-brand-500 bg-brand-500 text-white shadow-md shadow-brand-500/20' => ! $kendaraanAktif,
                       'border-line bg-surface text-muted hover:border-brand-500/50 hover:text-brandfg' => $kendaraanAktif,
                   ])>
                    Semua kendaraan
                </a>

                @foreach ($kendaraans as $k)
                    @php
                        $dipilih = $kendaraanAktif && (int) $kendaraanAktif->id === (int) $k->id;
                    @endphp
                    <a href="{{ route('pelanggan.kendaraan.riwayat', $k) }}"
                       @class([
                           'shrink-0 rounded-full border px-4 py-2 text-sm font-bold tabular-nums transition',
                           'border-brand-500 bg-brand-500 text-white shadow-md shadow-brand-500/20' => $dipilih,
                           'border-line bg-surface text-muted hover:border-brand-500/50 hover:text-brandfg' => ! $dipilih,
                       ])>
                        {{ $k->plat_nomor }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    {{-- ===== Daftar riwayat ===== --}}
    <div class="space-y-4">

        @forelse ($riwayat as $s)
            @php
                $nota = $s->nota;
                $rekomendasi = $s->catatanRekomendasis;
                $kendaraanServis = $s->kendaraan;
            @endphp

            <details class="group overflow-hidden rounded-2xl border border-line bg-surface shadow-sm transition open:border-brand-500/40 open:shadow-soft">

                {{-- Ringkasan (selalu tampil) --}}
                <summary class="flex cursor-pointer list-none items-center gap-3 p-4 transition hover:bg-surface2 sm:gap-4 sm:p-5 [&::-webkit-details-marker]:hidden">
                    <div class="hidden h-12 w-12 shrink-0 place-items-center rounded-xl bg-brandbg text-brandfg sm:grid">
                        <x-icon name="car" class="h-5 w-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <p class="font-bold text-main">{{ $s->tanggal_label }}</p>
                            <x-status-badge :tone="$s->status_tone">{{ $s->status_label }}</x-status-badge>
                        </div>

                        <p class="mt-1.5 line-clamp-1 text-sm text-main/80">{{ $s->keluhan_awal ?? 'Tidak ada keluhan tercatat' }}</p>

                        @if ($kendaraanServis)
                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-muted">
                                <x-plate :nomor="$kendaraanServis->plat_nomor" size="sm" />
                                <span>{{ $kendaraanServis->nama }}</span>
                                @if ($kendaraanServis->tahun)
                                    <span>Tahun {{ $kendaraanServis->tahun }}</span>
                                @endif
                                <span class="tabular-nums">{{ $km($s->km_masuk) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="hidden shrink-0 text-right sm:block">
                        <p class="text-base font-extrabold tabular-nums text-main">{{ $rp($s->total_biaya) }}</p>
                        <p class="mt-0.5 text-xs text-soft">{{ $nota ? 'Total biaya' : 'Estimasi' }}</p>
                    </div>

                    <x-icon name="chevron" class="h-5 w-5 shrink-0 text-soft transition-transform duration-200 group-open:rotate-180" />
                </summary>

                {{-- Detail (terbuka) --}}
                <div class="border-t border-line bg-surface2 p-4 sm:p-5">

                    {{-- Harga untuk mobile --}}
                    <div class="mb-4 flex items-center justify-between rounded-xl border border-line bg-surface px-4 py-3 sm:hidden">
                        <span class="text-xs font-semibold text-muted">{{ $nota ? 'Total biaya' : 'Estimasi biaya' }}</span>
                        <span class="font-extrabold tabular-nums text-main">{{ $rp($s->total_biaya) }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                        {{-- Informasi servis --}}
                        <section class="rounded-xl border border-line bg-surface p-4 sm:p-5">
                            <h3 class="flex items-center gap-2.5 font-bold text-main">
                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brandbg text-brandfg">
                                    <x-icon name="history" class="h-4 w-4" />
                                </span>
                                Informasi Servis
                            </h3>

                            <dl class="mt-4 space-y-4 text-sm">
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Keluhan awal</dt>
                                    <dd class="mt-1 leading-6 text-main">{{ $s->keluhan_awal ?? '-' }}</dd>
                                </div>

                                @if ($s->diagnosa_awal)
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Diagnosa awal</dt>
                                        <dd class="mt-1 leading-6 text-main">{{ $s->diagnosa_awal }}</dd>
                                    </div>
                                @endif

                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Diagnosa akhir</dt>
                                    <dd class="mt-1 leading-6 text-main">{{ $s->diagnosa_akhir ?? 'Belum ada diagnosa akhir' }}</dd>
                                </div>

                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Tindakan servis</dt>
                                    <dd class="mt-1 whitespace-pre-line leading-6 text-main">{{ $s->tindakan_servis ?? 'Belum ada tindakan tercatat' }}</dd>
                                </div>

                                <div class="grid grid-cols-2 gap-4 border-t border-line pt-4">
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">KM masuk</dt>
                                        <dd class="mt-1 font-bold tabular-nums text-main">{{ $s->km_masuk !== null ? $km($s->km_masuk) : '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">KM keluar</dt>
                                        <dd class="mt-1 font-bold tabular-nums text-main">{{ $s->km_keluar !== null ? $km($s->km_keluar) : '—' }}</dd>
                                    </div>
                                    @if ($s->user)
                                        <div class="col-span-2">
                                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-muted">Ditangani oleh</dt>
                                            <dd class="mt-1 font-bold text-main">{{ $s->user->name }}</dd>
                                        </div>
                                    @endif
                                </div>
                            </dl>
                        </section>

                        {{-- Rincian biaya + nota --}}
                        <section class="rounded-xl border border-line bg-surface p-4 sm:p-5">
                            <h3 class="flex items-center gap-2.5 font-bold text-main">
                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-infobg text-infofg">
                                    <x-icon name="check" class="h-4 w-4" />
                                </span>
                                Rincian Biaya
                            </h3>

                            @if ($s->detailJasas->isEmpty() && $s->detailSpareparts->isEmpty())
                                <div class="mt-4 rounded-xl border border-dashed border-line bg-surface2 p-4">
                                    <p class="text-sm text-muted">Rincian biaya belum tersedia.</p>
                                </div>
                            @else
                                <ul class="mt-4 divide-y divide-line overflow-hidden rounded-xl border border-line">
                                    @foreach ($s->detailJasas as $d)
                                        <li class="flex items-start justify-between gap-4 px-4 py-3 text-sm">
                                            <div class="min-w-0">
                                                <p class="font-medium text-main">{{ $d->jasa->nama_jasa ?? 'Jasa servis' }}</p>
                                                <span class="text-xs text-soft">Jasa</span>
                                            </div>
                                            <span class="shrink-0 font-bold tabular-nums text-main">{{ $rp($d->harga_saat_transaksi) }}</span>
                                        </li>
                                    @endforeach

                                    @foreach ($s->detailSpareparts as $d)
                                        <li class="flex items-start justify-between gap-4 px-4 py-3 text-sm">
                                            <div class="min-w-0">
                                                <p class="font-medium text-main">{{ $d->sparepart->nama_barang ?? 'Sparepart' }}</p>
                                                <span class="text-xs text-soft">{{ $d->qty }} × {{ $rp($d->harga_jual_saat_transaksi) }}</span>
                                            </div>
                                            <span class="shrink-0 font-bold tabular-nums text-main">{{ $rp($d->qty * $d->harga_jual_saat_transaksi) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($nota)
                                <div class="mt-4 rounded-xl bg-surface2 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-main">Nota {{ $nota->no_nota }}</p>
                                            <p class="mt-0.5 text-xs text-muted">{{ $nota->tanggal_label }}</p>
                                        </div>
                                        <x-status-badge :tone="$nota->lunas ? 'green' : 'red'">
                                            {{ $nota->lunas ? 'Lunas' : 'Belum lunas' }}
                                        </x-status-badge>
                                    </div>

                                    <dl class="mt-3 space-y-2 border-t border-line pt-3 text-sm">
                                        <div class="flex justify-between gap-3 text-muted">
                                            <dt>Subtotal</dt>
                                            <dd class="tabular-nums">{{ $rp($nota->subtotal) }}</dd>
                                        </div>

                                        @if ((float) $nota->diskon > 0)
                                            <div class="flex justify-between gap-3 text-muted">
                                                <dt>Diskon</dt>
                                                <dd class="tabular-nums text-okfg">- {{ $rp($nota->diskon) }}</dd>
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between gap-3 border-t border-line pt-2.5 font-bold text-main">
                                            <dt>Total</dt>
                                            <dd class="text-lg tabular-nums text-brandfg">{{ $rp($nota->total_biaya) }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @else
                                <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-warnline bg-warnbg px-4 py-3">
                                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-warnfg" />
                                    <p class="text-xs leading-5 text-warnfg">
                                        Nota belum diterbitkan. Total biaya yang ditampilkan merupakan estimasi berdasarkan jasa dan sparepart yang sudah tercatat.
                                    </p>
                                </div>
                            @endif
                        </section>
                    </div>

                    {{-- Catatan rekomendasi pada nota --}}
                    @if ($nota && trim((string) $nota->catatan_nota) !== '')
                        <section class="mt-4 rounded-xl border border-line border-l-4 border-l-brand-500 bg-surface p-4 sm:p-5">
                            <h3 class="font-bold text-main">Catatan Rekomendasi pada Nota</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-main/90">{{ $nota->catatan_nota }}</p>
                            <p class="mt-3 inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $nota->status_rekomendasi === 'sudah' ? 'bg-okbg text-okfg' : 'bg-warnbg text-warnfg' }}">
                                {{ $nota->status_rekomendasi === 'sudah' ? 'Sudah ditangani' : ($nota->status_rekomendasi === 'belum' ? 'Belum ditangani' : 'Status belum dicatat') }}
                            </p>
                        </section>
                    @endif

                    {{-- Rekomendasi dari bengkel --}}
                    @if ($rekomendasi->isNotEmpty())
                        <section class="mt-4 rounded-xl border border-line bg-surface p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-warnbg text-warnfg">
                                    <x-icon name="alert" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h3 class="font-bold text-main">Rekomendasi dari Bengkel</h3>
                                    <p class="text-xs text-muted">Catatan untuk perawatan atau servis berikutnya.</p>
                                </div>
                            </div>

                            <ul class="mt-4 space-y-2">
                                @foreach ($rekomendasi as $r)
                                    @php
                                        $statusKonfirmasi = $r->status_konfirmasi ?? 'belum';
                                        $sudahDikerjakan = $statusKonfirmasi === 'sudah';
                                    @endphp
                                    <li class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-warnline bg-warnbg px-4 py-3">
                                        <div class="flex min-w-0 flex-1 items-start gap-3">
                                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                                            <p class="text-sm leading-6 text-main">{{ $r->catatan }}</p>
                                        </div>
                                        <x-status-badge :tone="$sudahDikerjakan ? 'green' : 'amber'">
                                            {{ $sudahDikerjakan ? 'Sudah Dikerjakan' : 'Perlu Perhatian' }}
                                        </x-status-badge>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    {{-- Tombol detail --}}
                    @if (Route::has('pelanggan.detail-servis'))
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('pelanggan.detail-servis', $s->id) }}"
                               class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 sm:w-auto">
                                Lihat Detail Servis
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </details>

        @empty

            {{-- ===== Riwayat kosong ===== --}}
            <div class="rounded-2xl border border-dashed border-line bg-surface px-6 py-12 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-surface2 text-soft">
                    <x-icon name="history" class="h-7 w-7" />
                </div>

                @if (! empty($searchPlat))
                    <h3 class="mt-4 font-bold text-main">Riwayat Tidak Ditemukan</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-muted">
                        Tidak ditemukan riwayat servis untuk nomor plat "{{ $searchPlat }}".
                    </p>
                    <a href="{{ route('pelanggan.riwayat') }}"
                       class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
                        Tampilkan Semua Riwayat
                    </a>
                @elseif ($kendaraanAktif)
                    <h3 class="mt-4 font-bold text-main">Belum Ada Riwayat Servis</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-muted">
                        Belum ada riwayat servis yang tercatat untuk kendaraan {{ $kendaraanAktif->plat_nomor }}.
                    </p>
                    <a href="{{ route('pelanggan.riwayat') }}"
                       class="mt-5 inline-flex min-h-11 items-center rounded-xl border border-line bg-surface px-5 py-2.5 text-sm font-bold text-main transition hover:bg-surface2">
                        Lihat Semua Kendaraan
                    </a>
                @else
                    <h3 class="mt-4 font-bold text-main">Belum Ada Riwayat Servis</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-muted">
                        Riwayat servis akan muncul setelah kendaraan Anda tercatat melakukan servis di bengkel.
                    </p>
                @endif
            </div>

        @endforelse
    </div>

    {{-- ===== Pagination ===== --}}
    @if ($riwayat->hasPages())
        <div>{{ $riwayat->links() }}</div>
    @endif
</div>

@endsection