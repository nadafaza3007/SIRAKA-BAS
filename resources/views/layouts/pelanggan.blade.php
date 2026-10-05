<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Beranda') | SIRAKA Pelanggan
    </title>

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

    {{-- Tailwind CDN --}}
    {{-- Dipakai sementara agar tidak bergantung pada Vite --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family:
                'Plus Jakarta Sans',
                ui-sans-serif,
                system-ui,
                sans-serif;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            main {
                margin-left: 0 !important;
            }
        }
    </style>

    @stack('styles')
</head>


@php

    /*
    |--------------------------------------------------------------------------
    | Menu Pelanggan
    |--------------------------------------------------------------------------
    |
    | Sesuaikan nama route jika route Anda berbeda.
    |
    */

    $menu = [
        [
            'label' => 'Dashboard',
            'icon' => 'home',
            'route' => 'pelanggan.dashboard',
            'match' => [
                'pelanggan.dashboard'
            ],
        ],

        [
            'label' => 'Kendaraan',
            'icon' => 'car',
            'route' => 'pelanggan.kendaraan',
            'match' => [
                'pelanggan.kendaraan'
            ],
        ],

        [
            'label' => 'Riwayat Servis',
            'icon' => 'history',
            'route' => 'pelanggan.riwayat',
            'match' => [
                'pelanggan.riwayat',
                'pelanggan.kendaraan.riwayat',
            ],
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | User Login
    |--------------------------------------------------------------------------
    */

    $pengguna = auth()->user();


    /*
    |--------------------------------------------------------------------------
    | Inisial User
    |--------------------------------------------------------------------------
    */

    $inisial = mb_strtoupper(
        mb_substr(
            $pengguna->name ?? 'P',
            0,
            1
        )
    );


    /*
    |--------------------------------------------------------------------------
    | Cek Route Logout
    |--------------------------------------------------------------------------
    */

    $bisaKeluar = Route::has('logout');

@endphp


<body class="bg-[#f5f7fb] text-slate-800 antialiased">


    {{-- =========================================================
        SIDEBAR DESKTOP
    ========================================================== --}}

    <aside
        class="
            fixed
            inset-y-0
            left-0
            z-30
            hidden
            w-64
            flex-col
            bg-[#111827]
            text-white
            lg:flex
            no-print
        "
    >

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-7">

            <img
                src="{{ asset('images/logo-siraka.png') }}"
                alt="Logo SIRAKA"
                class="h-9 w-9 rounded-lg object-cover"
            >

            <div>

                <p
                    class="
                        text-lg
                        font-bold
                        leading-tight
                    "
                >
                    SIRAKA
                </p>

                <p class="text-xs text-slate-400">
                    Sistem Riwayat Kendaraan
                </p>

            </div>

        </div>


        {{-- Menu Navigation --}}
        <nav
            class="flex-1 space-y-1 px-4"
            aria-label="Menu utama"
        >

            @foreach ($menu as $item)

                @php
                    $aktif = request()->routeIs(
                        ...$item['match']
                    );
                @endphp

                <a
                    href="{{ route($item['route']) }}"

                    @if ($aktif)
                        aria-current="page"
                    @endif

                    @class([
                        '
                            flex
                            items-center
                            gap-3
                            rounded-lg
                            px-4
                            py-2.5
                            text-sm
                            transition
                            focus-visible:outline
                            focus-visible:outline-2
                            focus-visible:outline-offset-2
                            focus-visible:outline-orange-400
                        ',

                        '
                            bg-orange-500
                            font-semibold
                            text-white
                        ' => $aktif,

                        '
                            font-medium
                            text-slate-300
                            hover:bg-white/5
                            hover:text-white
                        ' => ! $aktif,
                    ])
                >

                    <x-icon
                        :name="$item['icon']"
                        class="h-5 w-5 shrink-0"
                    />

                    <span>
                        {{ $item['label'] }}
                    </span>

                </a>

            @endforeach

        </nav>


        {{-- Profil User --}}
        <div
            class="
                border-t
                border-white/10
                p-4
            "
        >

            <div class="flex items-center gap-3">

                {{-- Inisial --}}
                <span
                    class="
                        grid
                        h-9
                        w-9
                        shrink-0
                        place-items-center
                        rounded-full
                        bg-white/10
                        text-sm
                        font-semibold
                    "
                >
                    {{ $inisial }}
                </span>


                {{-- Nama --}}
                <div class="min-w-0">

                    <p
                        class="
                            truncate
                            text-sm
                            font-medium
                            text-white
                        "
                    >
                        {{ $pengguna->name ?? 'Pelanggan' }}
                    </p>

                    <p
                        class="
                            truncate
                            text-xs
                            text-slate-400
                        "
                    >
                        {{ $pengguna->no_hp ?? 'Pelanggan' }}
                    </p>

                </div>

            </div>


            {{-- Logout --}}
            @if ($bisaKeluar)

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="mt-3"
                >

                    @csrf

                    <button
                        type="submit"
                        class="
                            flex
                            w-full
                            items-center
                            gap-2
                            rounded-lg
                            px-3
                            py-2
                            text-sm
                            text-slate-400
                            transition
                            hover:bg-white/5
                            hover:text-white
                            focus-visible:outline
                            focus-visible:outline-2
                            focus-visible:outline-orange-400
                        "
                    >

                        <x-icon
                            name="logout"
                            class="h-4 w-4"
                        />

                        <span>
                            Keluar
                        </span>

                    </button>

                </form>

            @endif

        </div>

    </aside>



    {{-- =========================================================
        TOP BAR MOBILE
    ========================================================== --}}

    <header
        class="
            sticky
            top-0
            z-40
            flex
            items-center
            justify-between
            border-b
            border-slate-200
            bg-white
            px-5
            py-3
            lg:hidden
            no-print
        "
    >

        {{-- Logo Mobile --}}
        <div class="flex items-center gap-3">

            <img
                src="{{ asset('images/logo-siraka.png') }}"
                alt="Logo SIRAKA"
                class="h-9 w-9 rounded-lg object-cover"
            >

            <div>

                <p class="text-sm font-bold text-slate-900">
                    SIRAKA
                </p>

                <p
                    class="
                        text-[10px]
                        font-semibold
                        text-orange-500
                    "
                >
                    Portal Pelanggan
                </p>

            </div>

        </div>


        {{-- Logout Mobile --}}
        @if ($bisaKeluar)

            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf

                <button
                    type="submit"
                    aria-label="Keluar"
                    class="
                        rounded-lg
                        p-2
                        text-slate-500
                        transition
                        hover:bg-slate-100
                        hover:text-red-500
                        focus-visible:outline
                        focus-visible:outline-2
                        focus-visible:outline-orange-500
                    "
                >

                    <x-icon
                        name="logout"
                        class="h-5 w-5"
                    />

                </button>

            </form>

        @endif

    </header>



    {{-- =========================================================
        KONTEN UTAMA
    ========================================================== --}}

    <main
        class="
            min-h-screen
            pb-24
            lg:ml-64
            lg:pb-0
        "
    >

        <div
            class="
                mx-auto
                w-full
                max-w-7xl
                p-4
                sm:p-6
                lg:p-8
            "
        >


            {{-- =================================================
                FLASH MESSAGE SUCCESS
            ================================================== --}}

            @if (session('success'))

                <div
                    class="
                        mb-5
                        flex
                        items-center
                        gap-2
                        rounded-xl
                        border
                        border-emerald-200
                        bg-emerald-50
                        p-3.5
                        text-sm
                        font-semibold
                        text-emerald-700
                    "
                >

                    <x-icon
                        name="check"
                        class="h-5 w-5 shrink-0"
                    />

                    <span>
                        {{ session('success') }}
                    </span>

                </div>

            @endif



            {{-- =================================================
                FLASH MESSAGE ERROR
            ================================================== --}}

            @if (session('error'))

                <div
                    class="
                        mb-5
                        flex
                        items-center
                        gap-2
                        rounded-xl
                        border
                        border-red-200
                        bg-red-50
                        p-3.5
                        text-sm
                        font-semibold
                        text-red-700
                    "
                >

                    <x-icon
                        name="alert"
                        class="h-5 w-5 shrink-0"
                    />

                    <span>
                        {{ session('error') }}
                    </span>

                </div>

            @endif



            {{-- =================================================
                ISI HALAMAN
            ================================================== --}}

            @yield('content')

        </div>



        {{-- =====================================================
            FOOTER
        ====================================================== --}}

        <footer
            class="
                border-t
                border-slate-200
                py-6
                text-center
                text-xs
                text-slate-400
                no-print
            "
        >

            <p>
                SIRAKA &bull; Baba Auto Service
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    text-slate-400
                "
            >
                Transparansi Riwayat Kendaraan & Administrasi Bengkel
            </p>

        </footer>

    </main>



    {{-- =========================================================
        BOTTOM NAVIGATION MOBILE
    ========================================================== --}}

    <nav
        class="
            fixed
            inset-x-0
            bottom-0
            z-40
            grid
            grid-cols-3
            border-t
            border-slate-200
            bg-white
            pb-[env(safe-area-inset-bottom)]
            lg:hidden
            no-print
        "
        aria-label="Menu utama"
    >

        @foreach ($menu as $item)

            @php
                $aktif = request()->routeIs(
                    ...$item['match']
                );
            @endphp

            <a
                href="{{ route($item['route']) }}"

                @if ($aktif)
                    aria-current="page"
                @endif

                @class([
                    '
                        flex
                        flex-col
                        items-center
                        gap-1
                        py-2.5
                        text-xs
                        transition
                    ',

                    '
                        font-semibold
                        text-orange-600
                    ' => $aktif,

                    '
                        font-medium
                        text-slate-500
                    ' => ! $aktif,
                ])
            >

                <x-icon
                    :name="$item['icon']"
                    class="h-6 w-6"
                />

                <span>
                    {{ $item['label'] }}
                </span>

            </a>

        @endforeach

    </nav>



    {{-- Script tambahan dari halaman child --}}
    @stack('scripts')

</body>
</html>