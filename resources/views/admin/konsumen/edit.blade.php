@extends('layouts.admin')

@section('title', 'Kelola Konsumen - SIRAKA')

@section('content')

{{-- =========================================================
    HEADER
========================================================== --}}
<div class="mb-6">

    <a
        href="{{ route('admin.konsumen.index') }}"
        class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-neutral-500 transition hover:text-white"
    >
        <i class="fa-solid fa-arrow-left"></i>

        Kembali ke Data Konsumen
    </a>


    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-brand-400">
                Manajemen Pelanggan
            </p>

            <h2 class="mt-1 text-xl font-extrabold tracking-tight text-white">
                Kelola Konsumen
            </h2>

            <p class="mt-1 text-xs text-neutral-500">
                Perbarui identitas pelanggan dan kelola seluruh
                kendaraan yang terhubung.
            </p>

        </div>


        <div class="rounded-xl border border-neutral-800 bg-ink-900 px-4 py-3">

            <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                Total Kendaraan
            </p>

            <p class="mt-1 text-xl font-extrabold text-white">
                {{ $konsumen->kendaraans->count() }}

                <span class="text-xs font-bold text-brand-400">
                    Unit
                </span>
            </p>

        </div>

    </div>

</div>



{{-- =========================================================
    NOTIFIKASI
========================================================== --}}
@if(session('success'))

    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-bold text-emerald-400">

        <i class="fa-solid fa-circle-check"></i>

        {{ session('success') }}

    </div>

@endif


@if(session('error'))

    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-bold text-rose-400">

        <i class="fa-solid fa-triangle-exclamation"></i>

        {{ session('error') }}

    </div>

@endif


