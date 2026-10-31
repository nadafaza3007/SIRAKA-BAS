@extends('layouts.admin')

@section('title', 'Daftar Rekam Servis - SIRAKA')

@section('content')

{{-- HEADER --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-extrabold tracking-tight text-white">
            Daftar Rekam Servis
        </h2>
        <p class="mt-1 text-xs text-neutral-500">
            Pantau pengerjaan servis, koreksi diagnosa, dan kelola nota hingga pembayaran selesai.
        </p>
    </div>

    <div class="flex w-full flex-wrap gap-2 sm:w-auto">
    <a href="{{ route('admin.rekam-servis.rekap-nota.index') }}"
       class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-600/50 bg-emerald-600/10 px-4 py-2.5 text-xs font-bold text-emerald-300 hover:bg-emerald-600/20 sm:w-auto">
        <i class="fa-solid fa-file-excel"></i><span>Rekap Nota</span>
    </a>
    <a href="{{ route('admin.rekam-servis.create') }}"
       class="siraka-mobile-action inline-flex w-full items-center justify-center sm:w-fit gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
        <i class="fa-solid fa-plus fa-fw"></i>
        <span>Input Servis Baru</span>
    </a>
    </div>
</div>

{{-- TABEL: geser horizontal khusus layar sempit, tanpa mengubah logika aksi. --}}
<p class="mb-2 text-[11px] text-neutral-500 lg:hidden">Geser tabel ke samping untuk melihat status dan tombol aksi →</p>
{{-- TABEL --}}
<div class="overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">
    <div class="overflow-x-auto overscroll-x-contain">

        <table class="w-full min-w-[1000px] table-fixed text-left text-xs text-neutral-400">

            <colgroup>
                <col class="w-[110px]">
                <col class="w-[190px]">
                <col>
                <col>
                <col class="w-[190px]">
                <col class="w-[190px]">
            </colgroup>

            <thead class="border-b border-neutral-800 bg-black/40 text-[11px] font-bold tracking-wide text-neutral-500">
                <tr>
                    <th class="px-4 py-3.5">Tanggal</th>
                    <th class="px-4 py-3.5">Kendaraan</th>
                    <th class="px-4 py-3.5">Diagnosa awal</th>
                    <th class="px-4 py-3.5">Diagnosa akhir</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-neutral-800">

                @forelse ($servis as $item)

                    @php
                        $nota = $item->nota;

                        /*
                        | Tahap ditentukan dari data nyata, bukan hanya kolom status:
                        | menunggu -> dikerjakan -> menunggu pembayaran -> data akhir -> selesai
                        */
                        $tahap = match (true) {
                            // Lunas = seluruh transaksi selesai (termasuk data lama)
                            $nota && $nota->status_pembayaran === 'lunas'          => 'selesai',
                            // Data akhir sudah disimpan, tinggal pembayaran
                            $nota && $item->status === 'selesai'
                                  && $item->km_keluar !== null                     => 'pembayaran',
                            // Tagihan terbit, data akhir belum diisi
                            (bool) $nota                                           => 'data_akhir',
                            // Data lama yang selesai tanpa nota
                            $item->status === 'selesai'                            => 'selesai',
                            $item->status === 'menunggu_pengerjaan'                => 'menunggu',
                            default                                                => 'dikerjakan',
                        };

                        $badge = match ($tahap) {
                            'menunggu'   => ['Menunggu pengerjaan', 'fa-clock',               'bg-amber-500/10 text-amber-400 border-amber-500/20'],
                            'dikerjakan' => ['Sedang dikerjakan',   'fa-screwdriver-wrench',  'bg-blue-500/10 text-blue-400 border-blue-500/20'],
                            'pembayaran' => ['Menunggu pembayaran', 'fa-wallet',              'bg-orange-500/10 text-orange-400 border-orange-500/20'],
                            'data_akhir' => ['Lengkapi data akhir', 'fa-flag-checkered',      'bg-sky-500/10 text-sky-400 border-sky-500/20'],
                            'selesai'    => ['Selesai',             'fa-circle-check',        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                        };

                        // [label, ikon, route, primer?]
                        $aksi = match ($tahap) {
                            'menunggu'   => ['Koreksi diagnosa',   'fa-screwdriver-wrench',   route('admin.rekam-servis.koreksi-form', $item->id), true],
                            'dikerjakan' => ['Susun estimasi',     'fa-file-invoice-dollar',  route('admin.rekam-servis.nota', $item->id), true],
                            'pembayaran' => ['Terima pembayaran',  'fa-wallet',               route('admin.rekam-servis.nota', $item->id), true],
                            'data_akhir' => ['Lengkapi data akhir','fa-flag-checkered',       route('admin.rekam-servis.nota', $item->id), true],
                            'selesai'    => ['Lihat nota',         'fa-eye',                  route('admin.rekam-servis.nota', $item->id), false],
                        };
                    @endphp

                    <tr class="transition hover:bg-neutral-800/40">

                        {{-- TANGGAL --}}
                        <td class="px-4 py-4 align-middle font-mono text-neutral-300">
                            {{ \Carbon\Carbon::parse($item->tanggal_servis)->format('d/m/Y') }}
                        </td>

                        {{-- KENDARAAN --}}
                        <td class="px-4 py-4 align-middle">
                            <div class="truncate text-sm font-extrabold uppercase tracking-wide text-white">
                                {{ $item->kendaraan->plat_nomor }}
                            </div>
                            <div class="mt-1 truncate text-[11px] text-neutral-400">
                                {{ $item->kendaraan->konsumen->nama_lengkap }}
                            </div>
                            <div class="mt-0.5 truncate text-[11px] text-neutral-600">
                                {{ $item->kendaraan->merk }} {{ $item->kendaraan->tipe_model }}
                            </div>
                        </td>

                        {{-- DIAGNOSA AWAL --}}
                        <td class="px-4 py-4 align-middle">
                            <p class="text-xs leading-5 text-neutral-300">
                                {{ $item->diagnosa_awal ?: '-' }}
                            </p>
                        </td>

                        {{-- DIAGNOSA AKHIR
                             Ikon dibungkus kotak setinggi baris teks (h-5 = leading-5)
                             supaya selalu sejajar dengan baris pertama. --}}
                        <td class="px-4 py-4 align-middle">
                            @if($item->diagnosa_akhir)
                                <div class="flex items-start gap-2">
                                    <span class="grid h-5 w-4 shrink-0 place-items-center text-emerald-400">
                                        <i class="fa-solid fa-circle-check text-[11px]" aria-hidden="true"></i>
                                    </span>
                                    <p class="text-xs font-medium leading-5 text-neutral-100">
                                        {{ $item->diagnosa_akhir }}
                                    </p>
                                </div>
                            @else
                                <div class="flex items-start gap-2">
                                    <span class="grid h-5 w-4 shrink-0 place-items-center text-amber-500">
                                        <i class="fa-regular fa-clock text-[11px]" aria-hidden="true"></i>
                                    </span>
                                    <p class="text-xs leading-5 text-amber-500/90">
                                        Menunggu cek fisik
                                    </p>
                                </div>
                            @endif
                        </td>

                        {{-- STATUS --}}
                        <td class="px-4 py-4 text-center align-middle">
                            <span class="inline-flex h-7 items-center justify-center gap-1.5 whitespace-nowrap rounded-full border px-3 text-[11px] font-bold {{ $badge[2] }}">
                                <i class="fa-solid {{ $badge[1] }} text-[10px]" aria-hidden="true"></i>
                                <span class="leading-none">{{ $badge[0] }}</span>
                            </span>
                        </td>

                        {{-- AKSI --}}
                        <td class="px-4 py-4 text-right align-middle">
                            <a href="{{ $aksi[2] }}"
                               class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-3.5 text-[11px] font-bold transition
                                      {{ $aksi[3]
                                          ? 'bg-brand-500 text-white shadow-sm hover:bg-brand-600'
                                          : 'border border-neutral-700 bg-neutral-800 text-neutral-300 hover:bg-neutral-700 hover:text-white' }}">
                                <i class="fa-solid {{ $aksi[1] }} text-[11px]" aria-hidden="true"></i>
                                <span class="leading-none">{{ $aksi[0] }}</span>
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="p-10 text-center">
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