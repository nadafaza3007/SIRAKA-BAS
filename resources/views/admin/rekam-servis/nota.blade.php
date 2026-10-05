@extends('layouts.admin')

@section('title', 'Nota Transaksi Servis - ' . $servis->kendaraan->plat_nomor)

@section('content')

@php
    $subtotalCalc = $servis->subtotal();

    $diskonExisting = $servis->nota
        ? $servis->nota->diskon
        : 0;

    $inputRibuanExisting = $diskonExisting > 0
        ? ($diskonExisting / 1000)
        : 0;

    $totalCalc = max(
        0,
        $subtotalCalc - $diskonExisting
    );

    $isSelesai = $servis->status === 'selesai';
@endphp


<div class="mx-auto max-w-4xl space-y-6">

    {{-- =========================================================
        FLASH MESSAGE
    ========================================================== --}}
    @if(session('success'))

        <div class="no-print flex items-center justify-between rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-bold text-emerald-400">

            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check"></i>

                <span>
                    {{ session('success') }}
                </span>
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

        <div class="no-print flex items-center justify-between rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-bold text-rose-400">

            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>
                    {{ session('error') }}
                </span>
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
        NAVIGATION & PRINT
    ========================================================== --}}
    <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <a
            href="{{ route('admin.rekam-servis.index') }}"
            class="inline-flex items-center gap-2 text-xs font-bold text-neutral-400 transition hover:text-white"
        >
            <i class="fa-solid fa-arrow-left"></i>

            <span>
                Kembali ke Rekam Servis
            </span>
        </a>


        <div class="flex flex-wrap items-center gap-2">

            <button
                type="button"
                onclick="pilihFormatDanCetak('thermal')"
                class="flex items-center gap-1.5 rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2 text-xs font-bold text-neutral-200 transition hover:bg-neutral-700"
            >
                <i class="fa-solid fa-receipt text-amber-400"></i>

                <span>
                    Format Struk Kasir (Thermal)
                </span>
            </button>


            <button
                type="button"
                onclick="pilihFormatDanCetak('standar')"
                class="flex items-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600"
            >
                <i class="fa-solid fa-print"></i>

                <span>
                    Cetak Standar (A4/Faktur)
                </span>
            </button>

        </div>

    </div>



    {{-- =========================================================
        PANEL INPUT TRANSAKSI
    ========================================================== --}}
    @if(!$isSelesai)

        <div class="no-print grid grid-cols-1 gap-4 md:grid-cols-2">

            {{-- =====================================================
                SPAREPART
            ====================================================== --}}
            <section class="rounded-3xl border border-neutral-800 bg-ink-900 p-5 shadow-sm">

                <div class="mb-4 flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-brand-400">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>


                        <div>

                            <h3 class="text-xs font-extrabold text-white">
                                Input Suku Cadang
                            </h3>

                            <p class="mt-0.5 text-[10px] text-neutral-500">
                                Pilih barang dan tentukan jumlah.
                            </p>

                        </div>

                    </div>


                    <a
                        href="{{ route('admin.sparepart.index') }}"
                        class="shrink-0 text-[10px] font-bold text-brand-400 hover:underline"
                    >
                        + Master
                    </a>

                </div>


                <form
                    id="formTambahSparepart"
                    action="{{ route(
                        'admin.rekam-servis.sparepart.tambah',
                        $servis->id
                    ) }}"
                    method="POST"
                    class="space-y-3"
                >

                    @csrf


                    <div>

                        <label
                            for="sparepartSelect"
                            class="mb-1 block text-[10px] font-bold text-neutral-400"
                        >
                            Pilih Sparepart
                        </label>


                        <select
                            name="sparepart_id"
                            id="sparepartSelect"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/80 p-2.5 text-xs text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        >

                            <option value="">
                                -- Pilih Sparepart Dari Master --
                            </option>


                            @foreach($spareparts as $sp)

                                <option
                                    value="{{ $sp->id }}"
                                    data-stock="{{ $sp->stok }}"
                                    data-price="{{ $sp->harga_jual }}"
                                >
                                    {{ $sp->nama_barang }}
                                </option>

                            @endforeach

                        </select>

                    </div>



                    {{-- Informasi sparepart --}}
                    <div
                        id="sparepartInfo"
                        class="hidden rounded-xl border border-neutral-800 bg-black/20 p-3"
                    >

                        <div class="grid grid-cols-2 gap-3">

                            <div>

                                <p class="text-[9px] font-bold uppercase tracking-wider text-neutral-600">
                                    Stok Tersedia
                                </p>

                                <p
                                    id="sparepartStock"
                                    class="mt-1 text-xs font-bold text-white"
                                >
                                    -
                                </p>

                            </div>


                            <div class="text-right">

                                <p class="text-[9px] font-bold uppercase tracking-wider text-neutral-600">
                                    Harga Jual
                                </p>

                                <p
                                    id="sparepartPrice"
                                    class="mt-1 font-mono text-xs font-bold text-brand-400"
                                >
                                    -
                                </p>

                            </div>

                        </div>

                    </div>



                    <div class="flex gap-2">

                        <div class="w-24">

                            <label
                                for="sparepartQty"
                                class="mb-1 block text-[10px] font-bold text-neutral-400"
                            >
                                Qty
                            </label>

                            <input
                                type="number"
                                name="qty"
                                id="sparepartQty"
                                value="1"
                                min="1"
                                required
                                class="w-full rounded-xl border border-neutral-700 bg-neutral-800/80 p-2.5 text-center text-xs font-bold text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                            >

                        </div>


                        <div class="flex flex-1 items-end">

                            <button
                                type="submit"
                                class="js-submit-button flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <i class="fa-solid fa-plus"></i>

                                Tambahkan
                            </button>

                        </div>

                    </div>

                </form>

            </section>



            {{-- =====================================================
                JASA
            ====================================================== --}}
            <section class="rounded-3xl border border-neutral-800 bg-ink-900 p-5 shadow-sm">

                <div class="mb-4 flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-sky-500/10 text-sky-400">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>


                        <div>

                            <h3 class="text-xs font-extrabold text-white">
                                Input Ongkos Jasa
                            </h3>

                            <p class="mt-0.5 text-[10px] text-neutral-500">
                                Gunakan tarif master atau tarif khusus.
                            </p>

                        </div>

                    </div>


                    <a
                        href="{{ route('admin.jasa.index') }}"
                        class="shrink-0 text-[10px] font-bold text-sky-400 hover:underline"
                    >
                        + Master
                    </a>

                </div>


                <form
                    id="formTambahJasa"
                    action="{{ route(
                        'admin.rekam-servis.jasa.tambah',
                        $servis->id
                    ) }}"
                    method="POST"
                    class="space-y-3"
                >

                    @csrf


                    <div>

                        <label
                            for="jasaSelect"
                            class="mb-1 block text-[10px] font-bold text-neutral-400"
                        >
                            Pilih Jasa Servis
                        </label>


                        <select
                            name="jasa_id"
                            id="jasaSelect"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/80 p-2.5 text-xs text-white outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"
                        >

                            <option value="">
                                -- Pilih Tarif Jasa --
                            </option>


                            @foreach($jasas as $js)

                                <option
                                    value="{{ $js->id }}"
                                    data-price="{{ $js->harga }}"
                                >
                                    {{ $js->nama_jasa }}
                                </option>

                            @endforeach

                        </select>

                    </div>



                    {{-- Tarif master --}}
                    <div
                        id="jasaInfo"
                        class="hidden rounded-xl border border-neutral-800 bg-black/20 p-3"
                    >

                        <div class="flex items-center justify-between">

                            <span class="text-[10px] font-bold text-neutral-500">
                                Tarif Master
                            </span>

                            <span
                                id="jasaMasterPrice"
                                class="font-mono text-xs font-bold text-sky-400"
                            >
                                -
                            </span>

                        </div>

                    </div>



                    <div>

                        <label
                            for="hargaCustom"
                            class="mb-1 block text-[10px] font-bold text-neutral-400"
                        >
                            Tarif Custom

                            <span class="font-normal text-neutral-600">
                                (opsional)
                            </span>
                        </label>


                        <div class="flex gap-2">

                            <div class="relative flex-1">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[10px] font-bold text-neutral-500">
                                    Rp
                                </span>

                                <input
                                    type="number"
                                    name="harga_custom"
                                    id="hargaCustom"
                                    min="0"
                                    placeholder="Kosongkan untuk tarif master"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-800/80 py-2.5 pl-9 pr-3 text-xs text-white placeholder-neutral-600 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"
                                >

                            </div>


                            <button
                                type="submit"
                                class="js-submit-button inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-sky-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <i class="fa-solid fa-plus"></i>

                                Tambahkan
                            </button>

                        </div>

                    </div>

                </form>

            </section>

        </div>

    @endif



    {{-- =========================================================
        CONTAINER NOTA
    ========================================================== --}}
    <div
        id="printArea"
        class="rounded-3xl border border-neutral-800 bg-ink-900 p-6 text-neutral-200 shadow-2xl sm:p-10"
    >

        {{-- =====================================================
            HEADER NOTA
        ====================================================== --}}
        <div class="nota-header flex items-start justify-between border-b border-neutral-800 pb-4">

            <div class="flex items-center gap-3">

                <div class="logo-box flex items-center justify-center rounded-2xl border border-neutral-800 bg-black p-2 shadow-md">

                    <img
                        src="{{ asset('images/logo-siraka.png') }}"
                        alt="Logo SIRAKA"
                        class="h-12 w-12 rounded-xl object-contain"
                    >

                </div>


                <div>

                    <h1 class="text-base font-black tracking-wider text-white">
                        SIRAKA BENGKEL AUTOMOTIVE
                    </h1>

                    <p class="text-[10px] font-bold tracking-wide text-brand-400">
                        SISTEM INFORMASI PERAWATAN KENDARAAN
                    </p>

                    <p class="text-[10px] text-neutral-400">
                        Jl. Soebrantas No. 88, Panam, Pekanbaru
                        &bull;
                        Telp/WA: 0812-3456-7890
                    </p>

                </div>

            </div>


            <div class="text-right">

                <span class="ml-auto block w-fit rounded-full border border-brand-500/30 bg-brand-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-brand-400">

                    {{ $servis->nota
                        ? 'NOTA RESMI'
                        : 'PRATINJAU NOTA'
                    }}

                </span>


                <p class="mt-1 font-mono text-xs font-bold text-white">

                    {{ $servis->nota
                        ? $servis->nota->no_nota
                        : 'DRAFT-RS-' . $servis->id
                    }}

                </p>


                <p class="text-[10px] text-neutral-500">

                    {{ \Carbon\Carbon::parse(
                        $servis->tanggal_servis
                    )->translatedFormat('d F Y') }}

                </p>

            </div>

        </div>



        {{-- =====================================================
            IDENTITAS
        ====================================================== --}}
        <div class="nota-info grid grid-cols-2 gap-4 border-b border-neutral-800 py-3 text-xs">

            <div>

                <span class="mb-0.5 block text-[10px] font-extrabold uppercase tracking-wider text-neutral-500">
                    Kepada Pelanggan:
                </span>

                <p class="font-bold text-white">
                    {{ $servis->kendaraan->konsumen->nama_lengkap }}
                </p>

                <p class="text-[11px] text-neutral-400">
                    {{ $servis->kendaraan->konsumen->no_hp }}
                </p>

            </div>


            <div class="text-right">

                <span class="mb-0.5 block text-[10px] font-extrabold uppercase tracking-wider text-neutral-500">
                    Identitas Kendaraan:
                </span>

                <p class="font-black tracking-wider text-white">
                    {{ $servis->kendaraan->plat_nomor }}
                </p>

                <p class="text-[11px] text-neutral-400">

                    {{ $servis->kendaraan->merk }}
                    {{ $servis->kendaraan->tipe_model }}

                    (
                    {{ number_format(
                        $servis->km_akhir,
                        0,
                        ',',
                        '.'
                    ) }}
                    KM)

                </p>

            </div>

        </div>



        {{-- =====================================================
            DIAGNOSA & TINDAKAN
        ====================================================== --}}
        <div class="space-y-2 border-b border-neutral-800 py-3 text-xs">

            <div class="grid grid-cols-[160px_1fr] gap-4">

                <span class="text-neutral-400">
                    Diagnosa Fisik Mekanik:
                </span>

                <span class="text-right font-bold text-white">
                    {{ $servis->diagnosa_akhir
                        ?? $servis->diagnosa_awal
                    }}
                </span>

            </div>


            @if($servis->tindakan_servis)

                <div class="grid grid-cols-[160px_1fr] gap-4">

                    <span class="text-neutral-400">
                        Tindakan Perbaikan:
                    </span>

                    <span class="text-right font-medium leading-5 text-neutral-300">
                        {{ $servis->tindakan_servis }}
                    </span>

                </div>

            @endif

        </div>



        {{-- =====================================================
            RINCIAN TRANSAKSI
        ====================================================== --}}
        <div class="py-4">

            <div class="mb-3 flex items-center justify-between">

                <div>

                    <h3 class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-400">
                        Rincian Suku Cadang & Jasa
                    </h3>


                    @if(!$isSelesai)

                        <p class="no-print mt-1 text-[9px] text-neutral-600">
                            Perubahan item tersimpan otomatis.
                        </p>

                    @endif

                </div>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full min-w-[720px] text-left text-xs">

                    <thead>

                        <tr class="border-b border-neutral-800 text-[10px] font-extrabold uppercase tracking-wider text-neutral-400">

                            <th class="pb-2">
                                Deskripsi
                            </th>

                            <th class="pb-2 text-center">
                                Tipe
                            </th>

                            <th class="pb-2 text-center">
                                Qty
                            </th>

                            <th class="pb-2 text-right">
                                Harga Satuan
                            </th>

                            <th class="pb-2 text-right">
                                Subtotal
                            </th>

                            @if(!$isSelesai)

                                <th class="no-print pb-2 text-center">
                                    Aksi
                                </th>

                            @endif

                        </tr>

                    </thead>


                    <tbody
                        id="transactionItems"
                        class="divide-y divide-neutral-800/60"
                    >

                        {{-- =============================================
                            SPAREPART
                        ============================================== --}}
                        @foreach($servis->detailSpareparts as $spDetail)

                            <tr
                                data-item-row
                                data-kind="sparepart"
                                data-detail-id="{{ $spDetail->id }}"
                                class="transition hover:bg-neutral-800/30"
                            >

                                <td class="py-3 font-semibold text-white">

                                    {{ $spDetail->sparepart->nama_barang
                                        ?? 'Sparepart'
                                    }}

                                </td>


                                <td class="py-3 text-center">

                                    <span class="rounded-full bg-brand-500/10 px-2 py-1 text-[9px] font-bold text-brand-400">
                                        BARANG
                                    </span>

                                </td>


                                <td class="py-3 text-center">

                                    @if(!$isSelesai)

                                        <div class="no-print inline-flex items-center overflow-hidden rounded-lg border border-neutral-700 bg-neutral-800">

                                            <button
                                                type="button"
                                                data-qty-action="-1"
                                                data-url="{{ route(
                                                    'admin.rekam-servis.sparepart.qty',
                                                    [
                                                        $servis->id,
                                                        $spDetail->id
                                                    ]
                                                ) }}"
                                                class="js-qty-button grid h-7 w-7 place-items-center text-neutral-400 transition hover:bg-neutral-700 hover:text-white"
                                            >
                                                <i class="fa-solid fa-minus text-[8px]"></i>
                                            </button>


                                            <span class="js-item-qty min-w-[32px] px-1 font-bold text-white">
                                                {{ $spDetail->qty }}
                                            </span>


                                            <button
                                                type="button"
                                                data-qty-action="1"
                                                data-url="{{ route(
                                                    'admin.rekam-servis.sparepart.qty',
                                                    [
                                                        $servis->id,
                                                        $spDetail->id
                                                    ]
                                                ) }}"
                                                class="js-qty-button grid h-7 w-7 place-items-center text-neutral-400 transition hover:bg-neutral-700 hover:text-white"
                                            >
                                                <i class="fa-solid fa-plus text-[8px]"></i>
                                            </button>

                                        </div>


                                        <span class="hidden print:inline">
                                            {{ $spDetail->qty }}
                                        </span>

                                    @else

                                        <span class="font-bold">
                                            {{ $spDetail->qty }}
                                        </span>

                                    @endif

                                </td>


                                <td class="py-3 text-right font-mono">

                                    Rp {{ number_format(
                                        $spDetail->harga_jual_saat_transaksi,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                <td class="js-item-subtotal py-3 text-right font-mono font-bold text-white">

                                    Rp {{ number_format(
                                        $spDetail->harga_jual_saat_transaksi
                                        * $spDetail->qty,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                @if(!$isSelesai)

                                    <td class="no-print py-3 text-center">

                                        <button
                                            type="button"
                                            data-url="{{ route(
                                                'admin.rekam-servis.sparepart.hapus',
                                                [
                                                    $servis->id,
                                                    $spDetail->id
                                                ]
                                            ) }}"
                                            class="js-delete-item inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-400 transition hover:bg-rose-500/10 hover:text-rose-300"
                                            title="Hapus sparepart"
                                        >
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>

                                    </td>

                                @endif

                            </tr>

                        @endforeach



                        {{-- =============================================
                            JASA
                        ============================================== --}}
                        @foreach($servis->detailJasas as $jasaDetail)

                            <tr
                                data-item-row
                                data-kind="jasa"
                                data-detail-id="{{ $jasaDetail->id }}"
                                class="transition hover:bg-neutral-800/30"
                            >

                                <td class="py-3 font-semibold text-white">

                                    {{ $jasaDetail->jasa->nama_jasa
                                        ?? 'Jasa Servis'
                                    }}

                                </td>


                                <td class="py-3 text-center">

                                    <span class="rounded-full bg-sky-500/10 px-2 py-1 text-[9px] font-bold text-sky-400">
                                        JASA
                                    </span>

                                </td>


                                <td class="py-3 text-center font-bold">
                                    1
                                </td>


                                <td class="py-3 text-right font-mono">

                                    Rp {{ number_format(
                                        $jasaDetail->harga_saat_transaksi,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                <td class="py-3 text-right font-mono font-bold text-white">

                                    Rp {{ number_format(
                                        $jasaDetail->harga_saat_transaksi,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                @if(!$isSelesai)

                                    <td class="no-print py-3 text-center">

                                        <button
                                            type="button"
                                            data-url="{{ route(
                                                'admin.rekam-servis.jasa.hapus',
                                                [
                                                    $servis->id,
                                                    $jasaDetail->id
                                                ]
                                            ) }}"
                                            class="js-delete-item inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-400 transition hover:bg-rose-500/10 hover:text-rose-300"
                                            title="Hapus jasa"
                                        >
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>

                                    </td>

                                @endif

                            </tr>

                        @endforeach



                        {{-- =============================================
                            EMPTY STATE
                        ============================================== --}}
                        @if(
                            $servis->detailSpareparts->isEmpty()
                            &&
                            $servis->detailJasas->isEmpty()
                        )

                            <tr id="emptyTransactionRow">

                                <td
                                    colspan="6"
                                    class="py-8 text-center text-neutral-500"
                                >

                                    <i class="fa-solid fa-basket-shopping text-lg text-neutral-700"></i>

                                    <p class="mt-2 italic">
                                        Belum ada suku cadang atau jasa.
                                    </p>

                                </td>

                            </tr>

                        @endif

                    </tbody>

                </table>

            </div>

        </div>



        {{-- =====================================================
            TOTAL & REKOMENDASI
        ====================================================== --}}
        <div class="flex flex-col items-start justify-between gap-4 border-t border-neutral-800 pt-4 md:flex-row">

            {{-- =================================================
                CATATAN / GARANSI
            ================================================== --}}
            <div class="max-w-xs space-y-2 text-[11px] text-neutral-400">

                @if($servis->catatanRekomendasis->isNotEmpty())

                    <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-2.5 text-neutral-300">

                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-amber-400">
                            Catatan Rekomendasi:
                        </span>


                        <ul class="list-inside list-disc space-y-1">

                            @foreach($servis->catatanRekomendasis as $rek)

                                <li>
                                    {{ $rek->catatan }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                <p class="italic leading-5">
                    Garansi pengerjaan 1 minggu atau 1.000 KM.
                    Terima kasih atas kepercayaan Anda di SIRAKA BENGKEL.
                </p>

            </div>



            {{-- =================================================
                RINGKASAN HARGA
            ================================================== --}}
            <div class="nota-total-box ml-auto w-full space-y-2 rounded-2xl border border-neutral-700 bg-neutral-900/80 p-3.5 text-xs sm:w-72">

                {{-- Subtotal --}}
                <div class="flex justify-between text-neutral-300">

                    <span>
                        Subtotal Biaya:
                    </span>

                    <span
                        data-subtotal-label
                        class="font-mono font-bold text-white"
                    >
                        Rp {{ number_format(
                            $subtotalCalc,
                            0,
                            ',',
                            '.'
                        ) }}
                    </span>

                </div>


                {{-- Diskon --}}
                <div class="flex justify-between text-[11px] text-neutral-300">

                    <span>
                        Potongan Harga:
                    </span>

                    <span
                        id="labelNominalDiskon"
                        class="font-mono font-bold text-rose-400"
                    >
                        - Rp {{ number_format(
                            $diskonExisting,
                            0,
                            ',',
                            '.'
                        ) }}
                    </span>

                </div>


                {{-- Total --}}
                <div class="flex justify-between border-t border-neutral-700 pt-2 text-sm font-black text-white">

                    <span>
                        Total Tagihan:
                    </span>

                    <span
                        id="labelTotalBiaya"
                        class="font-mono text-sm text-brand-400"
                    >
                        Rp {{ number_format(
                            $totalCalc,
                            0,
                            ',',
                            '.'
                        ) }}
                    </span>

                </div>



                {{-- =============================================
                    INPUT DISKON & KONFIRMASI
                ============================================== --}}
                <div class="no-print border-t border-neutral-800 pt-2">

                    <form
                        action="{{ route(
                            'admin.rekam-servis.nota.konfirmasi',
                            $servis->id
                        ) }}"
                        method="POST"
                        id="formKonfirmasiNota"
                        class="space-y-2"
                    >

                        @csrf


                        @if(!$isSelesai)

                            <div>

                                <label
                                    for="diskonRibuanInput"
                                    class="mb-1 block text-[10px] font-bold text-neutral-400"
                                >
                                    Potongan Harga (Ribuan)
                                </label>


                                <div class="relative flex items-center">

                                    <span class="absolute left-2.5 text-xs font-bold text-neutral-400">
                                        Rp
                                    </span>


                                    <input
                                        type="number"
                                        id="diskonRibuanInput"
                                        value="{{ $inputRibuanExisting }}"
                                        min="0"
                                        step="1"
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-1.5 pl-8 pr-12 text-right font-mono text-xs font-bold text-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="0"
                                    >


                                    <span class="absolute right-2.5 text-xs font-bold text-neutral-400">
                                        .000
                                    </span>

                                </div>

                            </div>

                        @endif


                        <input
                            type="hidden"
                            name="diskon"
                            id="diskonRupiahInput"
                            value="{{ $diskonExisting }}"
                        >


                        <input
                            type="hidden"
                            name="metode_cetak"
                            id="metodeCetakInput"
                            value="{{ $servis->nota->metode_cetak ?? 'standar' }}"
                        >



                        <div>

                            @if($isSelesai)

                                <div class="flex w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-xl border border-neutral-700 bg-neutral-800 py-2 text-xs font-black text-emerald-400">

                                    <i class="fa-solid fa-lock"></i>

                                    <span>
                                        Transaksi Selesai & Terkunci
                                    </span>

                                </div>

                            @else

                                <button
                                    type="submit"
                                    id="btnSelesaikanServis"
                                    class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-emerald-500 py-2 text-xs font-black text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600"
                                >
                                    <i class="fa-solid fa-circle-check"></i>

                                    <span>
                                        Konfirmasi & Selesaikan Servis
                                    </span>
                                </button>

                            @endif

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    MODAL KONFIRMASI SIRAKA
========================================================== --}}
<div
    id="confirmModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 p-4 backdrop-blur-sm no-print"
    aria-hidden="true"
>
    <div
        id="confirmModalPanel"
        class="w-full max-w-sm rounded-2xl border border-neutral-800 bg-ink-900 p-5 shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirmModalTitle"
        aria-describedby="confirmModalMessage"
    >
        <div class="flex items-start gap-4">

            <div
                id="confirmModalIcon"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-500/10 text-rose-400"
            >
                <i class="fa-solid fa-trash"></i>
            </div>

            <div class="min-w-0 flex-1">
                <h3
                    id="confirmModalTitle"
                    class="text-sm font-extrabold text-white"
                >
                    Konfirmasi
                </h3>

                <p
                    id="confirmModalMessage"
                    class="mt-1.5 text-xs leading-5 text-neutral-400"
                >
                    Apakah Anda yakin ingin melanjutkan tindakan ini?
                </p>
            </div>

        </div>

        <div class="mt-5 flex justify-end gap-2 border-t border-neutral-800 pt-4">

            <button
                type="button"
                id="confirmModalCancel"
                class="rounded-xl bg-neutral-800 px-4 py-2.5 text-xs font-bold text-neutral-300 transition hover:bg-neutral-700 hover:text-white"
            >
                Batal
            </button>

            <button
                type="button"
                id="confirmModalOk"
                class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-rose-600"
            >
                <i class="fa-solid fa-trash"></i>
                <span>Hapus</span>
            </button>

        </div>
    </div>
</div>


{{-- =========================================================
    PRINT CSS
========================================================== --}}
<style>

@media print {

    .no-print,
    sidebar,
    header,
    nav,
    button,
    form {
        display: none !important;
    }


    body {
        background-color: white !important;
        color: black !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }


    #printArea {
        width: 100% !important;

        margin: 0 !important;
        padding: 0 !important;

        border: none !important;
        background: transparent !important;
        color: black !important;

        box-shadow: none !important;
    }


    #printArea * {
        color: black !important;
    }


    .logo-box {
        border-color: black !important;
        background-color: black !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }


    .nota-total-box {
        border: 1px solid #d1d5db !important;
        background-color: #f3f4f6 !important;
        color: black !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }


    .nota-total-box * {
        color: black !important;
    }


    #labelNominalDiskon {
        color: #b91c1c !important;
    }


    #labelTotalBiaya {
        color: #1d4ed8 !important;
    }



    /*
    |--------------------------------------------------------------------------
    | THERMAL
    |--------------------------------------------------------------------------
    */

    body.print-thermal {
        width: 78mm !important;
        font-size: 12px !important;
    }


    body.print-thermal #printArea {
        width: 78mm !important;
    }


    body.print-thermal .nota-header {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }


    body.print-thermal .nota-header > div:last-child {
        margin-top: 10px !important;
        width: 100% !important;
        text-align: center !important;
    }


    body.print-thermal .nota-header > div:last-child span {
        margin-left: auto !important;
        margin-right: auto !important;
    }


    body.print-thermal .nota-info {
        grid-template-columns: 1fr !important;
        text-align: center !important;
    }


    body.print-thermal .nota-info > div {
        text-align: center !important;
    }

}

