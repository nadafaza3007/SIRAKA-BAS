@extends('layouts.admin')

@section('title', 'Data Konsumen - SIRAKA')

@section('content')

{{-- =========================================================
    NOTIFIKASI
========================================================== --}}
@if(session('success'))
    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-bold text-emerald-400 shadow-lg">
        <i class="fa-solid fa-circle-check text-base"></i>

        <span>
            {{ session('success') }}
        </span>
    </div>
@endif


@if(session('error'))
    <div class="mb-6 flex items-center justify-between rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-bold text-rose-400 shadow-lg">

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>

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
    HEADER
========================================================== --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div>
        <h2 class="text-xl font-extrabold tracking-tight text-white">
            Data Konsumen & Kendaraan
        </h2>

        <p class="mt-1 text-xs text-neutral-500">
            Kelola identitas konsumen dan seluruh kendaraan yang
            terhubung dalam satu data pelanggan.
        </p>
    </div>


    <button
        type="button"
        onclick="openModal('modalTambahKonsumen')"
        class="flex w-fit items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600"
    >
        <i class="fa-solid fa-user-plus"></i>

        <span>
            Tambah Konsumen Baru
        </span>
    </button>

</div>



{{-- =========================================================
    TABEL DATA KONSUMEN
========================================================== --}}
<div class="overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">

    <div class="overflow-x-auto">

        <table class="w-full min-w-[950px] text-left text-xs text-neutral-400">

            <thead class="border-b border-neutral-800 bg-black/40 font-bold uppercase text-neutral-500">

                <tr>

                    <th class="p-4">
                        Konsumen
                    </th>

                    <th class="p-4">
                        No. HP / WhatsApp
                    </th>

                    <th class="p-4">
                        Kendaraan Terdaftar
                    </th>

                    <th class="p-4 text-center">
                        Jumlah Unit
                    </th>

                    <th class="p-4 text-right">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-neutral-800">

                @forelse ($konsumen as $item)

                    <tr class="transition hover:bg-neutral-800/40">

                        {{-- =================================================
                            KONSUMEN
                        ================================================== --}}
                        <td class="p-4">

                            <div class="font-bold text-white">
                                {{ $item->nama_lengkap }}
                            </div>

                            <div class="mt-1 text-[10px] text-neutral-600">
                                ID Konsumen #{{ $item->id }}
                            </div>

                        </td>


                        {{-- =================================================
                            NO HP
                        ================================================== --}}
                        <td class="p-4">

                            <div class="inline-flex items-center gap-2 font-mono text-neutral-300">

                                <i class="fa-brands fa-whatsapp text-emerald-500"></i>

                                {{ $item->no_hp }}

                            </div>

                        </td>


                        {{-- =================================================
                            KENDARAAN
                        ================================================== --}}
                        <td class="p-4">

                            <div class="flex max-w-xl flex-wrap gap-2">

                                @forelse($item->kendaraans as $k)

                                    <div class="rounded-lg border border-neutral-700 bg-neutral-800 px-2.5 py-1.5 text-[11px]">

                                        <span class="font-extrabold uppercase tracking-wide text-brand-400">
                                            {{ $k->plat_nomor }}
                                        </span>

                                        <span class="ml-1 text-neutral-400">
                                            {{ $k->merk }} {{ $k->tipe_model }}
                                        </span>

                                        @if($k->tahun)
                                            <span class="ml-1 text-neutral-600">
                                                • {{ $k->tahun }}
                                            </span>
                                        @endif

                                    </div>

                                @empty

                                    <span class="inline-flex items-center gap-1.5 text-[11px] italic text-neutral-600">

                                        <i class="fa-solid fa-car-side"></i>

                                        Belum ada kendaraan

                                    </span>

                                @endforelse

                            </div>

                        </td>


                        {{-- =================================================
                            JUMLAH UNIT
                        ================================================== --}}
                        <td class="p-4 text-center">

                            <span class="inline-flex items-center gap-1.5 rounded-full border border-neutral-700 bg-neutral-800 px-2.5 py-1 text-[10px] font-bold text-neutral-300">

                                <i class="fa-solid fa-car text-brand-400"></i>

                                {{ $item->kendaraans->count() }} Unit

                            </span>

                        </td>


                        {{-- =================================================
                            AKSI
                        ================================================== --}}
                        <td class="p-4 text-right">

                            <a
                                href="{{ route('admin.konsumen.edit', $item->id) }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-brand-500/20 bg-brand-500/10 px-3.5 py-2 text-[11px] font-bold text-brand-400 transition hover:border-brand-500 hover:bg-brand-500 hover:text-white"
                            >
                                <i class="fa-solid fa-sliders"></i>

                                Kelola
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="p-10 text-center"
                        >

                            <div class="mx-auto flex max-w-sm flex-col items-center">

                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 text-neutral-600">
                                    <i class="fa-solid fa-users"></i>
                                </div>

                                <p class="mt-3 font-bold text-neutral-400">
                                    Belum ada data konsumen
                                </p>

                                <p class="mt-1 text-[11px] leading-5 text-neutral-600">
                                    Tambahkan konsumen baru beserta kendaraan
                                    pertamanya untuk mulai menggunakan sistem.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>



