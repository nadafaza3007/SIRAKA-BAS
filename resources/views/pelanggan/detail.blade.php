@extends('layouts.pelanggan')

@section(
    'title',
    'Detail Servis - ' . ($servis->kendaraan->plat_nomor ?? 'Kendaraan')
)

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .print-card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush

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

    $kendaraan = $servis->kendaraan;
    $konsumen = $kendaraan?->konsumen;
    $nota = $servis->nota;

    $detailSpareparts = $servis->detailSpareparts ?? collect();
    $detailJasas = $servis->detailJasas ?? collect();
    $rekomendasi = $servis->catatanRekomendasis ?? collect();
@endphp


<div class="mx-auto max-w-6xl">

    {{-- =========================================================
        TOMBOL KEMBALI
    ========================================================== --}}
    <div class="mb-5 no-print">

        <a
            href="{{ route('pelanggan.riwayat') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-orange-600"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-4 w-4"
            >
                <path d="m15 18-6-6 6-6"></path>
            </svg>

            Kembali ke Riwayat Servis
        </a>

    </div>



    <div class="print-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- =====================================================
            HEADER DETAIL
        ====================================================== --}}
        <div class="border-b border-slate-200 p-5 sm:p-6">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-600">

                        <x-icon
                            name="history"
                            class="h-4 w-4"
                        />

                        SIRAKA • Baba Auto Service

                    </div>

                    <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
                        Detail Rekam Servis
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-slate-500">

                        <span>
                            ID Servis:
                            <strong class="text-slate-700">
                                #RS-{{ str_pad($servis->id, 5, '0', STR_PAD_LEFT) }}
                            </strong>
                        </span>

                        <span class="hidden sm:inline">
                            •
                        </span>

                        <span>
                            {{ $servis->tanggal_label }}
                        </span>

                        <x-status-badge :tone="$servis->status_tone">
                            {{ $servis->status_label }}
                        </x-status-badge>

                    </div>

                </div>


                <button
                    type="button"
                    onclick="window.print()"
                    class="no-print inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-4 w-4"
                    >
                        <path d="M6 9V2h12v7"></path>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect width="12" height="8" x="6" y="14"></rect>
                    </svg>

                    Cetak / Simpan PDF

                </button>

            </div>

        </div>



        <div class="p-5 sm:p-6">

            {{-- =====================================================
                DATA PELANGGAN & KENDARAAN
            ====================================================== --}}
            <div class="grid gap-4 md:grid-cols-2">

                {{-- Pelanggan --}}
                <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Data Pelanggan
                    </h2>

                    <dl class="mt-4 space-y-3 text-sm">

                        <div class="flex justify-between gap-4">

                            <dt class="text-slate-500">
                                Nama
                            </dt>

                            <dd class="text-right font-semibold text-slate-900">
                                {{ $konsumen?->nama_lengkap ?? '-' }}
                            </dd>

                        </div>

                        <div class="flex justify-between gap-4">

                            <dt class="text-slate-500">
                                No. HP
                            </dt>

                            <dd class="text-right font-medium text-slate-900">
                                {{ $konsumen?->no_hp ?? '-' }}
                            </dd>

                        </div>

                    </dl>

                </section>


                {{-- Kendaraan --}}
                <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Data Kendaraan
                    </h2>

                    <div class="mt-4">

                        @if ($kendaraan)

                            <x-plate
                                :nomor="$kendaraan->plat_nomor"
                                size="sm"
                            />

                        @endif

                    </div>

                    <dl class="mt-4 space-y-3 text-sm">

                        <div class="flex justify-between gap-4">

                            <dt class="text-slate-500">
                                Unit
                            </dt>

                            <dd class="text-right font-semibold text-slate-900">
                                {{ $kendaraan?->nama ?? '-' }}
                            </dd>

                        </div>

                        <div class="flex justify-between gap-4">

                            <dt class="text-slate-500">
                                Tahun
                            </dt>

                            <dd class="text-right font-medium text-slate-900">
                                {{ $kendaraan?->tahun ?? '-' }}
                            </dd>

                        </div>

                        <div class="flex justify-between gap-4">

                            <dt class="text-slate-500">
                                Kilometer
                            </dt>

                            <dd class="text-right font-semibold tabular-nums text-slate-900">
                                {{ $servis->km_akhir !== null
                                    ? $km($servis->km_akhir)
                                    : '-'
                                }}
                            </dd>

                        </div>

                    </dl>

                </section>

            </div>



            {{-- =====================================================
                DIAGNOSA & TINDAKAN
            ====================================================== --}}
            <section class="mt-6 rounded-xl border border-slate-200 p-5">

                <h2 class="font-semibold text-slate-900">
                    Informasi Servis
                </h2>

                <dl class="mt-4 space-y-5 text-sm">

                    <div>

                        <dt class="text-xs font-medium text-slate-500">
                            Keluhan Konsumen
                        </dt>

                        <dd class="mt-1 leading-6 text-slate-900">
                            {{ $servis->keluhan_awal ?? '-' }}
                        </dd>

                    </div>


                    <div class="grid gap-5 border-t border-slate-100 pt-5 md:grid-cols-2">

                        <div>

                            <dt class="text-xs font-medium text-slate-500">
                                Diagnosa Awal
                            </dt>

                            <dd class="mt-1 leading-6 text-slate-900">
                                {{ $servis->diagnosa_awal ?? 'Belum ada diagnosa awal' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="text-xs font-medium text-slate-500">
                                Diagnosa Akhir
                            </dt>

                            <dd class="mt-1 leading-6 text-slate-900">
                                {{ $servis->diagnosa_akhir
                                    ?? 'Menunggu konfirmasi pemeriksaan mekanik'
                                }}
                            </dd>

                        </div>

                    </div>


                    <div class="border-t border-slate-100 pt-5">

                        <dt class="text-xs font-medium text-slate-500">
                            Tindakan Servis
                        </dt>

                        {{-- Tetap mendukung satu atau banyak baris --}}
                        <dd class="mt-1 whitespace-pre-line leading-6 text-slate-900">{{ $servis->tindakan_servis ?? 'Belum ada tindakan tercatat' }}</dd>

                    </div>


                    @if ($servis->user)

                        <div class="border-t border-slate-100 pt-5">

                            <dt class="text-xs font-medium text-slate-500">
                                Ditangani oleh
                            </dt>

                            <dd class="mt-1 font-semibold text-slate-900">
                                {{ $servis->user->name }}
                            </dd>

                        </div>

                    @endif

                </dl>

            </section>



            {{-- =====================================================
                RINCIAN SPAREPART & JASA
            ====================================================== --}}
            <section class="mt-6">

                <h2 class="font-semibold text-slate-900">
                    Rincian Sparepart & Jasa
                </h2>

                <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">

                    <table class="min-w-full text-left text-sm">

                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">

                            <tr>

                                <th class="px-4 py-3">
                                    Item
                                </th>

                                <th class="px-4 py-3 text-center">
                                    Tipe
                                </th>

                                <th class="px-4 py-3 text-center">
                                    Qty
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Harga
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            {{-- =========================================
                                SPAREPART
                            ========================================== --}}
                            @foreach ($detailSpareparts as $item)

                                <tr>

                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        {{ $item->sparepart?->nama_barang ?? 'Sparepart' }}
                                    </td>

                                    <td class="px-4 py-3 text-center">

                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            Sparepart
                                        </span>

                                    </td>

                                    <td class="px-4 py-3 text-center tabular-nums">
                                        {{ $item->qty }}
                                    </td>

                                    <td class="px-4 py-3 text-right tabular-nums text-slate-700">
                                        {{ $rp($item->harga_jual_saat_transaksi) }}
                                    </td>

                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">

                                        {{ $rp(
                                            $item->harga_jual_saat_transaksi
                                            * $item->qty
                                        ) }}

                                    </td>

                                </tr>

                            @endforeach



                            {{-- =========================================
                                JASA
                            ========================================== --}}
                            @foreach ($detailJasas as $jasa)

                                <tr>

                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        {{ $jasa->jasa?->nama_jasa ?? 'Jasa Servis' }}
                                    </td>

                                    <td class="px-4 py-3 text-center">

                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-600">
                                            Jasa
                                        </span>

                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        1
                                    </td>

                                    <td class="px-4 py-3 text-right tabular-nums text-slate-700">
                                        {{ $rp($jasa->harga_saat_transaksi) }}
                                    </td>

                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">
                                        {{ $rp($jasa->harga_saat_transaksi) }}
                                    </td>

                                </tr>

                            @endforeach



                            {{-- =========================================
                                KOSONG
                            ========================================== --}}
                            @if (
                                $detailSpareparts->isEmpty()
                                && $detailJasas->isEmpty()
                            )

                                <tr>

                                    <td
                                        colspan="5"
                                        class="px-4 py-8 text-center text-sm text-slate-500"
                                    >
                                        Rincian item belum tersedia atau
                                        servis masih dalam pengerjaan.
                                    </td>

                                </tr>

                            @endif

                        </tbody>

                    </table>

                </div>

            </section>



            {{-- =====================================================
                REKOMENDASI
            ====================================================== --}}
            @if ($rekomendasi->isNotEmpty())

                <section class="mt-6 rounded-xl border border-amber-200 bg-amber-50/60 p-4">

                    <div class="flex items-center gap-2">

                        <x-icon
                            name="alert"
                            class="h-5 w-5 text-amber-600"
                        />

                        <h2 class="font-semibold text-slate-900">
                            Rekomendasi Servis Lanjutan
                        </h2>

                    </div>


                    <ul class="mt-4 space-y-2">

                        @foreach ($rekomendasi as $rek)

                            @php
                                $statusKonfirmasi =
                                    $rek->status_konfirmasi ?? 'belum';

                                $sudahDikerjakan =
                                    $statusKonfirmasi === 'sudah';
                            @endphp

                            <li class="flex flex-wrap items-start justify-between gap-3 rounded-lg border border-amber-200 bg-white/70 px-4 py-3">

                                <p class="min-w-0 flex-1 text-sm leading-6 text-slate-700">
                                    {{ $rek->catatan }}
                                </p>

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



            {{-- =====================================================
                TOTAL TAGIHAN
            ====================================================== --}}
            <section class="mt-6 border-t border-slate-200 pt-6">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">

                    <div class="max-w-lg">

                        <p class="text-sm text-slate-500">
                            Terima kasih atas kepercayaan Anda merawat
                            kendaraan di Baba Auto Service.
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Simpan lembar digital ini sebagai referensi
                            riwayat perawatan kendaraan Anda.
                        </p>

                    </div>


                    <div class="w-full sm:w-80">

                        @if ($nota)

                            <dl class="space-y-2 text-sm">

                                <div class="flex justify-between gap-4 text-slate-600">

                                    <dt>
                                        Subtotal
                                    </dt>

                                    <dd class="tabular-nums">
                                        {{ $rp($nota->subtotal) }}
                                    </dd>

                                </div>


                                @if ((float) $nota->diskon > 0)

                                    <div class="flex justify-between gap-4 text-emerald-600">

                                        <dt>
                                            Diskon
                                        </dt>

                                        <dd class="tabular-nums">
                                            - {{ $rp($nota->diskon) }}
                                        </dd>

                                    </div>

                                @endif


                                <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-base font-bold text-slate-900">

                                    <dt>
                                        Total Tagihan
                                    </dt>

                                    <dd class="tabular-nums text-orange-600">
                                        {{ $rp($nota->total_biaya) }}
                                    </dd>

                                </div>

                            </dl>


                            <div class="mt-4 flex flex-wrap items-center justify-end gap-2">

                                <x-status-badge
                                    :tone="$nota->lunas ? 'green' : 'red'"
                                >
                                    {{ $nota->lunas
                                        ? 'Lunas'
                                        : 'Belum Lunas'
                                    }}
                                </x-status-badge>

                                <span class="text-xs font-medium text-slate-500">
                                    Nota {{ $nota->no_nota }}
                                </span>

                            </div>

                        @else

                            <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                Tagihan atau nota belum diterbitkan.
                            </div>

                        @endif

                    </div>

                </div>

            </section>

        </div>

    </div>

</div>

@endsection