<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rekam Servis Saya - SIRAKA')</title>

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
                        },
                        ink: {
                            950: '#0a0a0a',
                            900: '#141414',
                            850: '#191919',
                            800: '#1f1f1f',
                            700: '#2a2a2a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="bg-ink-950 text-neutral-200 antialiased min-h-screen flex flex-col">

    <!-- Topbar Pelanggan -->
    <header class="h-16 bg-black/95 backdrop-blur border-b border-neutral-900 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30 no-print">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/30 flex items-center justify-center text-brand-500 shrink-0">
                <i class="fa-solid fa-car-side text-base"></i>
            </div>
            <div>
                <span class="font-black text-sm text-white tracking-wide">SIRAKA</span>
                <span class="text-[10px] text-brand-400 font-bold ml-1">Portal Pelanggan</span>
            </div>
        </div>

        <div class="flex items-center gap-3 text-xs">
            <div class="hidden sm:flex flex-col items-end">
                <span class="font-bold text-white">{{ auth()->user()->name }}</span>
                <span class="text-[10px] text-neutral-400">{{ auth()->user()->no_hp ?? 'Konsumen' }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-neutral-900 hover:bg-neutral-800 text-neutral-300 hover:text-rose-400 border border-neutral-800 transition flex items-center gap-1.5 font-bold">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </header>

    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-neutral-900 py-6 text-center text-xs text-neutral-600 no-print">
        <p>SIRAKA &bull; Baba Auto Service</p>
        <p class="text-[11px] text-neutral-700 mt-1">Transparansi Riwayat Medis Kendaraan & Administrasi Bengkel</p>
    </footer>

    @stack('scripts')
</body>
</html>

