<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#000000">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_bengkel.jpg') }}">
    <title>@yield('title', 'Beranda') | SIRAKA Pelanggan</title>

    <script>
        // Terapkan tema sebelum halaman tampil (kunci sama dengan panel admin, default: dark)
        (function () {
            try {
                var tema = localStorage.getItem('siraka-theme') || 'dark';
                if (tema !== 'light') document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#fff7ed', 100: '#ffedd5', 400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c' },
                        // Warna semantik: otomatis berganti sesuai tema (lihat :root di bawah)
                        page:    'var(--page)',
                        surface: 'var(--surface)',
                        surface2:'var(--surface-2)',
                        line:    'var(--line)',
                        main:    'var(--text)',
                        muted:   'var(--muted)',
                        soft:    'var(--soft)',
                        side:    'var(--side)',
                        warnbg:  'var(--warn-bg)',
                        warnfg:  'var(--warn-fg)',
                        warnline:'var(--warn-line)',
                        okbg:    'var(--ok-bg)',
                        okfg:    'var(--ok-fg)',
                        infobg:  'var(--info-bg)',
                        infofg:  'var(--info-fg)',
                        brandbg: 'var(--brand-bg)',
                        brandfg: 'var(--brand-fg)',
                    },
                    boxShadow: { soft: 'var(--shadow-soft)' }
                }
            }
        }
    </script>

    <style>
        /* ===== Palet tema (selaras panel admin) ===== */
        :root {
            color-scheme: light;
            --page: #f6f4f1;
            --surface: #ffffff;
            --surface-2: #faf8f5;
            --line: #ece8e2;
            --text: #292524;
            --muted: #78716c;
            --soft: #a8a29e;
            --side: #ffffff;
            --warn-bg: #fff6e0;  --warn-fg: #a15c07;  --warn-line: #f8e2ae;
            --ok-bg:   #e6f6ec;  --ok-fg:   #16794a;
            --info-bg: #e8f2fc;  --info-fg: #2563a8;
            --brand-bg:#ffefe2;  --brand-fg:#d2540c;
            --shadow-soft: 0 8px 28px rgba(68, 44, 20, .08);
        }
        .dark {
            color-scheme: dark;
            --page: #0a0a0a;
            --surface: #141414;
            --surface-2: #1c1c1c;
            --line: #262626;
            --text: #e5e5e5;
            --muted: #a3a3a3;
            --soft: #737373;
            --side: #000000;
            --warn-bg: rgba(245, 158, 11, .10);  --warn-fg: #fcd34d;  --warn-line: rgba(245, 158, 11, .25);
            --ok-bg:   rgba(16, 185, 129, .12);  --ok-fg:   #6ee7b7;
            --info-bg: rgba(14, 165, 233, .12);  --info-fg: #7dd3fc;
            --brand-bg:rgba(249, 115, 22, .14);  --brand-fg:#fdba74;
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, .45);
        }

        html { scroll-behavior: smooth; }
        html, body { width: 100%; max-width: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--page);
            color: var(--text);
            overflow-x: clip;
        }
        main { min-width: 0; }
        .font-accent { font-family: 'Instrument Serif', Georgia, serif; font-style: italic; font-weight: 400; letter-spacing: -0.01em; }
        button, a { -webkit-tap-highlight-color: transparent; }
        :focus-visible { outline: 2px solid #f97316; outline-offset: 3px; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--page); }
        ::-webkit-scrollbar-thumb { background: var(--line); border-radius: 8px; }
        ::-webkit-scrollbar-thumb:hover { background: #f97316; }

        @media (max-width: 639px) {
            input:not([type="checkbox"]):not([type="radio"]), select, textarea { font-size: 16px; }
        }

        /* ===== Toggle tema (sama dengan admin) ===== */
        .theme-switch { position: relative; display: inline-block; width: 42px; height: 23px; flex: 0 0 auto; }
        .theme-switch input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .theme-slider {
            position: absolute; inset: 0; cursor: pointer; border: 1px solid #525252;
            border-radius: 999px; background: #404040; transition: background .2s ease, border-color .2s ease;
        }
        .theme-slider::before {
            content: ""; position: absolute; top: 2px; left: 2px; width: 17px; height: 17px;
            border-radius: 50%; background: #fff; transition: transform .2s ease;
        }
        .theme-switch input:checked + .theme-slider { background: #f97316; border-color: #f97316; }
        .theme-switch input:checked + .theme-slider::before { transform: translateX(19px); }
        .theme-switch input:focus-visible + .theme-slider { outline: 2px solid #fb923c; outline-offset: 3px; }

        /* =====================================================
           KOMPATIBILITAS DARK MODE untuk halaman lama
           (kendaraan, riwayat, detail servis, dll) yang masih
           memakai kelas slate/putih. Tidak perlu diedit satu-satu.
        ===================================================== */
        .dark main .bg-white,
        .dark main .bg-white\/95 { background-color: var(--surface); }
        .dark main .bg-slate-50,
        .dark main .bg-slate-50\/60,
        .dark main .bg-slate-100,
        .dark main .bg-slate-100\/80,
        .dark main .hover\:bg-slate-50:hover { background-color: var(--surface-2); }

        .dark main .text-slate-900,
        .dark main .text-slate-800 { color: var(--text); }
        .dark main .text-slate-700,
        .dark main .text-slate-600 { color: #d4d4d4; }
        .dark main .text-slate-500,
        .dark main .text-slate-400 { color: var(--muted); }

        .dark main .border-slate-100,
        .dark main .border-slate-200,
        .dark main .border-slate-300,
        .dark main .ring-slate-200 { border-color: var(--line); --tw-ring-color: var(--line); }
        .dark main .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        .dark main .divide-slate-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--line); }

        .dark main .bg-emerald-50, .dark main .bg-emerald-100 { background-color: var(--ok-bg); }
        .dark main .text-emerald-600, .dark main .text-emerald-700, .dark main .text-emerald-800 { color: var(--ok-fg); }
        .dark main .border-emerald-200 { border-color: rgba(16,185,129,.3); }

        .dark main .bg-amber-50, .dark main .bg-amber-100 { background-color: var(--warn-bg); }
        .dark main .text-amber-600, .dark main .text-amber-700, .dark main .text-amber-800 { color: var(--warn-fg); }
        .dark main .border-amber-100, .dark main .border-amber-200 { border-color: var(--warn-line); }

        .dark main .bg-rose-50, .dark main .bg-rose-100 { background-color: rgba(244,63,94,.12); }
        .dark main .text-rose-600, .dark main .text-rose-700, .dark main .text-rose-800 { color: #fda4af; }
        .dark main .border-rose-200 { border-color: rgba(244,63,94,.3); }

        .dark main .bg-sky-50, .dark main .bg-sky-100 { background-color: var(--info-bg); }
        .dark main .text-sky-600, .dark main .text-sky-700 { color: var(--info-fg); }

        .dark main .bg-orange-50, .dark main .bg-orange-50\/60, .dark main .bg-orange-100 { background-color: var(--brand-bg); }
        .dark main .text-orange-600, .dark main .text-orange-700, .dark main .text-orange-800 { color: var(--brand-fg); }
        .dark main .border-orange-100, .dark main .border-orange-200 { border-color: rgba(249,115,22,.25); }

        .dark main .bg-slate-900 { background-color: #262626; }
        .dark main .bg-slate-900:hover, .dark main .hover\:bg-slate-700:hover { background-color: #333333; }

        .dark main input:not([type="checkbox"]):not([type="radio"]),
        .dark main select,
        .dark main textarea { background-color: var(--surface-2); color: var(--text); border-color: var(--line); }
        .dark main input::placeholder, .dark main textarea::placeholder { color: var(--soft); }

        .dark main table { color: #d4d4d4; }
        .dark main thead, .dark main thead tr { background-color: var(--surface-2); }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            main { margin-left: 0 !important; padding: 0 !important; }
        }
    </style>

    @stack('styles')
</head>

@php
    $menu = [
        ['label' => 'Dashboard',      'icon' => 'home',    'route' => 'pelanggan.dashboard', 'match' => ['pelanggan.dashboard']],
        ['label' => 'Kendaraan',      'icon' => 'car',     'route' => 'pelanggan.kendaraan', 'match' => ['pelanggan.kendaraan']],
        ['label' => 'Riwayat Servis', 'icon' => 'history', 'route' => 'pelanggan.riwayat',   'match' => ['pelanggan.riwayat', 'pelanggan.kendaraan.riwayat', 'pelanggan.detail-servis']],
    ];

    $pengguna = auth()->user();
    $inisial = mb_strtoupper(mb_substr($pengguna->name ?? 'P', 0, 1));
    $bisaKeluar = \Illuminate\Support\Facades\Route::has('logout');
@endphp

<body class="min-h-screen bg-page text-main antialiased">

    {{-- ================= SIDEBAR DESKTOP ================= --}}
    <aside class="dark no-print fixed inset-y-0 left-0 z-30 hidden w-64 flex-col justify-between border-r border-neutral-900 bg-side lg:flex">
        <div>
            <div class="flex h-16 items-center gap-3 border-b border-neutral-900 bg-black px-6">
                <img src="{{ asset('images/logo_bengkel.jpg') }}" alt="Logo SIRAKA" class="h-9 w-9 shrink-0 rounded-lg object-cover">
                <div>
                    <p class="text-sm font-extrabold leading-none tracking-wide text-white">SIRAKA</p>
                    <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-brand-400">Portal Pelanggan</p>
                </div>
            </div>

            <nav class="space-y-1 p-4 text-xs font-semibold" aria-label="Menu utama">
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-soft">Menu utama</div>
                @foreach ($menu as $item)
                    @php $aktif = request()->routeIs(...$item['match']); @endphp
                    <a href="{{ route($item['route']) }}"
                       @if ($aktif) aria-current="page" @endif
                       @class([
                           'flex items-center gap-3 rounded-xl px-3 py-2.5 transition',
                           'bg-brand-500 text-white shadow-md shadow-brand-500/20' => $aktif,
                           'text-muted hover:bg-surface2 hover:text-main' => ! $aktif,
                       ])>
                        <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="space-y-3 border-t border-line p-4">
            <div class="flex min-w-0 items-center gap-3 rounded-xl bg-surface2 p-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brandbg text-sm font-extrabold text-brandfg">{{ $inisial }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-main">{{ $pengguna->name ?? 'Pelanggan' }}</p>
                    <p class="truncate text-[11px] text-muted">{{ $pengguna->no_hp ?? 'Akun pelanggan' }}</p>
                </div>
            </div>

            @if ($bisaKeluar)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-surface2 px-3 py-2 text-xs font-bold text-muted transition hover:bg-rose-500/15 hover:text-rose-500">
                        <x-icon name="logout" class="h-4 w-4" />
                        <span>Keluar</span>
                    </button>
                </form>
            @endif
            <p class="text-center text-[11px] text-soft">SIRAKA &copy; {{ date('Y') }}</p>
        </div>
    </aside>

    <div class="min-w-0 lg:pl-64 flex min-h-screen flex-col">
        {{-- ================= TOPBAR (desktop & mobile, hitam seperti admin) ================= --}}
        <header class="no-print sticky top-0 z-40 flex h-16 items-center justify-between border-b border-line bg-white/95 px-4 backdrop-blur dark:border-neutral-900 dark:bg-black/95 sm:px-6 lg:z-20">
            <a href="{{ route('pelanggan.dashboard') }}" class="flex min-w-0 items-center gap-2.5 lg:hidden">
                <img src="{{ asset('images/logo_bengkel.jpg') }}" alt="Logo SIRAKA" class="h-8 w-8 rounded-lg object-cover">
                <span class="min-w-0">
                    <span class="block text-sm font-extrabold leading-none text-main">SIRAKA</span>
                    <span class="mt-0.5 block text-[9px] font-bold uppercase tracking-wider text-brand-400">Portal Pelanggan</span>
                </span>
            </a>
            <div class="hidden text-xs font-bold text-muted lg:block">Portal Pelanggan <span class="text-brand-500">SIRAKA</span></div>

            <div class="flex items-center gap-3 text-xs font-bold text-main">
                <div class="flex items-center gap-2">
                    <span class="js-theme-label hidden text-xs font-semibold text-main sm:inline">Dark</span>
                    <label class="theme-switch" title="Ganti tema tampilan">
                        <input type="checkbox" class="js-theme-switch" onchange="toggleSirakaTheme()" aria-label="Ganti Light Mode dan Dark Mode">
                        <span class="theme-slider"></span>
                    </label>
                </div>
                <span class="hidden items-center gap-2.5 sm:flex">
                    <span class="grid h-8 w-8 place-items-center rounded-full border border-line bg-surface2 text-brand-500">{{ $inisial }}</span>
                    <span class="hidden max-w-[10rem] truncate md:inline">{{ $pengguna->name ?? 'Pelanggan' }}</span>
                </span>
                @if ($bisaKeluar)
                    <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                        @csrf
                        <button type="submit" aria-label="Keluar" class="grid h-9 w-9 place-items-center rounded-lg bg-surface2 text-muted transition hover:text-rose-500">
                            <x-icon name="logout" class="h-4 w-4" />
                        </button>
                    </form>
                @endif
            </div>
        </header>

        {{-- ================= KONTEN ================= --}}
        <main class="min-w-0 w-full flex-1 px-4 pb-28 pt-5 sm:px-6 sm:pt-7 lg:px-8 lg:pb-8">
            <div class="mx-auto w-full max-w-7xl">
                @if (session('success'))
                    <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-500/30 bg-okbg px-4 py-3.5 text-sm font-medium text-okfg">
                        <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div role="alert" class="mb-5 flex items-start gap-3 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3.5 text-sm font-medium text-rose-500">
                        <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="no-print border-t border-line bg-surface px-4 py-5 text-center sm:px-6 lg:px-8">
            <p class="text-xs font-semibold text-muted">SIRAKA <span class="mx-1 text-brand-500">•</span> Baba Auto Service</p>
            <p class="mt-1 text-[11px] text-soft">Transparansi Riwayat Kendaraan &amp; Administrasi Bengkel</p>
        </footer>
    </div>

    {{-- ================= NAVIGASI BAWAH MOBILE ================= --}}
    <nav class="no-print fixed inset-x-0 bottom-0 z-40 grid grid-cols-3 border-t border-neutral-900 bg-black px-2 pb-[env(safe-area-inset-bottom)] pt-1 lg:hidden" aria-label="Menu utama">
        @foreach ($menu as $item)
            @php $aktif = request()->routeIs(...$item['match']); @endphp
            <a href="{{ route($item['route']) }}"
               @if ($aktif) aria-current="page" @endif
               @class([
                   'flex min-h-16 flex-col items-center justify-center gap-1.5 px-2 py-2 text-[10px] transition',
                   'font-bold text-brand-500' => $aktif,
                   'font-semibold text-neutral-500 hover:text-neutral-200' => ! $aktif,
               ])>
                <x-icon :name="$item['icon']" class="h-5 w-5" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <script>
        function applySirakaTheme(tema) {
            var gelap = tema !== 'light';
            document.documentElement.classList.toggle('dark', gelap);
            document.querySelectorAll('.js-theme-switch').forEach(function (el) { el.checked = !gelap; });
            document.querySelectorAll('.js-theme-label').forEach(function (el) { el.textContent = gelap ? 'Dark' : 'Light'; });
        }

        function toggleSirakaTheme() {
            var sekarang = 'dark';
            try { sekarang = localStorage.getItem('siraka-theme') || 'dark'; } catch (e) {}
            var berikut = sekarang === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem('siraka-theme', berikut); } catch (e) {}
            applySirakaTheme(berikut);
        }

        document.addEventListener('DOMContentLoaded', function () {
            var tema = 'dark';
            try { tema = localStorage.getItem('siraka-theme') || 'dark'; } catch (e) {}
            applySirakaTheme(tema);
        });
    </script>

    @stack('scripts')
</body>
</html>