</style>



{{-- =========================================================
    JAVASCRIPT POS
========================================================== --}}
<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | STATE
        |--------------------------------------------------------------------------
        */

        let subtotal =
            Number(
                @json($subtotalCalc)
            ) || 0;


        const csrfToken =
            @json(csrf_token());


        const inputRibuan =
            document.getElementById(
                'diskonRibuanInput'
            );


        const hiddenRupiah =
            document.getElementById(
                'diskonRupiahInput'
            );


        const labelDiskon =
            document.getElementById(
                'labelNominalDiskon'
            );


        const labelTotal =
            document.getElementById(
                'labelTotalBiaya'
            );


        const transactionItems =
            document.getElementById(
                'transactionItems'
            );


        /*
        |--------------------------------------------------------------------------
        | CUSTOM CONFIRM MODAL
        |--------------------------------------------------------------------------
        */

        const confirmModal =
            document.getElementById(
                'confirmModal'
            );

        const confirmModalPanel =
            document.getElementById(
                'confirmModalPanel'
            );

        const confirmModalTitle =
            document.getElementById(
                'confirmModalTitle'
            );

        const confirmModalMessage =
            document.getElementById(
                'confirmModalMessage'
            );

        const confirmModalIcon =
            document.getElementById(
                'confirmModalIcon'
            );

        const confirmModalCancel =
            document.getElementById(
                'confirmModalCancel'
            );

        const confirmModalOk =
            document.getElementById(
                'confirmModalOk'
            );


        function showConfirm(options = {}) {

            return new Promise(
                function (resolve) {

                    const title =
                        options.title
                        || 'Konfirmasi';

                    const message =
                        options.message
                        || 'Apakah Anda yakin ingin melanjutkan?';

                    const type =
                        options.type
                        || 'danger';

                    const confirmText =
                        options.confirmText
                        || 'Ya, Lanjutkan';


                    confirmModalTitle.textContent =
                        title;

                    confirmModalMessage.textContent =
                        message;


                    if (type === 'success') {

                        confirmModalIcon.className =
                            'grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-500/10 text-emerald-400';

                        confirmModalIcon.innerHTML =
                            '<i class="fa-solid fa-circle-check"></i>';

                        confirmModalOk.className =
                            'inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-600';

                        confirmModalOk.innerHTML =
                            '<i class="fa-solid fa-circle-check"></i><span>'
                            + escapeHtml(confirmText)
                            + '</span>';

                    } else {

                        confirmModalIcon.className =
                            'grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-500/10 text-rose-400';

                        confirmModalIcon.innerHTML =
                            '<i class="fa-solid fa-trash"></i>';

                        confirmModalOk.className =
                            'inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-rose-600';

                        confirmModalOk.innerHTML =
                            '<i class="fa-solid fa-trash"></i><span>'
                            + escapeHtml(confirmText)
                            + '</span>';
                    }


                    confirmModal.classList.remove(
                        'hidden'
                    );

                    confirmModal.classList.add(
                        'flex'
                    );

                    confirmModal.setAttribute(
                        'aria-hidden',
                        'false'
                    );

                    document.body.classList.add(
                        'overflow-hidden'
                    );


                    setTimeout(
                        function () {
                            confirmModalOk.focus();
                        },
                        0
                    );


                    let finished = false;


                    function cleanup(result) {

                        if (finished) {
                            return;
                        }

                        finished = true;


                        confirmModal.classList.add(
                            'hidden'
                        );

                        confirmModal.classList.remove(
                            'flex'
                        );

                        confirmModal.setAttribute(
                            'aria-hidden',
                            'true'
                        );

                        document.body.classList.remove(
                            'overflow-hidden'
                        );


                        confirmModalOk.onclick =
                            null;

                        confirmModalCancel.onclick =
                            null;

                        confirmModal.onclick =
                            null;

                        document.removeEventListener(
                            'keydown',
                            handleEscape
                        );


                        resolve(result);
                    }


                    function handleEscape(event) {

                        if (event.key === 'Escape') {
                            cleanup(false);
                        }
                    }


                    confirmModalOk.onclick =
                        function () {
                            cleanup(true);
                        };

                    confirmModalCancel.onclick =
                        function () {
                            cleanup(false);
                        };

                    confirmModal.onclick =
                        function (event) {

                            if (event.target === confirmModal) {
                                cleanup(false);
                            }
                        };

                    if (confirmModalPanel) {
                        confirmModalPanel.onclick =
                            function (event) {
                                event.stopPropagation();
                            };
                    }

                    document.addEventListener(
                        'keydown',
                        handleEscape
                    );
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function rupiah(value) {

            return 'Rp ' +
                Math.round(
                    Number(value) || 0
                ).toLocaleString(
                    'id-ID'
                );
        }


        function escapeHtml(value) {

            const div =
                document.createElement(
                    'div'
                );


            div.textContent =
                value ?? '';


            return div.innerHTML;
        }



        /*
        |--------------------------------------------------------------------------
        | TOAST
        |--------------------------------------------------------------------------
        */

        function showToast(
            message,
            type = 'success'
        ) {

            const oldToast =
                document.getElementById(
                    'ajaxToast'
                );


            if (oldToast) {
                oldToast.remove();
            }


            const toast =
                document.createElement(
                    'div'
                );


            const isSuccess =
                type === 'success';


            toast.id =
                'ajaxToast';


            toast.className =
                'fixed right-5 top-20 z-[9999] flex max-w-sm items-center gap-3 rounded-xl border px-4 py-3 text-xs font-bold shadow-2xl ' +
                (
                    isSuccess
                        ? 'border-emerald-500/30 bg-neutral-900 text-emerald-400'
                        : 'border-rose-500/30 bg-neutral-900 text-rose-400'
                );


            toast.innerHTML = `

                <i class="fa-solid ${
                    isSuccess
                        ? 'fa-circle-check'
                        : 'fa-triangle-exclamation'
                }"></i>

                <span>
                    ${escapeHtml(message)}
                </span>

            `;


            document.body.appendChild(
                toast
            );


            setTimeout(
                function () {

                    toast.remove();

                },
                2500
            );
        }



        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        function hitungTotalBiaya() {

            let angkaRibuan =
                parseFloat(
                    inputRibuan
                        ? inputRibuan.value
                        : @json($inputRibuanExisting)
                )
                || 0;


            if (angkaRibuan < 0) {

                angkaRibuan = 0;


                if (inputRibuan) {

                    inputRibuan.value = 0;

                }
            }


            const nominalDiskon =
                angkaRibuan * 1000;


            const totalTagihan =
                Math.max(
                    0,
                    subtotal - nominalDiskon
                );


            if (hiddenRupiah) {

                hiddenRupiah.value =
                    Math.round(
                        nominalDiskon
                    );
            }


            if (labelDiskon) {

                labelDiskon.textContent =
                    '- ' +
                    rupiah(
                        nominalDiskon
                    );
            }


            if (labelTotal) {

                labelTotal.textContent =
                    rupiah(
                        totalTagihan
                    );
            }


            const subtotalLabel =
                document.querySelector(
                    '[data-subtotal-label]'
                );


            if (subtotalLabel) {

                subtotalLabel.textContent =
                    rupiah(
                        subtotal
                    );
            }
        }


        window.setNotaSubtotal =
            function (value) {

                subtotal =
                    Number(value) || 0;


                hitungTotalBiaya();
            };


        if (inputRibuan) {

            inputRibuan.addEventListener(
                'input',
                hitungTotalBiaya
            );


            inputRibuan.addEventListener(
                'change',
                hitungTotalBiaya
            );
        }



        /*
        |--------------------------------------------------------------------------
        | INFO SPAREPART
        |--------------------------------------------------------------------------
        */

        const sparepartSelect =
            document.getElementById(
                'sparepartSelect'
            );


        const sparepartInfo =
            document.getElementById(
                'sparepartInfo'
            );


        const sparepartStock =
            document.getElementById(
                'sparepartStock'
            );


        const sparepartPrice =
            document.getElementById(
                'sparepartPrice'
            );


        const sparepartQty =
            document.getElementById(
                'sparepartQty'
            );


        if (sparepartSelect) {

            sparepartSelect.addEventListener(
                'change',
                function () {

                    const option =
                        this.options[
                            this.selectedIndex
                        ];


                    if (!this.value) {

                        sparepartInfo.classList.add(
                            'hidden'
                        );


                        if (sparepartQty) {

                            sparepartQty.removeAttribute(
                                'max'
                            );
                        }


                        return;
                    }


                    const stock =
                        Number(
                            option.dataset.stock
                        ) || 0;


                    const price =
                        Number(
                            option.dataset.price
                        ) || 0;


                    sparepartStock.textContent =
                        stock + ' Unit';


                    sparepartPrice.textContent =
                        rupiah(price);


                    if (sparepartQty) {

                        sparepartQty.max =
                            Math.max(
                                1,
                                stock
                            );


                        if (
                            Number(
                                sparepartQty.value
                            )
                            >
                            stock
                            &&
                            stock > 0
                        ) {

                            sparepartQty.value =
                                stock;
                        }
                    }


                    sparepartInfo.classList.remove(
                        'hidden'
                    );
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | INFO JASA
        |--------------------------------------------------------------------------
        */

        const jasaSelect =
            document.getElementById(
                'jasaSelect'
            );


        const jasaInfo =
            document.getElementById(
                'jasaInfo'
            );


        const jasaMasterPrice =
            document.getElementById(
                'jasaMasterPrice'
            );


        if (jasaSelect) {

            jasaSelect.addEventListener(
                'change',
                function () {

                    const option =
                        this.options[
                            this.selectedIndex
                        ];


                    if (!this.value) {

                        jasaInfo.classList.add(
                            'hidden'
                        );

                        return;
                    }


                    jasaMasterPrice.textContent =
                        rupiah(
                            option.dataset.price
                        );


                    jasaInfo.classList.remove(
                        'hidden'
                    );
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | HTTP JSON
        |--------------------------------------------------------------------------
        */

        async function requestJson(
            url,
            options = {}
        ) {

            options.headers = {
                ...(options.headers || {}),

                'Accept':
                    'application/json',

                'X-CSRF-TOKEN':
                    csrfToken,
            };


            const response =
                await fetch(
                    url,
                    options
                );


            let data;


            try {

                data =
                    await response.json();

            } catch (error) {

                throw new Error(
                    'Respons server tidak valid.'
                );
            }


            if (!response.ok) {

                let message =
                    data.message
                    ||
                    'Terjadi kesalahan.';


                if (data.errors) {

                    const firstError =
                        Object.values(
                            data.errors
                        )[0];


                    if (
                        Array.isArray(
                            firstError
                        )
                    ) {

                        message =
                            firstError[0];
                    }
                }


                throw new Error(
                    message
                );
            }


            return data;
        }



        /*
        |--------------------------------------------------------------------------
        | EMPTY STATE
        |--------------------------------------------------------------------------
        */

        function removeEmptyState() {

            const empty =
                document.getElementById(
                    'emptyTransactionRow'
                );


            if (empty) {
                empty.remove();
            }
        }


        function checkEmptyState() {

            if (!transactionItems) {
                return;
            }


            const rows =
                transactionItems.querySelectorAll(
                    '[data-item-row]'
                );


            if (rows.length > 0) {
                return;
            }


            transactionItems.innerHTML = `

                <tr id="emptyTransactionRow">

                    <td
                        colspan="6"
                        class="py-8 text-center text-neutral-500"
                    >

                        <i class="fa-solid fa-basket-shopping text-lg text-neutral-700"></i>

                        <p class="mt-2 italic">
                            Belum ada suku cadang atau jasa.
                        </p>

                    </td>

                </tr>

            `;
        }



        /*
        |--------------------------------------------------------------------------
        | RENDER SPAREPART
        |--------------------------------------------------------------------------
        */

        function renderSparepart(
            item
        ) {

            if (!transactionItems) {
                return;
            }


            let row =
                transactionItems.querySelector(
                    '[data-kind="sparepart"][data-detail-id="'
                    +
                    item.id
                    +
                    '"]'
                );


            const html = `

                <td class="py-3 font-semibold text-white">
                    ${escapeHtml(item.name)}
                </td>


                <td class="py-3 text-center">

                    <span class="rounded-full bg-brand-500/10 px-2 py-1 text-[9px] font-bold text-brand-400">
                        BARANG
                    </span>

                </td>


                <td class="py-3 text-center">

                    <div class="no-print inline-flex items-center overflow-hidden rounded-lg border border-neutral-700 bg-neutral-800">

                        <button
                            type="button"
                            data-qty-action="-1"
                            data-url="${item.qty_url}"
                            class="js-qty-button grid h-7 w-7 place-items-center text-neutral-400 transition hover:bg-neutral-700 hover:text-white"
                        >
                            <i class="fa-solid fa-minus text-[8px]"></i>
                        </button>


                        <span class="js-item-qty min-w-[32px] px-1 font-bold text-white">
                            ${item.qty}
                        </span>


                        <button
                            type="button"
                            data-qty-action="1"
                            data-url="${item.qty_url}"
                            class="js-qty-button grid h-7 w-7 place-items-center text-neutral-400 transition hover:bg-neutral-700 hover:text-white"
                        >
                            <i class="fa-solid fa-plus text-[8px]"></i>
                        </button>

                    </div>


                    <span class="hidden print:inline">
                        ${item.qty}
                    </span>

                </td>


                <td class="py-3 text-right font-mono">
                    ${rupiah(item.unit_price)}
                </td>


                <td class="js-item-subtotal py-3 text-right font-mono font-bold text-white">
                    ${rupiah(item.subtotal)}
                </td>


                <td class="no-print py-3 text-center">

                    <button
                        type="button"
                        data-url="${item.delete_url}"
                        class="js-delete-item inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-400 transition hover:bg-rose-500/10 hover:text-rose-300"
                    >
                        <i class="fa-solid fa-trash text-[10px]"></i>
                    </button>

                </td>

            `;


            if (row) {

                row.innerHTML =
                    html;

                return;
            }


            row =
                document.createElement(
                    'tr'
                );


            row.setAttribute(
                'data-item-row',
                ''
            );


            row.setAttribute(
                'data-kind',
                'sparepart'
            );


            row.setAttribute(
                'data-detail-id',
                item.id
            );


            row.className =
                'transition hover:bg-neutral-800/30';


            row.innerHTML =
                html;


            transactionItems.appendChild(
                row
            );
        }



        /*
        |--------------------------------------------------------------------------
        | RENDER JASA
        |--------------------------------------------------------------------------
        */

        function renderJasa(
            item
        ) {

            if (!transactionItems) {
                return;
            }


            const row =
                document.createElement(
                    'tr'
                );


            row.setAttribute(
                'data-item-row',
                ''
            );


            row.setAttribute(
                'data-kind',
                'jasa'
            );


            row.setAttribute(
                'data-detail-id',
                item.id
            );


            row.className =
                'transition hover:bg-neutral-800/30';


            row.innerHTML = `

                <td class="py-3 font-semibold text-white">
                    ${escapeHtml(item.name)}
                </td>


                <td class="py-3 text-center">

                    <span class="rounded-full bg-sky-500/10 px-2 py-1 text-[9px] font-bold text-sky-400">
                        JASA
                    </span>

                </td>


                <td class="py-3 text-center font-bold">
                    1
                </td>


                <td class="py-3 text-right font-mono">
                    ${rupiah(item.unit_price)}
                </td>


                <td class="py-3 text-right font-mono font-bold text-white">
                    ${rupiah(item.subtotal)}
                </td>


                <td class="no-print py-3 text-center">

                    <button
                        type="button"
                        data-url="${item.delete_url}"
                        class="js-delete-item inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-400 transition hover:bg-rose-500/10 hover:text-rose-300"
                    >
                        <i class="fa-solid fa-trash text-[10px]"></i>
                    </button>

                </td>

            `;


            transactionItems.appendChild(
                row
            );
        }



        /*
        |--------------------------------------------------------------------------
        | SUBMIT AJAX FORM
        |--------------------------------------------------------------------------
        */

        async function submitAjaxForm(
            form,
            kind
        ) {

            const button =
                form.querySelector(
                    '.js-submit-button'
                );


            if (!button) {
                return;
            }


            const originalHtml =
                button.innerHTML;


            button.disabled =
                true;


            button.innerHTML = `

                <i class="fa-solid fa-spinner fa-spin"></i>

                Menyimpan...

            `;


            try {

                const formData =
                    new FormData(form);


                const data =
                    await requestJson(
                        form.action,
                        {
                            method:
                                'POST',

                            body:
                                formData,
                        }
                    );


                removeEmptyState();


                if (
                    kind
                    ===
                    'sparepart'
                ) {

                    renderSparepart(
                        data.detail
                    );


                    const qtyInput =
                        form.querySelector(
                            '[name="qty"]'
                        );


                    if (qtyInput) {

                        qtyInput.value =
                            1;
                    }


                    if (sparepartSelect) {

                        sparepartSelect.value =
                            '';
                    }


                    if (sparepartInfo) {

                        sparepartInfo.classList.add(
                            'hidden'
                        );
                    }

                } else {

                    renderJasa(
                        data.detail
                    );


                    if (jasaSelect) {

                        jasaSelect.value =
                            '';
                    }


                    if (jasaInfo) {

                        jasaInfo.classList.add(
                            'hidden'
                        );
                    }


                    const customInput =
                        form.querySelector(
                            '[name="harga_custom"]'
                        );


                    if (customInput) {

                        customInput.value =
                            '';
                    }
                }


                window.setNotaSubtotal(
                    data.subtotal
                );


                showToast(
                    data.message
                );

            } catch (error) {

                showToast(
                    error.message,
                    'error'
                );

            } finally {

                button.disabled =
                    false;


                button.innerHTML =
                    originalHtml;
            }
        }



        /*
        |--------------------------------------------------------------------------
        | FORM SPAREPART
        |--------------------------------------------------------------------------
        */

        const formSparepart =
            document.getElementById(
                'formTambahSparepart'
            );


        if (formSparepart) {

            formSparepart.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();


                    submitAjaxForm(
                        this,
                        'sparepart'
                    );
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | FORM JASA
        |--------------------------------------------------------------------------
        */

        const formJasa =
            document.getElementById(
                'formTambahJasa'
            );


        if (formJasa) {

            formJasa.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();


                    submitAjaxForm(
                        this,
                        'jasa'
                    );
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | TABEL EVENT
        |--------------------------------------------------------------------------
        */

        if (transactionItems) {

            transactionItems.addEventListener(
                'click',
                async function (event) {

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS ITEM
                    |--------------------------------------------------------------------------
                    */

                    const deleteButton =
                        event.target.closest(
                            '.js-delete-item'
                        );


                    if (deleteButton) {

                        const yakin =
                            await showConfirm({
                                title:
                                    'Hapus Item Transaksi',

                                message:
                                    'Item ini akan dihapus dari rincian transaksi. Tindakan ini tidak dapat dibatalkan.',

                                confirmText:
                                    'Hapus Item',

                                type:
                                    'danger',
                            });


                        if (!yakin) {
                            return;
                        }


                        const row =
                            deleteButton.closest(
                                '[data-item-row]'
                            );


                        deleteButton.disabled =
                            true;


                        try {

                            const data =
                                await requestJson(
                                    deleteButton.dataset.url,
                                    {
                                        method:
                                            'DELETE',
                                    }
                                );


                            if (row) {
                                row.remove();
                            }


                            window.setNotaSubtotal(
                                data.subtotal
                            );


                            checkEmptyState();


                            showToast(
                                data.message
                            );

                        } catch (error) {

                            deleteButton.disabled =
                                false;


                            showToast(
                                error.message,
                                'error'
                            );
                        }


                        return;
                    }



                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE QTY
                    |--------------------------------------------------------------------------
                    */

                    const qtyButton =
                        event.target.closest(
                            '.js-qty-button'
                        );


                    if (!qtyButton) {
                        return;
                    }


                    const row =
                        qtyButton.closest(
                            '[data-item-row]'
                        );


                    const qtyLabel =
                        row?.querySelector(
                            '.js-item-qty'
                        );


                    if (!qtyLabel) {
                        return;
                    }


                    let qty =
                        parseInt(
                            qtyLabel.textContent
                        ) || 1;


                    qty +=
                        parseInt(
                            qtyButton.dataset.qtyAction
                        ) || 0;


                    /*
                     * Minimum qty = 1.
                     */
                    if (qty < 1) {
                        return;
                    }


                    qtyButton.disabled =
                        true;


                    try {

                        const data =
                            await requestJson(
                                qtyButton.dataset.url,
                                {
                                    method:
                                        'PATCH',

                                    headers: {
                                        'Content-Type':
                                            'application/json',
                                    },

                                    body:
                                        JSON.stringify({
                                            qty: qty,
                                        }),
                                }
                            );


                        qtyLabel.textContent =
                            data.qty;


                        const printQty =
                            row.querySelector(
                                '.print\\:inline'
                            );


                        if (printQty) {

                            printQty.textContent =
                                data.qty;
                        }


                        const itemSubtotal =
                            row.querySelector(
                                '.js-item-subtotal'
                            );


                        if (itemSubtotal) {

                            itemSubtotal.textContent =
                                rupiah(
                                    data.item_subtotal
                                );
                        }


                        window.setNotaSubtotal(
                            data.subtotal
                        );

                    } catch (error) {

                        showToast(
                            error.message,
                            'error'
                        );

                    } finally {

                        qtyButton.disabled =
                            false;
                    }
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | KONFIRMASI SELESAIKAN SERVIS
        |--------------------------------------------------------------------------
        */

        const formKonfirmasiNota =
            document.getElementById(
                'formKonfirmasiNota'
            );


        if (formKonfirmasiNota) {

            formKonfirmasiNota.addEventListener(
                'submit',
                async function (event) {

                    event.preventDefault();


                    const yakin =
                        await showConfirm({
                            title:
                                'Selesaikan Servis',

                            message:
                                'Setelah dikonfirmasi, transaksi akan dikunci dan item sparepart maupun jasa tidak dapat diubah lagi.',

                            confirmText:
                                'Ya, Selesaikan',

                            type:
                                'success',
                        });


                    if (!yakin) {
                        return;
                    }


                    const submitButton =
                        document.getElementById(
                            'btnSelesaikanServis'
                        );


                    if (submitButton) {

                        submitButton.disabled =
                            true;

                        submitButton.innerHTML = `
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <span>Menyelesaikan...</span>
                        `;
                    }


                    HTMLFormElement.prototype.submit.call(
                        formKonfirmasiNota
                    );
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | INITIAL CALCULATION
        |--------------------------------------------------------------------------
        */

        hitungTotalBiaya();

    }
);



/*
|--------------------------------------------------------------------------
| CETAK
|--------------------------------------------------------------------------
*/

function pilihFormatDanCetak(
    metode
) {

    const inputMetode =
        document.getElementById(
            'metodeCetakInput'
        );


    if (inputMetode) {

        inputMetode.value =
            metode;
    }


    if (metode === 'thermal') {

        document.body.classList.add(
            'print-thermal'
        );

    } else {

        document.body.classList.remove(
            'print-thermal'
        );
    }


    window.print();
}

</script>

@endsection