{{-- =========================================================
    MODAL TAMBAH KONSUMEN BARU
========================================================== --}}
<div
    id="modalTambahKonsumen"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
>

    <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-neutral-800 bg-ink-900 p-6 shadow-2xl">


        {{-- Header modal --}}
        <div class="mb-5 flex items-center justify-between border-b border-neutral-800 pb-4">

            <div>

                <h3 class="text-sm font-extrabold text-white">
                    Tambah Konsumen Baru
                </h3>

                <p class="mt-1 text-[11px] text-neutral-500">
                    Tambahkan identitas konsumen beserta satu atau
                    beberapa kendaraan sekaligus.
                </p>

            </div>


            <button
                type="button"
                onclick="closeModal('modalTambahKonsumen')"
                class="text-neutral-500 transition hover:text-white"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <form
            action="{{ route('admin.konsumen.store') }}"
            method="POST"
            class="space-y-5 text-xs"
        >

            @csrf


            {{-- =====================================================
                1. IDENTITAS KONSUMEN
            ====================================================== --}}
            <section>

                <div class="mb-3">

                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-brand-400">
                        1. Identitas Konsumen
                    </span>

                    <p class="mt-1 text-[10px] leading-4 text-neutral-500">
                        Nomor HP digunakan untuk menghubungkan akun
                        pelanggan dengan data konsumen bengkel.
                    </p>

                </div>


                <div class="space-y-3">

                    <div>

                        <label class="mb-1 block font-bold text-neutral-300">
                            Nama Lengkap *
                        </label>

                        <input
                            type="text"
                            name="nama_lengkap"
                            value="{{ old('nama_lengkap') }}"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                            placeholder="Contoh: Budi Santoso"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block font-bold text-neutral-300">
                            No. HP / WhatsApp *
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            value="{{ old('no_hp') }}"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 font-mono text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                            placeholder="081234567890"
                        >

                    </div>

                </div>

            </section>



            <hr class="border-neutral-800">



            {{-- =====================================================
                2. DATA KENDARAAN
            ====================================================== --}}
            <section>

                <div class="mb-3">

                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-brand-400">
                        2. Data Kendaraan
                    </span>

                    <p class="mt-1 text-[10px] leading-4 text-neutral-500">
                        Minimal satu kendaraan. Kendaraan lainnya dapat
                        ditambahkan sekarang atau melalui menu Kelola.
                    </p>

                </div>


                <div
                    id="kendaraanBaruContainer"
                    data-next-index="1"
                    class="space-y-4"
                >

                    {{-- Kendaraan pertama --}}
                    <div class="vehicle-item rounded-2xl border border-neutral-800 bg-black/20 p-4">

                        <div class="mb-4 flex items-center justify-between">

                            <div class="flex items-center gap-2">

                                <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-400">
                                    <i class="fa-solid fa-car"></i>
                                </div>

                                <span class="font-bold text-white">
                                    Kendaraan 1
                                </span>

                            </div>


                            <span class="text-[10px] text-neutral-600">
                                Kendaraan utama
                            </span>

                        </div>


                        <div class="space-y-3">

                            <div>

                                <label class="mb-1 block font-bold text-neutral-300">
                                    Plat Nomor *
                                </label>

                                <input
                                    type="text"
                                    name="kendaraan[0][plat_nomor]"
                                    required
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 font-bold uppercase tracking-wide text-brand-400 placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    placeholder="BM 1234 ABC"
                                >

                            </div>


                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                                <div>

                                    <label class="mb-1 block font-bold text-neutral-300">
                                        Merk *
                                    </label>

                                    <input
                                        type="text"
                                        name="kendaraan[0][merk]"
                                        required
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="Toyota"
                                    >

                                </div>


                                <div>

                                    <label class="mb-1 block font-bold text-neutral-300">
                                        Tipe / Model *
                                    </label>

                                    <input
                                        type="text"
                                        name="kendaraan[0][tipe_model]"
                                        required
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="Avanza Veloz"
                                    >

                                </div>

                            </div>


                            <div>

                                <label class="mb-1 block font-bold text-neutral-300">
                                    Tahun
                                </label>

                                <input
                                    type="number"
                                    name="kendaraan[0][tahun]"
                                    min="1900"
                                    max="{{ date('Y') + 1 }}"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    placeholder="{{ date('Y') }}"
                                >

                            </div>

                        </div>

                    </div>

                </div>


                <button
                    type="button"
                    onclick="tambahFormKendaraan()"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-brand-500/40 bg-brand-500/5 px-4 py-3 font-bold text-brand-400 transition hover:border-brand-500 hover:bg-brand-500/10"
                >
                    <i class="fa-solid fa-plus"></i>

                    Tambah Kendaraan Lagi
                </button>

            </section>



            {{-- =====================================================
                ACTION
            ====================================================== --}}
            <div class="flex justify-end gap-2 border-t border-neutral-800 pt-4">

                <button
                    type="button"
                    onclick="closeModal('modalTambahKonsumen')"
                    class="rounded-xl bg-neutral-800 px-4 py-2.5 font-bold text-neutral-200 transition hover:bg-neutral-700"
                >
                    Batal
                </button>


                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600"
                >
                    <i class="fa-solid fa-floppy-disk"></i>

                    Simpan Data
                </button>

            </div>

        </form>

    </div>

