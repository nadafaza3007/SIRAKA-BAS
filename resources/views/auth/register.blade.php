<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registrasi - SIRAKA</title>


    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>


    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        jakarta: [
                            'Plus Jakarta Sans',
                            'sans-serif'
                        ],
                    },

                    colors: {
                        brand: {
                            500: '#f97316',
                            600: '#ea580c',
                        },

                        ink: {
                            950: '#080b10',
                            900: '#11151d',
                            800: '#171c27',
                        }
                    }
                }
            }
        }
    </script>


    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >


    {{-- Google Font --}}
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

</head>


<body class="min-h-screen bg-ink-950 text-slate-200">


<main class="flex min-h-screen items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-6xl overflow-hidden rounded-3xl border border-slate-800 bg-ink-900 shadow-2xl">

        <div class="grid grid-cols-1 lg:grid-cols-2">


            {{-- =================================================
                FORM REGISTRASI
            ================================================== --}}
            <section class="flex items-center p-6 sm:p-10 lg:p-12">

                <div class="mx-auto w-full max-w-md">


                    {{-- Logo mobile --}}
                    <div class="mb-8 flex items-center gap-3 lg:hidden">

                        <div class="h-12 w-12 overflow-hidden rounded-xl border border-slate-700 bg-black">

                            <img
                                src="{{ asset('images/logo-siraka.png') }}"
                                alt="Logo SIRAKA"
                                class="h-full w-full object-cover"
                            >

                        </div>


                        <div>

                            <p class="font-extrabold tracking-wide text-white">
                                SIRAKA
                            </p>

                            <p class="text-xs text-slate-500">
                                Baba Auto Service
                            </p>

                        </div>

                    </div>



                    {{-- =================================================
                        HEADER
                    ================================================== --}}
                    <div>

                        <p class="text-sm font-semibold text-orange-500">
                            Buat akun pelanggan
                        </p>

                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">
                            Registrasi SIRAKA
                        </h1>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Daftarkan akun untuk mengakses kendaraan
                            dan riwayat servis yang terhubung dengan
                            data pelanggan di bengkel.
                        </p>

                    </div>



                    {{-- =================================================
                        ERROR UMUM
                    ================================================== --}}
                    @if ($errors->any())

                        <div class="mt-6 flex items-start gap-3 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4">

                            <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-400"></i>


                            <div>

                                <p class="text-sm font-semibold text-rose-300">
                                    Registrasi gagal
                                </p>

                                <p class="mt-1 text-xs leading-5 text-rose-400">
                                    {{ $errors->first() }}
                                </p>

                            </div>

                        </div>

                    @endif



                    {{-- =================================================
                        FORM
                    ================================================== --}}
                    <form
                        id="registerForm"
                        action="{{ route('register.post') }}"
                        method="POST"
                        class="mt-8 space-y-5"
                    >

                        @csrf



                        {{-- =================================================
                            NAMA LENGKAP
                        ================================================== --}}
                        <div>

                            <label
                                for="nama_lengkap"
                                class="mb-2 block text-sm font-semibold text-slate-300"
                            >
                                Nama Lengkap
                            </label>


                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">

                                    <i class="fa-solid fa-id-card text-sm"></i>

                                </span>


                                <input
                                    id="nama_lengkap"
                                    type="text"
                                    name="nama_lengkap"
                                    value="{{ old('nama_lengkap') }}"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Masukkan nama lengkap"
                                    @class([
                                        'w-full rounded-xl border bg-ink-800 py-3.5 pl-11 pr-4 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2',

                                        'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20'
                                            => $errors->has('nama_lengkap'),

                                        'border-slate-700 focus:border-orange-500 focus:ring-orange-500/20'
                                            => ! $errors->has('nama_lengkap'),
                                    ])
                                >

                            </div>


                            @error('nama_lengkap')

                                <p class="mt-1.5 text-xs text-rose-400">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>



                        {{-- =================================================
                            USERNAME + REALTIME CHECK
                        ================================================== --}}
                        <div>

                            <label
                                for="username"
                                class="mb-2 block text-sm font-semibold text-slate-300"
                            >
                                Username
                            </label>


                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">

                                    <i class="fa-solid fa-user text-sm"></i>

                                </span>


                                <input
                                    id="username"
                                    type="text"
                                    name="username"
                                    value="{{ old('username') }}"
                                    required
                                    autocomplete="username"
                                    spellcheck="false"
                                    autocapitalize="none"
                                    placeholder="Contoh: budi123"
                                    @class([
                                        'w-full rounded-xl border bg-ink-800 py-3.5 pl-11 pr-12 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2',

                                        'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20'
                                            => $errors->has('username'),

                                        'border-slate-700 focus:border-orange-500 focus:ring-orange-500/20'
                                            => ! $errors->has('username'),
                                    ])
                                >


                                {{-- Icon status --}}
                                <span
                                    id="usernameStatusIcon"
                                    class="pointer-events-none absolute inset-y-0 right-0 hidden items-center pr-4"
                                >
                                </span>

                            </div>



                            {{-- Status username realtime --}}
                            <div
                                id="usernameStatus"
                                class="mt-1.5 min-h-[20px] text-xs leading-5"
                            >

                                @error('username')

                                    <span class="inline-flex items-center gap-1.5 text-rose-400">

                                        <i class="fa-solid fa-circle-xmark"></i>

                                        {{ $message }}

                                    </span>

                                @else

                                    <span class="text-slate-500">

                                        Username digunakan sebagai alternatif
                                        selain nomor HP saat masuk ke sistem.

                                    </span>

                                @enderror

                            </div>

                        </div>



                        {{-- =================================================
                            NOMOR HP
                        ================================================== --}}
                        <div>

                            <label
                                for="no_hp"
                                class="mb-2 block text-sm font-semibold text-slate-300"
                            >
                                Nomor HP / WhatsApp
                            </label>


                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">

                                    <i class="fa-solid fa-phone text-sm"></i>

                                </span>


                                <input
                                    id="no_hp"
                                    type="text"
                                    name="no_hp"
                                    value="{{ old('no_hp') }}"
                                    required
                                    inputmode="tel"
                                    autocomplete="tel"
                                    placeholder="Contoh: 081234567890"
                                    @class([
                                        'w-full rounded-xl border bg-ink-800 py-3.5 pl-11 pr-4 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2',

                                        'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20'
                                            => $errors->has('no_hp'),

                                        'border-slate-700 focus:border-orange-500 focus:ring-orange-500/20'
                                            => ! $errors->has('no_hp'),
                                    ])
                                >

                            </div>


                            <p class="mt-1.5 text-xs leading-5 text-slate-500">

                                Gunakan nomor HP yang sama dengan data
                                pelanggan di bengkel agar kendaraan dapat
                                terhubung otomatis.

                            </p>


                            @error('no_hp')

                                <p class="mt-1.5 text-xs text-rose-400">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>



                        {{-- =================================================
                            PASSWORD
                        ================================================== --}}
                        <div>

                            <label
                                for="passwordInput"
                                class="mb-2 block text-sm font-semibold text-slate-300"
                            >
                                Kata Sandi
                            </label>


                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">

                                    <i class="fa-solid fa-lock text-sm"></i>

                                </span>


                                <input
                                    id="passwordInput"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimal 6 karakter"
                                    @class([
                                        'w-full rounded-xl border bg-ink-800 py-3.5 pl-11 pr-12 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2',

                                        'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20'
                                            => $errors->has('password'),

                                        'border-slate-700 focus:border-orange-500 focus:ring-orange-500/20'
                                            => ! $errors->has('password'),
                                    ])
                                >


                                <button
                                    type="button"
                                    onclick="togglePassword(
                                        'passwordInput',
                                        'toggleIcon1'
                                    )"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 transition hover:text-white"
                                >

                                    <i
                                        id="toggleIcon1"
                                        class="fa-solid fa-eye text-sm"
                                    ></i>

                                </button>

                            </div>


                            @error('password')

                                <p class="mt-1.5 text-xs text-rose-400">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>



                        {{-- =================================================
                            KONFIRMASI PASSWORD
                        ================================================== --}}
                        <div>

                            <label
                                for="passwordConfirmationInput"
                                class="mb-2 block text-sm font-semibold text-slate-300"
                            >
                                Konfirmasi Kata Sandi
                            </label>


                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">

                                    <i class="fa-solid fa-shield-halved text-sm"></i>

                                </span>


                                <input
                                    id="passwordConfirmationInput"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi kata sandi"
                                    class="w-full rounded-xl border border-slate-700 bg-ink-800 py-3.5 pl-11 pr-12 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                                >


                                <button
                                    type="button"
                                    onclick="togglePassword(
                                        'passwordConfirmationInput',
                                        'toggleIcon2'
                                    )"
                                    aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 transition hover:text-white"
                                >

                                    <i
                                        id="toggleIcon2"
                                        class="fa-solid fa-eye text-sm"
                                    ></i>

                                </button>

                            </div>

                        </div>



                        {{-- =================================================
                            SUBMIT
                        ================================================== --}}
                        <button
                            type="submit"
                            id="registerSubmit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-ink-900 disabled:cursor-not-allowed disabled:opacity-50"
                        >

                            <i class="fa-solid fa-user-plus"></i>

                            <span id="registerSubmitText">
                                Daftarkan Akun & Sinkronkan
                            </span>

                        </button>

                    </form>



                    {{-- =================================================
                        LOGIN
                    ================================================== --}}
                    <div class="mt-7 border-t border-slate-800 pt-6 text-center">

                        <p class="text-sm text-slate-400">

                            Sudah punya akun?

                            <a
                                href="{{ route('login') }}"
                                class="ml-1 font-semibold text-orange-500 transition hover:text-orange-400"
                            >
                                Masuk di sini
                            </a>

                        </p>

                    </div>


                    <p class="mt-8 text-center text-xs text-slate-600 lg:hidden">
                        © {{ date('Y') }} SIRAKA • Baba Auto Service
                    </p>

                </div>

            </section>



            {{-- =================================================
                INFORMASI SIRAKA
            ================================================== --}}
            <section class="relative hidden overflow-hidden bg-ink-800 p-10 lg:flex lg:flex-col lg:justify-between xl:p-12">


                {{-- Background dekorasi --}}
                <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-orange-500/10 blur-3xl"></div>

                <div class="pointer-events-none absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-orange-600/5 blur-3xl"></div>



                {{-- Logo --}}
                <div class="relative z-10">

                    <div class="flex items-center gap-4">

                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-slate-700 bg-black shadow-lg">

                            <img
                                src="{{ asset('images/logo-siraka.png') }}"
                                alt="Logo SIRAKA"
                                class="h-full w-full object-cover"
                            >

                        </div>


                        <div>

                            <h2 class="text-2xl font-extrabold tracking-wide text-white">
                                SIRAKA
                            </h2>

                            <p class="mt-0.5 text-xs font-medium uppercase tracking-[0.16em] text-slate-400">
                                Baba Auto Service
                            </p>

                        </div>

                    </div>

                </div>



                {{-- Informasi sinkronisasi --}}
                <div class="relative z-10">

                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400">

                        <i class="fa-solid fa-link"></i>

                        Sinkronisasi Otomatis

                    </div>


                    <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-white">

                        Semua Riwayat Kendaraan
                        <br>

                        <span class="text-orange-500">
                            dalam Satu Akun
                        </span>

                    </h1>


                    <p class="mt-5 max-w-md text-sm leading-7 text-slate-400">

                        Gunakan nomor HP yang sama dengan data pelanggan di Baba 
                        Auto Service agar kendaraan dan riwayat servis Anda dapat 
                        terhubung otomatis.

                    </p>



                    <div class="mt-8 space-y-4">


                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-500/10 text-orange-500">

                                <i class="fa-solid fa-user-plus text-xs"></i>

                            </div>


                            <div>

                                <p class="text-sm font-semibold text-white">
                                    Buat akun
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Daftarkan identitas dan akun Anda.
                                </p>

                            </div>

                        </div>



                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-500/10 text-orange-500">

                                <i class="fa-solid fa-database text-xs"></i>

                            </div>


                            <div>

                                <p class="text-sm font-semibold text-white">
                                    Data Dicocokkan
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Nomor HP akan dicocokkan dengan data pelanggan bengkel.
                                </p>

                            </div>

                        </div>



                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">

                                <i class="fa-solid fa-circle-check text-xs"></i>

                            </div>


                            <div>

                                <p class="text-sm font-semibold text-white">
                                    Riwayat Terhubung
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Kendaraan yang sesuai akan tersedia di akun Anda.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                <p class="relative z-10 text-xs text-slate-500">
                    © {{ date('Y') }} SIRAKA • Baba Auto Service
                </p>

            </section>

        </div>

    </div>

