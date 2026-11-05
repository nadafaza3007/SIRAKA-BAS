<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIRAKA</title>

    {{-- Terapkan tema sebelum render agar tidak berkedip --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                var dark = saved
                    ? saved === 'dark'
                    : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        jakarta: ['Plus Jakarta Sans', 'sans-serif'],
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(.96); }
            to   { opacity: 1; transform: scale(1); }
        }

        .modal-in {
            animation: modalIn .2s ease-out;
        }

        body, main > div, input {
            transition: background-color .2s ease, border-color .2s ease, color .2s ease;
        }
    </style>
</head>


<body class="min-h-screen bg-slate-100 text-slate-700 dark:bg-ink-950 dark:text-slate-200">

    {{-- MODAL SUCCESS REGISTRASI --}}
    @if (session('success'))

        <div
            id="successModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm dark:bg-black/80"
        >

            <div class="modal-in w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-7 text-center shadow-2xl dark:border-slate-700 dark:bg-ink-900">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/10 text-2xl text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <h3 class="mt-5 text-lg font-bold text-slate-900 dark:text-white">
                    Pendaftaran Berhasil!
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                    {{ session('success') }}
                </p>

                <button
                    type="button"
                    onclick="closeModal()"
                    class="mt-6 w-full rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-600"
                >
                    Masuk Sekarang
                </button>

            </div>

        </div>

    @endif


    {{-- LOGIN WRAPPER --}}
    <main class="flex min-h-screen items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-6xl">

        {{-- BARIS ATAS: KEMBALI + TEMA (sejajar dengan kartu) --}}
        <div class="mb-4 flex items-center justify-between">

            <a
                href="{{ url('/') }}"
                class="group inline-flex items-center gap-2 rounded-lg py-1.5 text-sm font-semibold text-slate-600 transition hover:text-orange-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 dark:text-slate-400 dark:hover:text-orange-400"
            >
                <i class="fa-solid fa-arrow-left text-xs transition-transform group-hover:-translate-x-0.5"></i>
                Kembali ke Beranda
            </a>

            <button
                type="button"
                onclick="toggleTheme()"
                aria-label="Ganti mode tampilan"
                title="Ganti mode terang / gelap"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-600 transition hover:text-orange-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 dark:border-slate-700 dark:bg-ink-800 dark:text-slate-300 dark:hover:text-orange-400"
            >
                <i class="fa-solid fa-moon dark:hidden"></i>
                <i class="fa-solid fa-sun hidden dark:inline"></i>
            </button>

        </div>