@if($errors->any())

    <div class="mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4">

        <div class="flex items-start gap-3">

            <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-400"></i>

            <div>

                <p class="text-xs font-bold text-rose-400">
                    Data belum dapat disimpan.
                </p>

                <ul class="mt-2 space-y-1 text-[11px] text-rose-300">

                    @foreach($errors->all() as $error)

                        <li>
                            • {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    </div>

@endif



<div class="grid gap-6 xl:grid-cols-3">


    {{-- =========================================================
        AREA UTAMA
    ========================================================== --}}
    <div class="space-y-6 xl:col-span-2">


        {{-- =====================================================
            IDENTITAS KONSUMEN
        ====================================================== --}}
        <section class="rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">

            <div class="border-b border-neutral-800 px-5 py-4">

                <div class="flex items-center gap-3">

                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500/10 text-brand-400">

                        <i class="fa-solid fa-user"></i>

                    </div>

                    <div>

                        <h3 class="text-sm font-extrabold text-white">
                            Identitas Konsumen
                        </h3>

                        <p class="mt-0.5 text-[10px] text-neutral-500">
                            Data utama pelanggan bengkel.
                        </p>

                    </div>

                </div>

            </div>


            <form
                action="{{ route('admin.konsumen.update', $konsumen->id) }}"
                method="POST"
                class="p-5"
            >

                @csrf
                @method('PUT')


                <div class="grid gap-4 md:grid-cols-2">

                    <div>

                        <label class="mb-1.5 block text-xs font-bold text-neutral-300">
                            Nama Lengkap *
                        </label>

                        <input
                            type="text"
                            name="nama_lengkap"
                            value="{{ old('nama_lengkap', $konsumen->nama_lengkap) }}"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-3 text-xs text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        >

                    </div>


                    <div>

                        <label class="mb-1.5 block text-xs font-bold text-neutral-300">
                            No. HP / WhatsApp *
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            value="{{ old('no_hp', $konsumen->no_hp) }}"
                            required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-3 font-mono text-xs text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        >

                    </div>

                </div>



                {{-- =================================================
                    KENDARAAN EXISTING
                ================================================== --}}
                @if($konsumen->kendaraans->isNotEmpty())

                    <div class="my-5 border-t border-neutral-800"></div>


                    <div class="mb-4">

                        <h4 class="text-xs font-extrabold text-white">
                            Kendaraan Terdaftar
                        </h4>

                        <p class="mt-1 text-[10px] leading-4 text-neutral-500">
                            Perubahan plat, merk, model, atau tahun tidak
                            menghapus riwayat servis karena histori tetap
                            terhubung melalui ID kendaraan.
                        </p>

                    </div>


                    <div class="space-y-4">

                        @foreach($konsumen->kendaraans as $kendaraan)

                            <div class="rounded-2xl border border-neutral-800 bg-black/20 p-4">

                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

                                    <div class="flex items-center gap-3">

                                        <div class="grid h-9 w-9 place-items-center rounded-xl bg-neutral-800 text-brand-400">

                                            <i class="fa-solid fa-car-side"></i>

                                        </div>


                                        <div>

                                            <p class="font-extrabold uppercase tracking-wide text-white">
                                                {{ $kendaraan->plat_nomor }}
                                            </p>

                                            <p class="mt-0.5 text-[10px] text-neutral-500">
                                                ID Kendaraan #{{ $kendaraan->id }}
                                            </p>

                                        </div>

                                    </div>


                                    @if(method_exists($kendaraan, 'rekamServis'))

                                        <span class="rounded-full bg-neutral-800 px-2.5 py-1 text-[10px] font-bold text-neutral-400">
                                            Kendaraan terhubung
                                        </span>

                                    @endif

                                </div>


                                <div class="grid gap-3 md:grid-cols-4">

                                    <div class="md:col-span-1">

                                        <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                            Plat Nomor *
                                        </label>

                                        <input
                                            type="text"
                                            name="kendaraan[{{ $kendaraan->id }}][plat_nomor]"
                                            value="{{ old(
                                                'kendaraan.' . $kendaraan->id . '.plat_nomor',
                                                $kendaraan->plat_nomor
                                            ) }}"
                                            required
                                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 font-bold uppercase tracking-wide text-brand-400 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        >

                                    </div>


                                    <div>

                                        <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                            Merk *
                                        </label>

                                        <input
                                            type="text"
                                            name="kendaraan[{{ $kendaraan->id }}][merk]"
                                            value="{{ old(
                                                'kendaraan.' . $kendaraan->id . '.merk',
                                                $kendaraan->merk
                                            ) }}"
                                            required
                                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        >

                                    </div>


                                    <div>

                                        <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                            Tipe / Model *
                                        </label>

                                        <input
                                            type="text"
                                            name="kendaraan[{{ $kendaraan->id }}][tipe_model]"
                                            value="{{ old(
                                                'kendaraan.' . $kendaraan->id . '.tipe_model',
                                                $kendaraan->tipe_model
                                            ) }}"
                                            required
                                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        >

                                    </div>


                                    <div>

                                        <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                            Tahun
                                        </label>

                                        <input
                                            type="number"
                                            name="kendaraan[{{ $kendaraan->id }}][tahun]"
                                            value="{{ old(
                                                'kendaraan.' . $kendaraan->id . '.tahun',
                                                $kendaraan->tahun
                                            ) }}"
                                            min="1900"
                                            max="{{ date('Y') + 1 }}"
                                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        >

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif



                <div class="mt-5 flex justify-end border-t border-neutral-800 pt-4">

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>

                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </section>



        {{-- =====================================================
            TAMBAH KENDARAAN
        ====================================================== --}}
        <section class="rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">

            <button
                type="button"
                onclick="toggleTambahKendaraan()"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
            >

                <div class="flex items-center gap-3">

                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-car-side"></i>
                    </div>


                    <div>

                        <h3 class="text-sm font-extrabold text-white">
                            Tambah Kendaraan
                        </h3>

                        <p class="mt-0.5 text-[10px] text-neutral-500">
                            Hubungkan kendaraan baru ke konsumen ini.
                        </p>

                    </div>

                </div>


                <i
                    id="toggleTambahIcon"
                    class="fa-solid fa-chevron-down text-neutral-500 transition-transform"
                ></i>

            </button>


            <div
                id="formTambahKendaraan"
                class="hidden border-t border-neutral-800 p-5"
            >

                <form
                    action="{{ route(
                        'admin.konsumen.kendaraan.store',
                        $konsumen->id
                    ) }}"
                    method="POST"
                    class="space-y-4"
                >

                    @csrf


                    <div
                        id="kendaraanTambahContainer"
                        data-next-index="1"
                        class="space-y-4"
                    >

                        <div class="vehicle-new rounded-2xl border border-neutral-800 bg-black/20 p-4">

                            <div class="mb-4 flex items-center gap-2">

                                <div class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                    <i class="fa-solid fa-plus"></i>
                                </div>

                                <p class="text-xs font-bold text-white">
                                    Kendaraan Baru 1
                                </p>

                            </div>


                            <div class="grid gap-3 md:grid-cols-4">

                                <div>

                                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                        Plat Nomor *
                                    </label>

                                    <input
                                        type="text"
                                        name="kendaraan[0][plat_nomor]"
                                        required
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs font-bold uppercase tracking-wide text-brand-400 placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="BM 1234 ABC"
                                    >

                                </div>


                                <div>

                                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                        Merk *
                                    </label>

                                    <input
                                        type="text"
                                        name="kendaraan[0][merk]"
                                        required
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="Toyota"
                                    >

                                </div>


                                <div>

                                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                        Tipe / Model *
                                    </label>

                                    <input
                                        type="text"
                                        name="kendaraan[0][tipe_model]"
                                        required
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="Avanza"
                                    >

                                </div>


                                <div>

                                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                                        Tahun
                                    </label>

                                    <input
                                        type="number"
                                        name="kendaraan[0][tahun]"
                                        min="1900"
                                        max="{{ date('Y') + 1 }}"
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                        placeholder="{{ date('Y') }}"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <button
                        type="button"
                        onclick="tambahKendaraanBaru()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-emerald-500/30 bg-emerald-500/5 px-4 py-2.5 text-xs font-bold text-emerald-400 transition hover:border-emerald-500 hover:bg-emerald-500/10"
                    >
                        <i class="fa-solid fa-plus"></i>

                        Tambah Kendaraan Lagi
                    </button>


                    <div class="flex justify-end border-t border-neutral-800 pt-4">

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-600"
                        >
                            <i class="fa-solid fa-car-side"></i>

                            Simpan Kendaraan
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </div>



    {{-- =========================================================
        SIDEBAR INFORMASI
    ========================================================== --}}
    <aside class="space-y-4">


        {{-- Ringkasan --}}
        <div class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">

            <h3 class="text-xs font-extrabold text-white">
                Ringkasan Konsumen
            </h3>


            <dl class="mt-4 space-y-4 text-xs">

                <div>

                    <dt class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Nama
                    </dt>

                    <dd class="mt-1 font-semibold text-white">
                        {{ $konsumen->nama_lengkap }}
                    </dd>

                </div>


                <div>

                    <dt class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Nomor HP
                    </dt>

                    <dd class="mt-1 font-mono text-neutral-300">
                        {{ $konsumen->no_hp }}
                    </dd>

                </div>


                <div>

                    <dt class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Kendaraan
                    </dt>

                    <dd class="mt-1 font-semibold text-white">
                        {{ $konsumen->kendaraans->count() }} Unit
                    </dd>

                </div>

            </dl>

        </div>



        {{-- Daftar plat --}}
        <div class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">

            <h3 class="text-xs font-extrabold text-white">
                Plat Terdaftar
            </h3>


            <div class="mt-4 space-y-2">

                @forelse($konsumen->kendaraans as $kendaraan)

                    <div class="rounded-xl border border-neutral-800 bg-black/20 px-3 py-2.5">

                        <p class="text-xs font-extrabold uppercase tracking-wide text-brand-400">
                            {{ $kendaraan->plat_nomor }}
                        </p>

                        <p class="mt-1 text-[10px] text-neutral-500">
                            {{ $kendaraan->merk }}
                            {{ $kendaraan->tipe_model }}

                            @if($kendaraan->tahun)
                                • {{ $kendaraan->tahun }}
                            @endif
                        </p>

                    </div>

                @empty

                    <div class="rounded-xl border border-dashed border-neutral-700 p-4 text-center">

                        <i class="fa-solid fa-car text-neutral-700"></i>

                        <p class="mt-2 text-[10px] text-neutral-600">
                            Belum ada kendaraan.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>



        {{-- Informasi --}}
        <div class="rounded-2xl border border-sky-500/20 bg-sky-500/5 p-4">

            <div class="flex items-start gap-3">

                <i class="fa-solid fa-circle-info mt-0.5 text-sky-400"></i>

                <p class="text-[10px] leading-5 text-sky-300/80">
                    Kendaraan yang ditambahkan di halaman ini akan
                    otomatis tersedia pada pilihan plat ketika membuat
                    rekam servis baru.
                </p>

            </div>

        </div>

    </aside>

