@extends('layouts.admin')

@section('title', 'Input Servis Baru - SIRAKA')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Data Konsumen + Kendaraan untuk Autocomplete
    |--------------------------------------------------------------------------
    */
    $dataKonsumen = $konsumens->map(function ($konsumen) {
        return [
            'id' => $konsumen->id,
            'nama_lengkap' => $konsumen->nama_lengkap,
            'no_hp' => $konsumen->no_hp,

            'kendaraans' => $konsumen->kendaraans->map(function ($kendaraan) {
                return [
                    'id' => $kendaraan->id,
                    'plat_nomor' => $kendaraan->plat_nomor,
                    'merk' => $kendaraan->merk,
                    'tipe_model' => $kendaraan->tipe_model,
                    'tahun' => $kendaraan->tahun,
                ];
            })->values(),
        ];
    })->values();
@endphp


<div class="max-w-2xl">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="mb-6">

        <h2 class="text-xl font-extrabold tracking-tight text-white">
            Input Servis Baru
        </h2>

        <p class="mt-1 text-xs leading-5 text-neutral-500">
            Pilih konsumen terlebih dahulu, kemudian pilih kendaraan
            berdasarkan nomor plat. Keluhan akan dicocokkan otomatis
            dengan Master Diagnosa.
        </p>

    </div>


    {{-- =========================================================
        VALIDATION ERROR
    ========================================================== --}}
    @if ($errors->any())

        <div class="mb-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs text-rose-400">

            <div class="flex items-start gap-3">

                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

                <div>

                    <p class="font-bold">
                        Data belum dapat disimpan.
                    </p>

                    <p class="mt-1">
                        {{ $errors->first() }}
                    </p>

                </div>

            </div>

        </div>

    @endif



    {{-- =========================================================
        FORM
    ========================================================== --}}
    <div class="rounded-2xl border border-neutral-800 bg-ink-900 p-5 shadow-sm sm:p-6">

        <form
            id="formServis"
            action="{{ route('admin.rekam-servis.store') }}"
            method="POST"
            class="space-y-5 text-xs"
        >

            @csrf


            {{-- =====================================================
                1. PILIH KONSUMEN
            ====================================================== --}}
            <div>

                <label
                    for="konsumenSelect"
                    class="mb-1.5 block font-bold text-neutral-300"
                >
                    Pilih Konsumen *
                </label>


                <select
                    id="konsumenSelect"
                    name="konsumen_id"
                    required
                    class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-3 font-medium text-white outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                >

                    <option value="">
                        -- Pilih Konsumen --
                    </option>

                    @foreach ($konsumens as $konsumen)

                        <option
                            value="{{ $konsumen->id }}"
                            @selected(
                                (string) old('konsumen_id')
                                === (string) $konsumen->id
                            )
                        >
                            {{ $konsumen->nama_lengkap }}
                            — {{ $konsumen->no_hp }}
                        </option>

                    @endforeach

                </select>


                <p class="mt-1.5 text-[10px] leading-4 text-neutral-500">
                    Konsumen ditampilkan berdasarkan nama dan nomor HP /
                    WhatsApp.
                </p>

            </div>



            {{-- =====================================================
                2. PILIH PLAT / KENDARAAN
            ====================================================== --}}
            <div>

                <label
                    for="platSearch"
                    class="mb-1.5 block font-bold text-neutral-300"
                >
                    Pilih Plat Nomor *
                </label>


                <div class="relative">

                    {{-- Field yang benar-benar dikirim ke server --}}
                    <input
                        type="hidden"
                        id="kendaraanId"
                        name="kendaraan_id"
                        value="{{ old('kendaraan_id') }}"
                    >


                    {{-- Search plat --}}
                    <div class="relative">

                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-500">

                            <i class="fa-solid fa-car"></i>

                        </span>


                        <input
                            type="text"
                            id="platSearch"
                            autocomplete="off"
                            disabled
                            placeholder="Pilih konsumen terlebih dahulu"
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 py-3 pl-10 pr-10 font-bold uppercase tracking-wide text-white outline-none transition placeholder:normal-case placeholder:font-normal placeholder:tracking-normal placeholder:text-neutral-600 disabled:cursor-not-allowed disabled:opacity-50 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        >


                        <button
                            type="button"
                            id="clearPlatButton"
                            class="absolute inset-y-0 right-0 hidden items-center px-3.5 text-neutral-500 transition hover:text-white"
                            aria-label="Hapus pilihan kendaraan"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </div>



                    {{-- =================================================
                        DROPDOWN REKOMENDASI PLAT
                    ================================================== --}}
                    <div
                        id="platSuggestions"
                        class="absolute left-0 right-0 top-full z-40 mt-2 hidden max-h-72 overflow-y-auto rounded-xl border border-neutral-700 bg-ink-900 shadow-2xl"
                    >
                    </div>

                </div>


                {{-- Informasi / error --}}
                <p
                    id="platHint"
                    class="mt-1.5 text-[10px] leading-4 text-neutral-500"
                >
                    Pilih konsumen terlebih dahulu.
                </p>


                {{-- Kendaraan terpilih --}}
                <div
                    id="selectedVehicle"
                    class="mt-3 hidden rounded-xl border border-brand-500/20 bg-brand-500/5 p-3"
                >

                    <div class="flex items-start gap-3">

                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-400">

                            <i class="fa-solid fa-car-side"></i>

                        </div>


                        <div>

                            <p
                                id="selectedPlate"
                                class="font-extrabold uppercase tracking-wide text-brand-400"
                            >
                            </p>

                            <p
                                id="selectedVehicleName"
                                class="mt-0.5 text-[11px] text-neutral-300"
                            >
                            </p>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =====================================================
                3. KILOMETER
            ====================================================== --}}
            <div>

                <label
                    for="kmAkhir"
                    class="mb-1.5 block font-bold text-neutral-300"
                >
                    Kilometer Terkini (KM) *
                </label>


                <div class="relative">

                    <input
                        type="number"
                        id="kmAkhir"
                        name="km_akhir"
                        value="{{ old('km_akhir') }}"
                        min="0"
                        required
                        placeholder="Contoh: 45000"
                        class="w-full rounded-xl border border-neutral-700 bg-neutral-800/60 p-3 pr-14 text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                    >

                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-[10px] font-bold text-neutral-500">
                        KM
                    </span>

                </div>

            </div>



            {{-- =====================================================
                4. KELUHAN
            ====================================================== --}}
            <div>

                <label
                    for="keluhanAwal"
                    class="mb-1.5 block font-bold text-neutral-300"
                >
                    Keluhan Konsumen *
                </label>


                <textarea
                    id="keluhanAwal"
                    name="keluhan_awal"
                    rows="4"
                    required
                    class="w-full resize-y rounded-xl border border-neutral-700 bg-neutral-800/60 p-3 leading-6 text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                    placeholder="Contoh: rem bunyi saat diinjak, tarikan agak berat"
                >{{ old('keluhan_awal') }}</textarea>


                <div class="mt-1.5 flex items-start gap-1.5 text-[10px] leading-4 text-neutral-500">

                    <i class="fa-solid fa-wand-magic-sparkles mt-0.5 text-brand-500"></i>

                    <span>
                        Kata kunci keluhan akan dicocokkan otomatis
                        ke Master Diagnosa.
                    </span>

                </div>

            </div>



            {{-- =====================================================
                ACTION
            ====================================================== --}}
            <div class="flex flex-col-reverse gap-2 border-t border-neutral-800 pt-5 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('admin.rekam-servis.index') }}"
                    class="rounded-xl bg-neutral-800 px-4 py-2.5 text-center font-bold text-neutral-200 transition hover:bg-neutral-700"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Simpan Servis

                </button>

            </div>

        </form>

    </div>