<div class="w-full max-w-6xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-ink-900">

            <div class="grid min-h-[650px] grid-cols-1 lg:grid-cols-2">


                {{-- BAGIAN INFORMASI --}}
                <section class="relative hidden overflow-hidden bg-slate-50 p-10 transition-colors lg:flex lg:flex-col lg:justify-between dark:bg-ink-800 xl:p-12">

                    {{-- Dekorasi --}}
                    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-orange-500/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-orange-600/5 blur-3xl"></div>


                    {{-- Logo --}}
                    <div class="relative z-10">
                        <div class="flex items-center gap-4">

                            <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-black">
                                <img
                                    src="{{ asset('images/logo_bengkel.jpg') }}"
                                    alt="Logo SIRAKA"
                                    class="h-full w-full object-cover"
                                >
                            </div>

                            <div>
                                <h2 class="text-2xl font-extrabold tracking-wide text-slate-900 dark:text-white">
                                    SIRAKA
                                </h2>
                                <p class="mt-0.5 text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                    Baba Auto Service
                                </p>
                            </div>

                        </div>
                    </div>


                    {{-- Deskripsi --}}
                    <div class="relative z-10 max-w-lg">

                        <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-orange-500/20 bg-orange-500/10 px-3 py-1.5 text-xs font-semibold text-orange-600 dark:text-orange-400">
                            <i class="fa-solid fa-car-side"></i>
                            Portal Kendaraan
                        </div>

                        <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-slate-900 dark:text-white xl:text-5xl">
                            Pantau Histori &
                            <br>
                            <span class="text-orange-500">
                                Performa Kendaraan
                            </span>
                            Anda
                        </h1>

                        <p class="mt-6 max-w-md text-sm leading-7 text-slate-600 dark:text-slate-400">
                            Akses informasi kendaraan, riwayat servis,
                            diagnosa, tindakan mekanik, sparepart,
                            rekomendasi bengkel, hingga nota servis
                            dalam satu sistem.
                        </p>

                        {{-- Fitur ringkas --}}
                        <div class="mt-8 grid grid-cols-2 gap-3">

                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700/70 dark:bg-black/20 dark:shadow-none">
                                <i class="fa-solid fa-clock-rotate-left text-orange-500"></i>
                                <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">
                                    Riwayat Servis
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Histori perawatan kendaraan.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700/70 dark:bg-black/20 dark:shadow-none">
                                <i class="fa-solid fa-file-invoice text-orange-500"></i>
                                <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">
                                    Nota Digital
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Rincian biaya servis Anda.
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Footer kiri --}}
                    <p class="relative z-10 text-xs text-slate-500">
                        © {{ date('Y') }} SIRAKA • Baba Auto Service
                    </p>

                </section>


                {{-- FORM LOGIN --}}
                <section class="flex items-center p-6 sm:p-10 lg:p-12">

                    <div class="mx-auto w-full max-w-md">

                        {{-- Logo mobile --}}
                        <div class="mb-8 flex items-center gap-3 lg:hidden">

                            <div class="h-12 w-12 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-black">
                                <img
                                    src="{{ asset('images/logo_bengkel.jpg') }}"
                                    alt="Logo SIRAKA"
                                    class="h-full w-full object-cover"
                                >
                            </div>

                            <div>
                                <p class="font-extrabold tracking-wide text-slate-900 dark:text-white">
                                    SIRAKA
                                </p>
                                <p class="text-xs text-slate-500">
                                    Baba Auto Service
                                </p>
                            </div>

                        </div>


                        {{-- Header --}}
                        <div>
                            <p class="text-sm font-semibold text-orange-500">
                                Selamat datang
                            </p>

                            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                Masuk ke SIRAKA
                            </h1>

                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Masukkan username atau nomor HP dan kata
                                sandi untuk mengakses sistem.
                            </p>
                        </div>


                        {{-- ERROR --}}
                        @if ($errors->any())

                            <div class="mt-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-500/30 dark:bg-rose-500/10">

                                <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-500 dark:text-rose-400"></i>

                                <div>
                                    <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">
                                        Gagal masuk
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-rose-600 dark:text-rose-400">
                                        {{ $errors->first() }}
                                    </p>
                                </div>

                            </div>

                        @endif


                        {{-- FORM --}}
                        <form
                            action="{{ route('login.post') }}"
                            method="POST"
                            class="mt-8 space-y-5"
                        >

                            @csrf

                            {{-- Username / No HP --}}
                            <div>

                                <label
                                    for="no_hp"
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Username atau Nomor HP
                                </label>

                                <div class="relative">

                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-user text-sm"></i>
                                    </span>

                                    <input
                                        id="no_hp"
                                        type="text"
                                        name="no_hp"
                                        value="{{ old('no_hp') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="Masukkan Username atau Nomor HP"
                                        class="w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:border-slate-700 dark:bg-ink-800 dark:text-white dark:placeholder:text-slate-600"
                                    >

                                </div>

                            </div>


                            {{-- Password --}}
                            <div>

                                <div class="mb-2 flex items-center justify-between gap-3">

                                    <label
                                        for="passwordInput"
                                        class="block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Kata Sandi
                                    </label>

                                    @if (Route::has('password.request'))
                                        <a
                                            href="{{ route('password.request') }}"
                                            class="text-xs font-semibold text-orange-600 transition hover:text-orange-500 dark:text-orange-500 dark:hover:text-orange-400"
                                        >
                                            Lupa kata sandi?
                                        </a>
                                    @endif

                                </div>

                                <div class="relative">

                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-lock text-sm"></i>
                                    </span>

                                    <input
                                        id="passwordInput"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Masukkan kata sandi"
                                        class="w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-11 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 dark:border-slate-700 dark:bg-ink-800 dark:text-white dark:placeholder:text-slate-600"
                                    >

                                    <button
                                        type="button"
                                        onclick="togglePassword()"
                                        aria-label="Tampilkan atau sembunyikan kata sandi"
                                        class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-900 dark:text-slate-500 dark:hover:text-white"
                                    >
                                        <i id="toggleIcon" class="fa-solid fa-eye text-sm"></i>
                                    </button>

                                </div>

                            </div>


                            {{-- Login --}}
                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-ink-900"
                            >
                                <i class="fa-solid fa-right-to-bracket"></i>
                                Masuk ke Sistem
                            </button>

                        </form>


                        {{-- REGISTER --}}
                        <div class="mt-7 border-t border-slate-200 pt-6 text-center dark:border-slate-800">

                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                Belum punya akun?
                                <a
                                    href="{{ route('register') }}"
                                    class="ml-1 font-semibold text-orange-600 transition hover:text-orange-500 dark:text-orange-500 dark:hover:text-orange-400"
                                >
                                    Registrasi sekarang
                                </a>
                            </p>

                        </div>


                        {{-- Mobile copyright --}}
                        <p class="mt-8 text-center text-xs text-slate-500 dark:text-slate-600 lg:hidden">
                            © {{ date('Y') }} SIRAKA • Baba Auto Service
                        </p>

                    </div>

                </section>

            </div>

        </div>

        </div>

</main>


    {{-- JAVASCRIPT --}}
    <script>

        function toggleTheme() {
            var isDark = document.documentElement.classList.toggle('dark');

            try {
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            } catch (e) {}
        }


        function togglePassword() {

            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');

            if (input.type === 'password') {

                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');

            } else {

                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');

            }

        }


        function closeModal() {

            const modal = document.getElementById('successModal');

            if (modal) {
                modal.remove();
            }

        }

    </script>

</body>

</html>