</div>

@endsection



@push('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | Toggle form tambah kendaraan
    |--------------------------------------------------------------------------
    */
    function toggleTambahKendaraan() {

        const form =
            document.getElementById(
                'formTambahKendaraan'
            );

        const icon =
            document.getElementById(
                'toggleTambahIcon'
            );


        form.classList.toggle('hidden');

        icon.classList.toggle(
            'rotate-180'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Tambah lebih dari satu kendaraan
    |--------------------------------------------------------------------------
    */
    function tambahKendaraanBaru() {

        const container =
            document.getElementById(
                'kendaraanTambahContainer'
            );


        let index =
            parseInt(
                container.dataset.nextIndex || '1'
            );


        const nomor =
            index + 1;


        const wrapper =
            document.createElement('div');


        wrapper.className =
            'vehicle-new rounded-2xl border border-neutral-800 bg-black/20 p-4';


        wrapper.innerHTML = `

            <div class="mb-4 flex items-center justify-between gap-3">

                <div class="flex items-center gap-2">

                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-plus"></i>
                    </div>

                    <p class="text-xs font-bold text-white">
                        Kendaraan Baru ${nomor}
                    </p>

                </div>


                <button
                    type="button"
                    onclick="this.closest('.vehicle-new').remove()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-500/10 px-2.5 py-1.5 text-[10px] font-bold text-rose-400 transition hover:bg-rose-500 hover:text-white"
                >
                    <i class="fa-solid fa-trash"></i>

                    Hapus
                </button>

            </div>


            <div class="grid gap-3 md:grid-cols-4">

                <div>

                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                        Plat Nomor *
                    </label>

                    <input
                        type="text"
                        name="kendaraan[${index}][plat_nomor]"
                        required
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs font-bold uppercase tracking-wide text-brand-400 placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        placeholder="BM 1234 ABC"
                    >

                </div>


                <div>

                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                        Merk *
                    </label>

                    <input
                        type="text"
                        name="kendaraan[${index}][merk]"
                        required
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        placeholder="Toyota"
                    >

                </div>


                <div>

                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                        Tipe / Model *
                    </label>

                    <input
                        type="text"
                        name="kendaraan[${index}][tipe_model]"
                        required
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        placeholder="Avanza"
                    >

                </div>


                <div>

                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">
                        Tahun
                    </label>

                    <input
                        type="number"
                        name="kendaraan[${index}][tahun]"
                        min="1900"
                        max="${new Date().getFullYear() + 1}"
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
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

</script>

@endpush