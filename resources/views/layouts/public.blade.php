<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#101820">
    <meta name="description" content="Baba Auto Service - bengkel mobil di Pekanbaru. Lihat layanan, promo, tips perawatan mobil, dan akses riwayat servis kendaraan Anda melalui SIRAKA.">
    <title>@yield('title', 'Baba Auto Service | SIRAKA')</title>

    <script>
        // Terapkan tema sebelum halaman tampil (kunci sama dengan panel admin & portal pelanggan)
        (function () {
            document.documentElement.classList.add('js');
            try {
                var tema = localStorage.getItem('siraka-theme') || 'light';
                if (tema === 'dark') document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        bas: { ink: '#101820', dark: '#17212b', orange: '#f97316', paper: '#f7f8fa' },
                        brand: { 50: '#fff7ed', 100: '#ffedd5', 400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c' },
                        page: 'var(--page)', surface: 'var(--surface)', surface2: 'var(--surface-2)',
                        line: 'var(--line)', main: 'var(--text)', muted: 'var(--muted)', soft: 'var(--soft)',
                        warnbg: 'var(--warn-bg)', warnfg: 'var(--warn-fg)', warnline: 'var(--warn-line)',
                        okbg: 'var(--ok-bg)', okfg: 'var(--ok-fg)',
                        infobg: 'var(--info-bg)', infofg: 'var(--info-fg)',
                        brandbg: 'var(--brand-bg)', brandfg: 'var(--brand-fg)',
                    },
                    boxShadow: { soft: 'var(--shadow-soft)' }
                }
            }
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ===== Palet tema (selaras portal pelanggan & admin) ===== */
        :root {
            color-scheme: light;
            --page: #f6f4f1; --surface: #ffffff; --surface-2: #faf8f5; --line: #ece8e2;
            --text: #292524; --muted: #78716c; --soft: #a8a29e;
            --warn-bg: #fff6e0; --warn-fg: #a15c07; --warn-line: #f8e2ae;
            --ok-bg: #e6f6ec;   --ok-fg: #16794a;
            --info-bg: #e8f2fc; --info-fg: #2563a8;
            --brand-bg: #ffefe2; --brand-fg: #d2540c;
            --shadow-soft: 0 12px 40px rgba(68, 44, 20, .09);
            --glass: rgba(255, 255, 255, .86);
        }
        .dark {
            color-scheme: dark;
            --page: #0a0a0a; --surface: #141414; --surface-2: #1c1c1c; --line: #262626;
            --text: #e5e5e5; --muted: #a3a3a3; --soft: #737373;
            --warn-bg: rgba(245, 158, 11, .10); --warn-fg: #fcd34d; --warn-line: rgba(245, 158, 11, .25);
            --ok-bg: rgba(16, 185, 129, .12);   --ok-fg: #6ee7b7;
            --info-bg: rgba(14, 165, 233, .12); --info-fg: #7dd3fc;
            --brand-bg: rgba(249, 115, 22, .14); --brand-fg: #fdba74;
            --shadow-soft: 0 12px 40px rgba(0, 0, 0, .5);
            --glass: rgba(10, 10, 10, .82);
        }

        html { scroll-behavior: smooth; scroll-padding-top: 88px; }
        html, body { max-width: 100%; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--page); color: var(--text); overflow-x: clip; }
        .font-accent { font-family: 'Instrument Serif', Georgia, serif; font-style: italic; font-weight: 400; letter-spacing: -0.01em; }
        .text-balance { text-wrap: balance; }
        .glass { background: var(--glass); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
        .mobile-menu[hidden] { display: none; }
        .map-frame { filter: grayscale(.12) contrast(1.02); }
        .dark .map-frame { filter: invert(.9) hue-rotate(180deg) grayscale(.2); }
        :focus-visible { outline: 2px solid #f97316; outline-offset: 3px; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--page); }
        ::-webkit-scrollbar-thumb { background: var(--line); border-radius: 8px; }
        ::-webkit-scrollbar-thumb:hover { background: #f97316; }

        .hero-grid {
            background-image: linear-gradient(var(--line) 1px, transparent 1px), linear-gradient(90deg, var(--line) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(ellipse at 70% 30%, #000 10%, transparent 70%);
            -webkit-mask-image: radial-gradient(ellipse at 70% 30%, #000 10%, transparent 70%);
        }

        /* ===== Animasi muncul saat scroll ===== */
        @media (prefers-reduced-motion: no-preference) {
            .js .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
            .js .reveal.in { opacity: 1; transform: none; }
            .float-a { animation: floaty 6s ease-in-out infinite; }
            .float-b { animation: floaty 7s ease-in-out -2s infinite; }
            @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
            .pin-ring { animation: pinring 1.8s ease-out infinite; }
            @keyframes pinring { 0% { transform: scale(1); opacity: .65; } 100% { transform: scale(2.3); opacity: 0; } }
        }

        /* ===== Toggle tema ===== */
        .theme-switch { position: relative; display: inline-block; width: 42px; height: 23px; flex: 0 0 auto; }
        .theme-switch input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .theme-slider { position: absolute; inset: 0; cursor: pointer; border: 1px solid var(--line); border-radius: 999px; background: #d6d3d1; transition: background .2s, border-color .2s; }
        .theme-slider::before { content: ""; position: absolute; top: 2px; left: 2px; width: 17px; height: 17px; border-radius: 50%; background: #fff; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.25); }
        .theme-switch input:checked + .theme-slider { background: #f97316; border-color: #f97316; }
        .theme-switch input:checked + .theme-slider::before { transform: translateX(19px); }
        .theme-switch input:focus-visible + .theme-slider { outline: 2px solid #fb923c; outline-offset: 3px; }
    </style>
    @yield('head')
</head>

<body class="antialiased">
    @yield('content')

    <script>
        function applySirakaTheme(tema) {
            var gelap = tema === 'dark';
            document.documentElement.classList.toggle('dark', gelap);
            document.querySelectorAll('.js-theme-switch').forEach(function (el) { el.checked = gelap; });
            document.querySelectorAll('.js-theme-label').forEach(function (el) { el.textContent = gelap ? 'Dark' : 'Light'; });
            document.dispatchEvent(new CustomEvent('siraka:theme', { detail: tema }));
        }
        function toggleSirakaTheme() {
            var sekarang = 'light';
            try { sekarang = localStorage.getItem('siraka-theme') || 'light'; } catch (e) {}
            var berikut = sekarang === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem('siraka-theme', berikut); } catch (e) {}
            applySirakaTheme(berikut);
        }
        document.addEventListener('DOMContentLoaded', function () {
            var tema = 'light';
            try { tema = localStorage.getItem('siraka-theme') || 'light'; } catch (e) {}
            applySirakaTheme(tema);

            // Animasi muncul saat di-scroll
            var items = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) { items.forEach(function (el) { el.classList.add('in'); }); return; }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
            }, { threshold: 0.12 });
            items.forEach(function (el) { io.observe(el); });
        });
    </script>

    @yield('scripts')
</body>
</html>