<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - SIRAKA')</title>

    <!-- Tailwind CSS via CDN (Tanpa perlu npm / Vite) -->
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
                            900: '#7c2d12',
                        },
                        ink: {
                            950: '#0a0a0a',
                            900: '#141414',
                            850: '#191919',
                            800: '#1f1f1f',
                            700: '#2a2a2a',
                            600: '#3a3a3a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Icons & Google Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #141414; }
        ::-webkit-scrollbar-thumb { background: #3a3a3a; border-radius: 8px; }
        ::-webkit-scrollbar-thumb:hover { background: #f97316; }
    @media print {
        /* Sembunyikan sidebar, navbar, tombol, dan form input saat cetak */
        .no-print, sidebar, header, nav, button, form {
            display: none !important;
        }
        body {
            background-color: white !important;
            color: black !important;
        }
        #printAreaStandar {
            border: none !important;
            background: transparent !important;
            color: black !important;
            box-shadow: none !important;
            width: 100% !important;
            padding: 0 !important;
        }
        /* Paksa teks berwarna gelap agar terlihat di kertas */
        #printAreaStandar * {
            color: black !important;
        }
    }
    </style>
</head>
<body class="bg-ink-950 text-neutral-200 antialiased flex min-h-screen">

    <!-- Overlay Mobile Sidebar -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 z-30 bg-black/70 backdrop-blur-sm hidden lg:hidden"></div>

    <!-- Sidebar Admin (Desktop: statis | Mobile: drawer) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-black text-neutral-400 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 flex flex-col justify-between border-r border-neutral-900">
        <div class="overflow-y-auto">
            <!-- Header Brand Bengkel -->
            <div class="h-16 flex items-center gap-3 px-6 bg-black border-b border-neutral-900 sticky top-0">
                <img src="{{ asset('images/logo-siraka.png') }}"
                    alt="Logo SIRAKA"
                    class="w-9 h-9 object-contain shrink-0">
                <div>
                    <h1 class="font-extrabold text-sm text-white tracking-wide leading-none">SIRAKA</h1>
                    <span class="text-[9px] text-brand-400 font-bold uppercase tracking-wider">
                        {{ auth()->check() && auth()->user()->role === 'admin' ? 'Admin Bengkel' : 'Mekanik Bengkel' }}
                    </span>
                </div>
                <button onclick="toggleSidebar()" class="ml-auto lg:hidden text-neutral-500 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Menu Navigasi -->
            <nav class="p-4 space-y-1 text-xs font-semibold">
                <div class="px-3 py-1.5 text-[10px] uppercase tracking-wider text-neutral-600 font-bold">Utama</div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                    <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                    <span>Dashboard Utama</span>
                </a>

                <div class="pt-3 px-3 py-1.5 text-[10px] uppercase tracking-wider text-neutral-600 font-bold">Operasional</div>
                <a href="{{ route('admin.rekam-servis.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.rekam-servis.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                    <i class="fa-solid fa-clipboard-list w-4 text-center"></i>
                    <span>Rekam Servis</span>
                </a>
                <a href="{{ route('admin.konsumen.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.konsumen.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                    <i class="fa-solid fa-users w-4 text-center"></i>
                    <span>Data Konsumen</span>
                </a>

                <!-- KHUSUS ADMIN: Master Data & Keuangan (Disembunyikan untuk Mekanik) -->
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <div class="pt-3 px-3 py-1.5 text-[10px] uppercase tracking-wider text-neutral-600 font-bold">Master Data (Admin)</div>
                    <a href="{{ route('admin.sparepart.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.sparepart.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                        <i class="fa-solid fa-boxes-stacked w-4 text-center"></i>
                        <span>Master Sparepart</span>
                    </a>
                    <a href="{{ route('admin.jasa.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.jasa.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                        <i class="fa-solid fa-screwdriver-wrench w-4 text-center"></i>
                        <span>Master Jasa</span>
                    </a>
                    <a href="{{ route('admin.diagnosa.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.diagnosa.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                        <i class="fa-solid fa-wand-magic-sparkles w-4 text-center"></i>
                        <span>Master Diagnosa</span>
                    </a>

                    <div class="pt-3 px-3 py-1.5 text-[10px] uppercase tracking-wider text-neutral-600 font-bold">Keuangan</div>
                    <a href="{{ route('admin.laporan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-neutral-900 hover:text-white transition {{ request()->routeIs('admin.laporan.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : '' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                        <span>Laporan Pemasukan</span>
                    </a>
                @endif
            </nav>
        </div>

        <div class="p-4 border-t border-neutral-900 text-[11px] text-neutral-600 text-center flex flex-col gap-2">
            <div>SIRAKA &copy; {{ date('Y') }}</div>
            <!-- Tombol Logout di Sidebar bawah -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2 px-3 bg-neutral-900 hover:bg-rose-500/20 text-neutral-400 hover:text-rose-400 rounded-xl font-bold transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Konten Kanan -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="h-16 bg-black/95 backdrop-blur border-b border-neutral-900 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden w-9 h-9 rounded-lg bg-neutral-900 flex items-center justify-center text-neutral-300 hover:text-brand-500 transition">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="hidden sm:block text-xs font-bold text-neutral-400">Panel Administrator <span class="text-brand-500">SIRAKA</span></div>
                <div class="sm:hidden flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-brand-500 flex items-center justify-center text-white">
                        <i class="fa-solid fa-wrench text-[10px]"></i>
                    </div>
                    <span class="font-extrabold text-sm text-white">SIRAKA</span>
                </div>
            </div>
            <!-- Pojok Kanan Atas: Menampilkan Nama / Username yang sedang login -->
            <div class="flex items-center gap-2 text-xs font-bold text-neutral-300">
                <div class="w-8 h-8 rounded-full bg-neutral-800 flex items-center justify-center text-brand-500 border border-neutral-700">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span class="hidden sm:inline">{{ auth()->check() ? (auth()->user()->name ?? auth()->user()->username ?? 'Admin') : 'Guest' }}</span>
            </div>
        </header>

        <!-- Area Konten Utama -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-24 lg:pb-8">
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Bottom Navigation (Mobile Only) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-black border-t border-neutral-900 px-2 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
            <div class="grid grid-cols-5 text-center">
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1 py-1 {{ request()->routeIs('admin.dashboard') ? 'text-brand-500' : 'text-neutral-500' }}">
                    <i class="fa-solid fa-table-cells-large text-base"></i>
                    <span class="text-[10px] font-bold">Dashboard</span>
                </a>
                <a href="{{ route('admin.konsumen.index') }}" class="flex flex-col items-center gap-1 py-1 {{ request()->routeIs('admin.konsumen.*') ? 'text-brand-500' : 'text-neutral-500' }}">
                    <i class="fa-solid fa-user-group text-base"></i>
                    <span class="text-[10px] font-bold">Konsumen</span>
                </a>
                <a href="{{ route('admin.rekam-servis.create') }}" class="flex flex-col items-center -mt-5">
                    <span class="w-12 h-12 rounded-full bg-brand-500 shadow-lg shadow-brand-500/40 flex items-center justify-center text-white text-lg border-4 border-black">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                </a>
                <a href="{{ route('admin.rekam-servis.index') }}" class="flex flex-col items-center gap-1 py-1 {{ request()->routeIs('admin.rekam-servis.*') ? 'text-brand-500' : 'text-neutral-500' }}">
                    <i class="fa-solid fa-clock-rotate-left text-base"></i>
                    <span class="text-[10px] font-bold">Riwayat</span>
                </a>
                <button onclick="toggleSidebar()" type="button" class="flex flex-col items-center gap-1 py-1 text-neutral-500">
                    <i class="fa-solid fa-bars text-base"></i>
                    <span class="text-[10px] font-bold">Menu</span>
                </button>
            </div>
        </nav>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>