</div>

@endsection



@push('scripts')

<script>

    function openModal(id) {

        const modal =
            document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add(
            'overflow-hidden'
        );
    }


    function closeModal(id) {

        const modal =
            document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.classList.remove(
            'overflow-hidden'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Tambah Form Kendaraan Baru
    |--------------------------------------------------------------------------
    */
    function tambahFormKendaraan() {

        const container =
            document.getElementById(
                'kendaraanBaruContainer'
            );

        if (!container) {
            return;
        }


        let index =
            parseInt(
                container.dataset.nextIndex || '1'
            );


        const nomor =
            index + 1;


        const wrapper =
            document.createElement('div');


        wrapper.className =
            'vehicle-item rounded-2xl border border-neutral-800 bg-black/20 p-4';


        wrapper.innerHTML = `

            <div class="mb-4 flex items-center justify-between gap-3">

                <div class="flex items-center gap-2">

                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-400">
                        <i class="fa-solid fa-car"></i>
                    </div>

                    <span class="font-bold text-white">
                        Kendaraan ${nomor}
                    </span>

                </div>


                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-500/10 px-2.5 py-1.5 text-[10px] font-bold text-rose-400 transition hover:bg-rose-500 hover:text-white"
                    onclick="this.closest('.vehicle-item').remove()"
                >
                    <i class="fa-solid fa-trash"></i>
                    Hapus
                </button>

            </div>


            <div class="space-y-3">

                <div>

                    <label class="mb-1 block font-bold text-neutral-300">
                        Plat Nomor *
                    </label>

                    <input
                        type="text"
                        name="kendaraan[${index}][plat_nomor]"
                        required
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 font-bold uppercase tracking-wide text-brand-400 placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                        placeholder="BM 1234 ABC"
                    >

                </div>


                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <div>

                        <label class="mb-1 block font-bold text-neutral-300">
                            Merk *
                        </label>

                        <input
                            type="text"
                            name="kendaraan[${index}][merk]"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                            placeholder="Toyota"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block font-bold text-neutral-300">
                            Tipe / Model *
                        </label>

                        <input
                            type="text"
                            name="kendaraan[${index}][tipe_model]"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                            placeholder="Avanza Veloz"
                        >

                    </div>

                </div>


                <div>

                    <label class="mb-1 block font-bold text-neutral-300">
                        Tahun
                    </label>

                    <input
                        type="number"
                        name="kendaraan[${index}][tahun]"
                        min="1900"
                        max="${new Date().getFullYear() + 1}"
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                        placeholder="${new Date().getFullYear()}"
                    >

                </div>

            </div>
        `;


        container.appendChild(
            wrapper
        );


        container.dataset.nextIndex =
            index + 1;
    }


    /*
    |--------------------------------------------------------------------------
    | Escape untuk menutup modal
    |--------------------------------------------------------------------------
    */
    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key !== 'Escape') {
                return;
            }


            closeModal(
                'modalTambahKonsumen'
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Jika validasi Tambah Konsumen gagal,
    | buka lagi modal secara otomatis.
    |--------------------------------------------------------------------------
    */
    @if(
        $errors->any()
        && old('nama_lengkap')
    )

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                openModal(
                    'modalTambahKonsumen'
                );

            }
        );

    @endif

</script>

@endpush