</div>

@endsection



@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */
        const konsumenData = @json($dataKonsumen);

        const oldKonsumenId =
            @json((string) old('konsumen_id', ''));

        const oldKendaraanId =
            @json((string) old('kendaraan_id', ''));


        /*
        |--------------------------------------------------------------------------
        | ELEMENT
        |--------------------------------------------------------------------------
        */
        const konsumenSelect =
            document.getElementById('konsumenSelect');

        const platSearch =
            document.getElementById('platSearch');

        const kendaraanId =
            document.getElementById('kendaraanId');

        const suggestions =
            document.getElementById('platSuggestions');

        const platHint =
            document.getElementById('platHint');

        const selectedVehicle =
            document.getElementById('selectedVehicle');

        const selectedPlate =
            document.getElementById('selectedPlate');

        const selectedVehicleName =
            document.getElementById('selectedVehicleName');

        const clearPlatButton =
            document.getElementById('clearPlatButton');

        const form =
            document.getElementById('formServis');


        let kendaraanAktif = [];


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI PLAT
        |--------------------------------------------------------------------------
        |
        | BM 1234 ABC
        | BM1234ABC
        |
        | dianggap sama saat pencarian.
        |
        */
        function normalisasiPlat(value) {

            return String(value || '')
                .toUpperCase()
                .replace(/\s+/g, '');

        }


        /*
        |--------------------------------------------------------------------------
        | MENCARI DATA KONSUMEN
        |--------------------------------------------------------------------------
        */
        function getKonsumen(id) {

            return konsumenData.find(
                item => String(item.id) === String(id)
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RESET PILIHAN KENDARAAN
        |--------------------------------------------------------------------------
        */
        function resetKendaraan() {

            kendaraanId.value = '';

            platSearch.value = '';

            selectedVehicle.classList.add('hidden');

            clearPlatButton.classList.add('hidden');
            clearPlatButton.classList.remove('flex');

            suggestions.classList.add('hidden');

        }


        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN KENDARAAN TERPILIH
        |--------------------------------------------------------------------------
        */
        function pilihKendaraan(kendaraan) {

            kendaraanId.value = kendaraan.id;

            platSearch.value = kendaraan.plat_nomor;


            selectedPlate.textContent =
                kendaraan.plat_nomor;


            let detail =
                kendaraan.merk + ' ' + kendaraan.tipe_model;


            if (kendaraan.tahun) {
                detail += ' • ' + kendaraan.tahun;
            }


            selectedVehicleName.textContent =
                detail;


            selectedVehicle.classList.remove('hidden');

            clearPlatButton.classList.remove('hidden');
            clearPlatButton.classList.add('flex');

            suggestions.classList.add('hidden');


            platHint.textContent =
                'Kendaraan telah dipilih.';

            platHint.className =
                'mt-1.5 text-[10px] leading-4 text-emerald-400';

        }


        /*
        |--------------------------------------------------------------------------
        | RENDER REKOMENDASI
        |--------------------------------------------------------------------------
        */
        function renderSuggestions(query = '') {

            suggestions.innerHTML = '';


            if (!kendaraanAktif.length) {

                suggestions.classList.add('hidden');

                return;
            }


            const cari =
                normalisasiPlat(query);


            const hasil =
                kendaraanAktif.filter(function (kendaraan) {

                    /*
                     * Kalau field kosong:
                     * tampilkan semua kendaraan milik konsumen.
                     */
                    if (!cari) {
                        return true;
                    }


                    return normalisasiPlat(
                        kendaraan.plat_nomor
                    ).includes(cari);

                });


            if (!hasil.length) {

                const empty =
                    document.createElement('div');

                empty.className =
                    'p-4 text-center text-[11px] text-neutral-500';

                empty.innerHTML = `
                    <i class="fa-solid fa-magnifying-glass mr-1"></i>
                    Tidak ada plat yang cocok.
                `;

                suggestions.appendChild(empty);

                suggestions.classList.remove('hidden');

                return;
            }


            hasil.forEach(function (kendaraan) {

                const button =
                    document.createElement('button');

                button.type = 'button';

                button.className =
                    'flex w-full items-center justify-between gap-4 border-b border-neutral-800 px-4 py-3 text-left transition last:border-b-0 hover:bg-neutral-800';


                let detail =
                    kendaraan.merk + ' ' + kendaraan.tipe_model;


                if (kendaraan.tahun) {
                    detail += ' • ' + kendaraan.tahun;
                }


                button.innerHTML = `

                    <div class="min-w-0">

                        <div class="font-extrabold uppercase tracking-wide text-brand-400">
                            ${kendaraan.plat_nomor}
                        </div>

                        <div class="mt-0.5 truncate text-[10px] text-neutral-500">
                            ${detail}
                        </div>

                    </div>

                    <i class="fa-solid fa-chevron-right shrink-0 text-[10px] text-neutral-600"></i>

                `;


                button.addEventListener(
                    'click',
                    function () {

                        pilihKendaraan(
                            kendaraan
                        );

                    }
                );


                suggestions.appendChild(
                    button
                );

            });


            suggestions.classList.remove('hidden');

        }


        /*
        |--------------------------------------------------------------------------
        | SAAT KONSUMEN DIPILIH
        |--------------------------------------------------------------------------
        */
        function aktifkanKonsumen() {

            const konsumen =
                getKonsumen(
                    konsumenSelect.value
                );


            resetKendaraan();


            if (!konsumen) {

                kendaraanAktif = [];

                platSearch.disabled = true;

                platSearch.placeholder =
                    'Pilih konsumen terlebih dahulu';


                platHint.textContent =
                    'Pilih konsumen terlebih dahulu.';

                platHint.className =
                    'mt-1.5 text-[10px] leading-4 text-neutral-500';

                return;
            }


            kendaraanAktif =
                konsumen.kendaraans || [];


            if (!kendaraanAktif.length) {

                platSearch.disabled = true;

                platSearch.placeholder =
                    'Konsumen belum memiliki kendaraan';


                platHint.innerHTML = `
                    Konsumen ini belum memiliki kendaraan.
                    Tambahkan kendaraan melalui menu
                    <a
                        href="{{ route('admin.konsumen.index') }}"
                        class="font-bold text-brand-400 hover:underline"
                    >
                        Data Konsumen
                    </a>.
                `;

                platHint.className =
                    'mt-1.5 text-[10px] leading-4 text-amber-400';

                return;
            }


            platSearch.disabled = false;

            platSearch.placeholder =
                'Ketik nomor plat, contoh: BM 1234 ABC';


            platHint.textContent =
                kendaraanAktif.length
                + ' kendaraan tersedia untuk '
                + konsumen.nama_lengkap
                + '. Ketik plat atau klik field untuk melihat pilihan.';


            platHint.className =
                'mt-1.5 text-[10px] leading-4 text-neutral-500';


            /*
             * Restore setelah gagal validasi.
             */
            if (oldKendaraanId) {

                const oldVehicle =
                    kendaraanAktif.find(
                        kendaraan =>
                            String(kendaraan.id)
                            === String(oldKendaraanId)
                    );


                if (oldVehicle) {
                    pilihKendaraan(oldVehicle);
                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | EVENT
        |--------------------------------------------------------------------------
        */
        konsumenSelect.addEventListener(
            'change',
            aktifkanKonsumen
        );


        platSearch.addEventListener(
            'focus',
            function () {

                if (!platSearch.disabled) {
                    renderSuggestions(
                        platSearch.value
                    );
                }

            }
        );


        platSearch.addEventListener(
            'input',
            function () {

                /*
                 * Kalau mekanik mengetik ulang plat,
                 * pilihan kendaraan sebelumnya dibatalkan.
                 */
                kendaraanId.value = '';

                selectedVehicle.classList.add('hidden');

                clearPlatButton.classList.remove('hidden');
                clearPlatButton.classList.add('flex');


                platHint.textContent =
                    'Pilih salah satu kendaraan dari rekomendasi.';

                platHint.className =
                    'mt-1.5 text-[10px] leading-4 text-amber-400';


                renderSuggestions(
                    platSearch.value
                );

            }
        );


        clearPlatButton.addEventListener(
            'click',
            function () {

                resetKendaraan();

                if (!platSearch.disabled) {

                    platSearch.focus();

                    renderSuggestions('');

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | TUTUP AUTOCOMPLETE JIKA KLIK DI LUAR
        |--------------------------------------------------------------------------
        */
        document.addEventListener(
            'click',
            function (event) {

                if (
                    !platSearch.contains(event.target)
                    &&
                    !suggestions.contains(event.target)
                ) {
                    suggestions.classList.add(
                        'hidden'
                    );
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDASI SEBELUM SUBMIT
        |--------------------------------------------------------------------------
        */
        form.addEventListener(
            'submit',
            function (event) {

                if (!konsumenSelect.value) {

                    event.preventDefault();

                    alert(
                        'Silakan pilih konsumen terlebih dahulu.'
                    );

                    konsumenSelect.focus();

                    return;
                }


                if (!kendaraanId.value) {

                    event.preventDefault();

                    alert(
                        'Silakan pilih plat kendaraan dari rekomendasi yang tersedia.'
                    );

                    platSearch.focus();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL STATE
        |--------------------------------------------------------------------------
        */
        if (
            oldKonsumenId
            &&
            !konsumenSelect.value
        ) {
            konsumenSelect.value =
                oldKonsumenId;
        }


        aktifkanKonsumen();

    });

</script>

@endpush