</main>



{{-- =========================================================
    JAVASCRIPT
========================================================== --}}
<script>

    /*
    |--------------------------------------------------------------------------
    | SHOW / HIDE PASSWORD
    |--------------------------------------------------------------------------
    */

    function togglePassword(
        fieldId,
        iconId
    ) {

        const input =
            document.getElementById(
                fieldId
            );

        const icon =
            document.getElementById(
                iconId
            );


        if (!input || !icon) {
            return;
        }


        if (input.type === 'password') {

            input.type =
                'text';

            icon.classList.remove(
                'fa-eye'
            );

            icon.classList.add(
                'fa-eye-slash'
            );

        } else {

            input.type =
                'password';

            icon.classList.remove(
                'fa-eye-slash'
            );

            icon.classList.add(
                'fa-eye'
            );
        }
    }



    /*
    |--------------------------------------------------------------------------
    | REALTIME USERNAME CHECK
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const form =
                document.getElementById(
                    'registerForm'
                );

            const usernameInput =
                document.getElementById(
                    'username'
                );

            const usernameStatus =
                document.getElementById(
                    'usernameStatus'
                );

            const usernameStatusIcon =
                document.getElementById(
                    'usernameStatusIcon'
                );

            const registerSubmit =
                document.getElementById(
                    'registerSubmit'
                );

            const registerSubmitText =
                document.getElementById(
                    'registerSubmitText'
                );


            if (
                !form
                ||
                !usernameInput
                ||
                !usernameStatus
                ||
                !registerSubmit
            ) {
                return;
            }


            let debounceTimer =
                null;

            let requestController =
                null;

            let usernameState =
                'unknown';

            let checkedUsername =
                '';



            /*
            |--------------------------------------------------------------------------
            | NORMALISASI
            |--------------------------------------------------------------------------
            */

            function normalizeUsername(
                value
            ) {

                return String(
                    value || ''
                )
                    .trim()
                    .toLowerCase();
            }



            /*
            |--------------------------------------------------------------------------
            | STYLE INPUT
            |--------------------------------------------------------------------------
            */

            function resetInputStyle() {

                usernameInput.classList.remove(
                    'border-rose-500',
                    'border-emerald-500',
                    'border-amber-500',

                    'focus:border-rose-500',
                    'focus:border-emerald-500',
                    'focus:border-amber-500',

                    'focus:ring-rose-500/20',
                    'focus:ring-emerald-500/20',
                    'focus:ring-amber-500/20'
                );


                usernameInput.classList.add(
                    'border-slate-700',
                    'focus:border-orange-500',
                    'focus:ring-orange-500/20'
                );
            }


            function setInputStyle(
                type
            ) {

                resetInputStyle();


                usernameInput.classList.remove(
                    'border-slate-700',
                    'focus:border-orange-500',
                    'focus:ring-orange-500/20'
                );


                if (type === 'success') {

                    usernameInput.classList.add(
                        'border-emerald-500',
                        'focus:border-emerald-500',
                        'focus:ring-emerald-500/20'
                    );

                } else if (
                    type === 'error'
                ) {

                    usernameInput.classList.add(
                        'border-rose-500',
                        'focus:border-rose-500',
                        'focus:ring-rose-500/20'
                    );

                } else if (
                    type === 'checking'
                ) {

                    usernameInput.classList.add(
                        'border-amber-500',
                        'focus:border-amber-500',
                        'focus:ring-amber-500/20'
                    );
                }
            }



            /*
            |--------------------------------------------------------------------------
            | ICON
            |--------------------------------------------------------------------------
            */

            function setIcon(
                html
            ) {

                if (!usernameStatusIcon) {
                    return;
                }


                if (!html) {

                    usernameStatusIcon
                        .classList
                        .add('hidden');

                    usernameStatusIcon
                        .classList
                        .remove('flex');

                    usernameStatusIcon
                        .innerHTML = '';

                    return;
                }


                usernameStatusIcon
                    .classList
                    .remove('hidden');

                usernameStatusIcon
                    .classList
                    .add('flex');

                usernameStatusIcon
                    .innerHTML =
                        html;
            }



            /*
            |--------------------------------------------------------------------------
            | DEFAULT STATUS
            |--------------------------------------------------------------------------
            */

            function setDefault() {

                usernameState =
                    'unknown';

                checkedUsername =
                    '';

                resetInputStyle();

                setIcon('');


                usernameStatus.innerHTML = `
                    <span class="text-slate-500">
                        Username digunakan sebagai alternatif
                        selain nomor HP saat masuk ke sistem.
                    </span>
                `;


                registerSubmit.disabled =
                    false;
            }



            /*
            |--------------------------------------------------------------------------
            | CHECKING
            |--------------------------------------------------------------------------
            */

            function setChecking() {

                usernameState =
                    'checking';


                setInputStyle(
                    'checking'
                );


                usernameStatus.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 font-medium text-amber-400">

                        <i class="fa-solid fa-spinner fa-spin"></i>

                        Memeriksa ketersediaan username...

                    </span>
                `;


                setIcon(`
                    <i class="fa-solid fa-spinner fa-spin text-amber-400"></i>
                `);


                registerSubmit.disabled =
                    true;
            }



            /*
            |--------------------------------------------------------------------------
            | USERNAME TERSEDIA
            |--------------------------------------------------------------------------
            */

            function setAvailable(
                username,
                message
            ) {

                usernameState =
                    'available';

                checkedUsername =
                    username;


                setInputStyle(
                    'success'
                );


                usernameStatus.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-400">

                        <i class="fa-solid fa-circle-check"></i>

                        ${message || 'Username tersedia.'}

                    </span>
                `;


                setIcon(`
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                `);


                registerSubmit.disabled =
                    false;
            }



            /*
            |--------------------------------------------------------------------------
            | USERNAME TIDAK TERSEDIA / INVALID
            |--------------------------------------------------------------------------
            */

            function setUnavailable(
                message
            ) {

                usernameState =
                    'unavailable';

                checkedUsername =
                    '';


                setInputStyle(
                    'error'
                );


                usernameStatus.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 font-semibold text-rose-400">

                        <i class="fa-solid fa-circle-xmark"></i>

                        ${message}

                    </span>
                `;


                setIcon(`
                    <i class="fa-solid fa-circle-xmark text-rose-400"></i>
                `);


                registerSubmit.disabled =
                    true;
            }



            /*
            |--------------------------------------------------------------------------
            | FALLBACK JIKA AJAX GAGAL
            |--------------------------------------------------------------------------
            */

            function setConnectionWarning() {

                usernameState =
                    'fallback';

                checkedUsername =
                    '';


                resetInputStyle();


                usernameStatus.innerHTML = `
                    <span class="inline-flex items-start gap-1.5 text-amber-400">

                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

                        <span>
                            Pemeriksaan realtime tidak tersedia.
                            Username tetap akan diperiksa saat pendaftaran.
                        </span>

                    </span>
                `;


                setIcon('');


                /*
                 * Tetap izinkan daftar.
                 *
                 * POST /register masih memiliki
                 * unique:users,username sebagai pengaman.
                 */
                registerSubmit.disabled =
                    false;
            }



            /*
            |--------------------------------------------------------------------------
            | VALIDASI FORMAT LOKAL
            |--------------------------------------------------------------------------
            */

            function validateLocal(
                username
            ) {

                if (!username) {

                    return {
                        valid: false,
                        empty: true,
                    };
                }


                if (
                    username.length < 3
                ) {

                    return {
                        valid: false,

                        message:
                            'Username minimal 3 karakter.',
                    };
                }


                if (
                    username.length > 50
                ) {

                    return {
                        valid: false,

                        message:
                            'Username maksimal 50 karakter.',
                    };
                }


                if (
                    !/^[A-Za-z0-9_-]+$/.test(
                        username
                    )
                ) {

                    return {
                        valid: false,

                        message:
                            'Username hanya boleh berisi huruf, angka, underscore, atau tanda hubung.',
                    };
                }


                return {
                    valid: true,
                };
            }



            /*
            |--------------------------------------------------------------------------
            | CHECK KE SERVER
            |--------------------------------------------------------------------------
            */

            async function checkUsername() {

                const username =
                    normalizeUsername(
                        usernameInput.value
                    );


                usernameInput.value =
                    username;


                const validation =
                    validateLocal(
                        username
                    );


                if (
                    validation.empty
                ) {

                    setDefault();

                    return;
                }


                if (
                    !validation.valid
                ) {

                    setUnavailable(
                        validation.message
                    );

                    return;
                }


                /*
                 * Tidak perlu request ulang kalau
                 * username ini sudah dinyatakan tersedia.
                 */
                if (
                    usernameState === 'available'
                    &&
                    checkedUsername === username
                ) {
                    return;
                }


                /*
                 * Batalkan request sebelumnya.
                 */
                if (requestController) {

                    requestController.abort();
                }


                requestController =
                    new AbortController();


                setChecking();


                try {

                    const url =
                        @json(route('username.check'))
                        +
                        '?username='
                        +
                        encodeURIComponent(
                            username
                        );


                    const response =
                        await fetch(
                            url,
                            {
                                method:
                                    'GET',

                                headers: {
                                    'Accept':
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest',
                                },

                                signal:
                                    requestController.signal,
                            }
                        );


                    /*
                     * Kalau rate limit / server error,
                     * pakai validasi POST sebagai fallback.
                     */
                    if (!response.ok) {

                        throw new Error(
                            'Username check gagal.'
                        );
                    }


                    const data =
                        await response.json();


                    /*
                     * User mungkin sudah mengetik lagi
                     * sebelum request selesai.
                     */
                    const currentUsername =
                        normalizeUsername(
                            usernameInput.value
                        );


                    if (
                        currentUsername
                        !==
                        username
                    ) {
                        return;
                    }


                    if (
                        data.available
                    ) {

                        setAvailable(
                            username,
                            data.message
                            ||
                            'Username tersedia.'
                        );

                    } else {

                        setUnavailable(
                            data.message
                            ||
                            'Username sudah digunakan.'
                        );
                    }

                } catch (error) {

                    if (
                        error.name
                        ===
                        'AbortError'
                    ) {
                        return;
                    }


                    setConnectionWarning();
                }
            }



            /*
            |--------------------------------------------------------------------------
            | SAAT USER MENGETIK
            |--------------------------------------------------------------------------
            */

            usernameInput.addEventListener(
                'input',
                function () {

                    clearTimeout(
                        debounceTimer
                    );


                    /*
                     * Request sebelumnya tidak lagi relevan.
                     */
                    if (requestController) {

                        requestController.abort();
                    }


                    usernameState =
                        'unknown';

                    checkedUsername =
                        '';


                    const username =
                        normalizeUsername(
                            this.value
                        );


                    if (!username) {

                        setDefault();

                        return;
                    }


                    const local =
                        validateLocal(
                            username
                        );


                    /*
                     * Berikan error lokal langsung tanpa
                     * request database.
                     */
                    if (
                        !local.valid
                        &&
                        !local.empty
                    ) {

                        setUnavailable(
                            local.message
                        );

                        return;
                    }


                    /*
                     * Menunggu user selesai mengetik.
                     */
                    resetInputStyle();

                    setIcon('');


                    usernameStatus.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 text-slate-500">

                            <i class="fa-solid fa-keyboard"></i>

                            Memeriksa setelah Anda selesai mengetik...

                        </span>
                    `;


                    registerSubmit.disabled =
                        true;


                    /*
                     * Debounce 450 ms.
                     */
                    debounceTimer =
                        setTimeout(
                            function () {

                                checkUsername();

                            },
                            450
                        );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | NORMALISASI SAAT BLUR
            |--------------------------------------------------------------------------
            */

            usernameInput.addEventListener(
                'blur',
                function () {

                    this.value =
                        normalizeUsername(
                            this.value
                        );
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

                    const username =
                        normalizeUsername(
                            usernameInput.value
                        );


                    usernameInput.value =
                        username;


                    const validation =
                        validateLocal(
                            username
                        );


                    if (
                        !validation.valid
                    ) {

                        event.preventDefault();

                        setUnavailable(
                            validation.message
                            ||
                            'Username tidak valid.'
                        );

                        usernameInput.focus();

                        return;
                    }


                    /*
                     * Jangan submit jika username secara
                     * realtime sudah diketahui tidak tersedia.
                     */
                    if (
                        usernameState
                        ===
                        'unavailable'
                    ) {

                        event.preventDefault();

                        usernameInput.focus();

                        return;
                    }


                    /*
                     * Jika proses pengecekan masih berjalan,
                     * tunggu dulu.
                     */
                    if (
                        usernameState
                        ===
                        'checking'
                    ) {

                        event.preventDefault();

                        return;
                    }


                    /*
                     * Loading hanya untuk submit yang valid.
                     */
                    registerSubmit.disabled =
                        true;


                    registerSubmit.innerHTML = `
                        <i class="fa-solid fa-spinner fa-spin"></i>

                        <span>
                            Mendaftarkan akun...
                        </span>
                    `;
                }
            );



            /*
            |--------------------------------------------------------------------------
            | OLD VALUE SETELAH VALIDASI SERVER
            |--------------------------------------------------------------------------
            */

            if (
                usernameInput.value.trim()
                !==
                ''
            ) {

                /*
                 * Misalnya validasi no HP/password gagal,
                 * username lama tetap dicek ulang saat
                 * halaman dimuat.
                 */
                setTimeout(
                    checkUsername,
                    250
                );

            } else {

                setDefault();
            }

        }
    );

</script>


</body>

</html>