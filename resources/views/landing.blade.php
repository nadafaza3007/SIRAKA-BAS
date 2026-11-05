@extends('layouts.public')

@section('title', 'Baba Auto Service | Bengkel Mobil Pekanbaru')

@section('head')

<style>
    /* ===== Mobil interaktif ===== */
    .car-line { fill: none; stroke: var(--text); stroke-width: 1.4; stroke-dasharray: 4 5; opacity: .35; transition: opacity .25s, stroke-width .25s; }
    .car-line.on { stroke: #f97316; stroke-width: 2.2; stroke-dasharray: none; opacity: 1; }
    .car-dot { fill: var(--text); opacity: .4; }
    .car-dot.on { fill: #f97316; opacity: 1; }
    .car-pin { transition: transform .2s ease, background .2s, color .2s; }
    .car-pin.on { background: #101820; color: #fff; transform: translate(-50%, -50%) scale(1.18); border-color: #f97316; }
    .dark .car-pin.on { background: #fff; color: #101820; }
    .comp-card.on { border-color: #f97316; background: var(--brand-bg); box-shadow: 0 8px 24px rgba(249, 115, 22, .14); }
    .comp-card.on .comp-no { background: #f97316; color: #fff; }
</style>
@endsection

@section('content')

@php
    // ====== DATA BENGKEL (dari halaman sebelumnya) ======
    $wa = '6282181414113';
    $waLink = fn (string $pesan) => 'https://wa.me/' . $wa . '?text=' . rawurlencode($pesan);

    // ====== PROMO — CONTOH ISI, GANTI sesuai promo bengkel yang berlaku ======
    $promos = [
        [
            'badge' => 'Promo Servis',
            'icon' => 'fa-oil-can',
            'judul' => 'Paket Ganti Oli',
            'deskripsi' => 'Ganti oli plus pemeriksaan kondisi kendaraan oleh mekanik berpengalaman.',
            'kode' => 'OLI-BAS',
            'syarat' => 'Syarat & periode promo: konfirmasi ke bengkel.',
        ],
        [
            'badge' => 'Gratis',
            'icon' => 'fa-clipboard-check',
            'judul' => 'Cek Kondisi Kendaraan',
            'deskripsi' => 'Pemeriksaan titik-titik penting kendaraan sebelum Anda memutuskan servis.',
            'kode' => 'CEK-BAS',
            'syarat' => 'Syarat & periode promo: konfirmasi ke bengkel.',
        ],
        [
            'badge' => 'Pelanggan Baru',
            'icon' => 'fa-mobile-screen-button',
            'judul' => 'Akun Digital SIRAKA',
            'deskripsi' => 'Daftar akun dan lihat riwayat servis, nota, serta pengingat servis berkala kendaraan Anda.',
            'kode' => 'SIRAKA',
            'syarat' => 'Gratis. Akun terhubung setelah kendaraan Anda tercatat di bengkel.',
        ],
    ];

    // ====== KOMPONEN MOBIL (x,y = posisi titik pada gambar, dalam %) ======
    $komponen = [
        ['id' => 'radiator', 'no' => 1, 'sisi' => 'l', 'x' => 50, 'y' => 4,  'ikon' => 'fa-temperature-high', 'nama' => 'Radiator & Coolant',
         'ringkas' => 'Menjaga mesin tidak overheat',
         'interval' => 'Cek level tiap bulan · kuras ±40.000 km / 2 tahun',
         'kenapa' => 'Sistem pendingin menjaga suhu mesin tetap stabil. Coolant yang habis atau kotor bisa membuat mesin overheat dan merusak komponen mahal.',
         'tanda' => ['Jarum temperatur naik melewati batas normal', 'Ada uap atau bau manis dari area kap mesin', 'Coolant sering berkurang tanpa sebab jelas', 'Kipas radiator tidak menyala'],
         'saran' => 'Cek level coolant saat mesin dingin. Jangan membuka tutup radiator ketika mesin masih panas.'],
        ['id' => 'mesin', 'no' => 2, 'sisi' => 'r', 'x' => 50, 'y' => 18, 'ikon' => 'fa-oil-can', 'nama' => 'Mesin & Oli',
         'ringkas' => 'Pelumas utama jantung mobil',
         'interval' => 'Ganti ±5.000–10.000 km atau 6 bulan (sesuai jenis oli)',
         'kenapa' => 'Oli melumasi dan mendinginkan komponen mesin. Oli yang sudah lama akan mengental dan kehilangan kemampuan melindungi.',
         'tanda' => ['Lampu indikator oli menyala', 'Suara mesin terdengar kasar', 'Oli sangat hitam, kental, atau encer', 'Asap knalpot berlebihan'],
         'saran' => 'Gunakan oli sesuai spesifikasi buku manual dan cek batang pengukur oli setiap 2 minggu.'],
        ['id' => 'filter', 'no' => 3, 'sisi' => 'l', 'x' => 33, 'y' => 12, 'ikon' => 'fa-wind', 'nama' => 'Filter Udara',
         'ringkas' => 'Udara bersih untuk pembakaran',
         'interval' => 'Cek tiap 10.000 km · ganti ±20.000 km',
         'kenapa' => 'Filter yang tersumbat debu membuat suplai udara ke mesin berkurang sehingga tarikan berat dan konsumsi BBM naik.',
         'tanda' => ['Tarikan terasa berat', 'Konsumsi BBM lebih boros', 'Filter tampak sangat kotor/hitam', 'Suara hisapan udara berubah'],
         'saran' => 'Jika sering melewati jalan berdebu, periksa lebih sering dan ganti lebih cepat.'],
        ['id' => 'aki', 'no' => 4, 'sisi' => 'r', 'x' => 67, 'y' => 12, 'ikon' => 'fa-car-battery', 'nama' => 'Aki',
         'ringkas' => 'Sumber listrik untuk menyalakan mobil',
         'interval' => 'Cek tegangan tiap 6 bulan · umur rata-rata 2–3 tahun',
         'kenapa' => 'Aki yang lemah membuat mobil sulit hidup dan dapat mengganggu sistem kelistrikan lain.',
         'tanda' => ['Starter terdengar lemah atau berat', 'Lampu redup saat mesin dihidupkan', 'Terminal berkerak putih/hijau', 'Lampu indikator aki menyala'],
         'saran' => 'Bersihkan terminal secara berkala dan jangan membiarkan lampu atau audio menyala saat mesin mati.'],
        ['id' => 'rem', 'no' => 5, 'sisi' => 'l', 'x' => 12, 'y' => 25, 'ikon' => 'fa-circle-stop', 'nama' => 'Sistem Rem',
         'ringkas' => 'Komponen keselamatan nomor satu',
         'interval' => 'Cek kampas tiap 10.000 km · minyak rem ±2 tahun',
         'kenapa' => 'Kampas yang menipis dan minyak rem yang lama membuat jarak pengereman bertambah. Ini menyangkut keselamatan Anda.',
         'tanda' => ['Rem berdecit atau bunyi logam bergesekan', 'Pedal terasa dalam atau empuk', 'Setir bergetar saat mengerem', 'Mobil menarik ke satu sisi saat mengerem'],
         'saran' => 'Jangan menunda pemeriksaan rem jika muncul salah satu tanda di atas.'],
        ['id' => 'ac', 'no' => 6, 'sisi' => 'r', 'x' => 50, 'y' => 40, 'ikon' => 'fa-snowflake', 'nama' => 'AC & Filter Kabin',
         'ringkas' => 'Udara dingin dan bersih di kabin',
         'interval' => 'Filter kabin 10.000–15.000 km · cek AC tiap 6 bulan',
         'kenapa' => 'Filter kabin kotor dan evaporator berjamur membuat udara berbau dan dingin AC berkurang.',
         'tanda' => ['AC kurang dingin', 'Bau apek saat AC dinyalakan', 'Hembusan angin terasa lemah', 'Terdengar bunyi kasar dari kompresor'],
         'saran' => 'Nyalakan AC secara berkala meski cuaca sejuk agar komponen tetap terlumasi.'],
        ['id' => 'suspensi', 'no' => 7, 'sisi' => 'l', 'x' => 13, 'y' => 78, 'ikon' => 'fa-arrows-up-down', 'nama' => 'Suspensi & Kaki-kaki',
         'ringkas' => 'Kenyamanan dan kestabilan berkendara',
         'interval' => 'Cek tiap 10.000 km · shock absorber ±50.000–80.000 km',
         'kenapa' => 'Shock absorber dan komponen kaki-kaki yang aus membuat mobil limbung, tidak nyaman, dan mempercepat ban aus.',
         'tanda' => ['Bunyi menjeduk saat melewati jalan rusak', 'Mobil terasa melambung/goyang berlebihan', 'Setir tidak lurus', 'Ada rembesan oli pada shock absorber'],
         'saran' => 'Lakukan spooring dan balancing berkala atau setelah menghantam lubang yang dalam.'],
        ['id' => 'ban', 'no' => 8, 'sisi' => 'r', 'x' => 87, 'y' => 78, 'ikon' => 'fa-circle-notch', 'nama' => 'Ban',
         'ringkas' => 'Satu-satunya yang menyentuh jalan',
         'interval' => 'Cek tekanan tiap 2 minggu · rotasi ±10.000 km',
         'kenapa' => 'Tekanan dan kondisi ban memengaruhi cengkeraman, kenyamanan, dan keamanan, terutama saat hujan.',
         'tanda' => ['Telapak aus tidak rata atau sudah mendekati batas', 'Ada benjolan atau retak pada sisi ban', 'Setir atau bodi bergetar', 'Tekanan sering turun'],
         'saran' => 'Ikuti tekanan pada stiker di pintu mobil dan ukur saat ban dalam keadaan dingin.'],
        ['id' => 'lampu', 'no' => 9, 'sisi' => 'l', 'x' => 30, 'y' => 95, 'ikon' => 'fa-lightbulb', 'nama' => 'Lampu & Kelistrikan',
         'ringkas' => 'Agar terlihat dan terlihat oleh pengemudi lain',
         'interval' => 'Cek tiap bulan & sebelum perjalanan jauh',
         'kenapa' => 'Lampu rem, sein, dan lampu utama yang mati berbahaya bagi Anda dan pengemudi lain.',
         'tanda' => ['Lampu redup atau berkedip', 'Lampu sein berkedip lebih cepat dari biasanya', 'Sekering sering putus', 'Lampu rem tidak menyala'],
         'saran' => 'Minta bantuan orang lain untuk melihat lampu rem dan sein saat Anda menyalakannya.'],
    ];
    $kiri = collect($komponen)->where('sisi', 'l')->sortBy('y')->values();
    $kanan = collect($komponen)->where('sisi', 'r')->sortBy('y')->values();

    $layananUmum = [
        ['icon' => 'fa-oil-can', 'judul' => 'Perawatan Berkala & Ganti Oli', 'deskripsi' => 'Pemeriksaan rutin dan penggantian oli untuk membantu menjaga performa kendaraan.'],
        ['icon' => 'fa-gears', 'judul' => 'Pemeriksaan Mesin', 'deskripsi' => 'Pemeriksaan kondisi mesin dan identifikasi gejala gangguan pada kendaraan.'],
        ['icon' => 'fa-car-burst', 'judul' => 'Sistem Pengereman', 'deskripsi' => 'Pemeriksaan komponen rem untuk membantu menjaga keamanan saat berkendara.'],
        ['icon' => 'fa-car-side', 'judul' => 'Kaki-kaki & Suspensi', 'deskripsi' => 'Pemeriksaan bagian roda, suspensi, dan komponen kaki-kaki kendaraan.'],
        ['icon' => 'fa-fan', 'judul' => 'AC Kendaraan', 'deskripsi' => 'Pemeriksaan fungsi pendingin kabin dan komponen pendukung AC.'],
        ['icon' => 'fa-car-battery', 'judul' => 'Sistem Kelistrikan', 'deskripsi' => 'Pemeriksaan aki, lampu, dan komponen kelistrikan kendaraan.'],
    ];

    $tips = [
        ['icon' => 'fa-calendar-check', 'judul' => 'Cek ringan tiap minggu', 'isi' => 'Luangkan 5 menit untuk melihat level oli, tekanan ban, dan fungsi lampu sebelum berangkat.'],
        ['icon' => 'fa-triangle-exclamation', 'judul' => 'Jangan abaikan lampu indikator', 'isi' => 'Lampu oli, aki, atau mesin yang menyala adalah peringatan dini. Periksakan sebelum kerusakan membesar.'],
        ['icon' => 'fa-gas-pump', 'judul' => 'Jaga BBM tidak terlalu tipis', 'isi' => 'Biasakan mengisi sebelum tangki mendekati kosong agar pompa bahan bakar tidak bekerja berat.'],
        ['icon' => 'fa-clock-rotate-left', 'judul' => 'Catat riwayat servis', 'isi' => 'Dengan SIRAKA, setiap servis, nota, dan pengingat berkala kendaraan Anda tersimpan rapi dan bisa dibuka kapan saja.'],
    ];

    $tanda = [
        'Rem berdecit atau pedal terasa dalam',
        'Setir bergetar atau mobil menarik ke satu sisi',
        'Lampu indikator oli, aki, atau mesin menyala',
        'Temperatur naik atau keluar uap dari kap mesin',
        'Tercium bau terbakar atau bensin',
        'Mobil sulit dihidupkan atau starter lemah',
    ];

    $checklist = ['Oli mesin', 'Air radiator', 'Tekanan ban', 'Ban cadangan', 'Rem', 'Aki', 'Lampu & sein', 'Wiper & air wiper'];
@endphp

{{-- ================= NAVBAR ================= --}}
<header class="glass sticky top-0 z-50 border-b border-line">
    <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between gap-4 px-5 sm:px-8">
        <a href="#beranda" class="flex items-center gap-3" aria-label="Baba Auto Service - Beranda">
            <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-bas-ink">
                <img src="{{ asset('images/logo_bengkel.jpg') }}" alt="Logo SIRAKA"
                     class="h-full w-full object-contain p-1.5"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="hidden h-full w-full items-center justify-center text-brand-400">
                    <i class="fa-solid fa-screwdriver-wrench text-lg"></i>
                </span>
            </span>
            <span class="leading-tight">
                <span class="block text-[15px] font-extrabold tracking-tight text-main">SIRAKA</span>
                <span class="mt-0.5 block text-[10px] font-bold uppercase tracking-[.2em] text-muted">Baba Auto Servis</span>
            </span>
        </a>

        <nav class="hidden items-center gap-6 xl:gap-7 lg:flex" aria-label="Navigasi utama">
            <a href="#beranda" class="text-sm font-semibold text-muted transition hover:text-brandfg">Beranda</a>
            <a href="#layanan" class="text-sm font-semibold text-muted transition hover:text-brandfg">Layanan</a>
            <a href="#promo" class="text-sm font-semibold text-muted transition hover:text-brandfg">Promo</a>
            <a href="#perawatan" class="text-sm font-semibold text-muted transition hover:text-brandfg">Perawatan</a>
            <a href="#tips" class="text-sm font-semibold text-muted transition hover:text-brandfg">Tips</a>
            <a href="#lokasi" class="text-sm font-semibold text-muted transition hover:text-brandfg">Lokasi</a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <label class="theme-switch" title="Ganti tema tampilan">
                <input type="checkbox" class="js-theme-switch" onchange="toggleSirakaTheme()" aria-label="Ganti Light Mode dan Dark Mode">
                <span class="theme-slider"></span>
            </label>
            <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2.5 text-sm font-bold text-main transition hover:bg-surface2 lg:inline-flex">Masuk</a>
            <a href="{{ route('register') }}" class="hidden rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600 lg:inline-flex">
                Buat Akun <i class="fa-solid fa-arrow-right ml-2 mt-0.5 text-xs"></i>
            </a>
            <button id="menuToggle" type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-main lg:hidden"
                    aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobileMenu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <div id="mobileMenu" class="mobile-menu border-t border-line bg-surface px-5 py-4 shadow-lg lg:hidden" hidden>
        <nav class="mx-auto flex max-w-7xl flex-col gap-1" aria-label="Navigasi mobile">
            @foreach ([['#beranda','Beranda'],['#layanan','Layanan'],['#promo','Promo'],['#perawatan','Perawatan Mobil'],['#tips','Tips'],['#lokasi','Lokasi']] as $m)
                <a href="{{ $m[0] }}" class="mobile-link rounded-lg px-3 py-2.5 text-sm font-semibold text-main hover:bg-surface2">{{ $m[1] }}</a>
            @endforeach
            <div class="mt-3 grid grid-cols-2 gap-2 border-t border-line pt-4">
                <a href="{{ route('login') }}" class="rounded-xl border border-line px-4 py-3 text-center text-sm font-bold text-main">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-brand-500 px-4 py-3 text-center text-sm font-bold text-white">Registrasi</a>
            </div>
        </nav>
    </div>
</header>

<main>
    {{-- ================= HERO ================= --}}
    <section id="beranda" class="relative isolate overflow-hidden">
        <div class="hero-grid absolute inset-0 -z-10"></div>
        <div class="absolute -right-32 -top-32 -z-10 h-[460px] w-[460px] rounded-full bg-brand-500/20 blur-3xl"></div>

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.05fr_.95fr] lg:gap-10 lg:py-24">
            <div class="reveal">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-brandbg px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-brandfg">
                    <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                    Bengkel mobil • Pekanbaru
                </div>

                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight text-main sm:text-5xl lg:text-[3.6rem]">
                    Servis mobil jadi lebih
                    <span class="font-accent text-[1.12em] font-normal text-brand-500">tenang</span>,
                    riwayat selalu di genggaman.
                </h1>

                <p class="mt-6 max-w-xl text-sm leading-7 text-muted sm:text-base">
                    Selamat datang di <strong>Sistem Informasi Rekam Kendaraan dan Administrasi (SIRAKA) Baba Auto Service</strong>. Temukan layanan, promo, dan tips perawatan mobil, lalu pantau kendaraan dan riwayat servis Anda lewat SIRAKA.
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="#promo" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3.5 text-sm font-extrabold text-white shadow-xl shadow-brand-500/25 transition hover:-translate-y-0.5 hover:bg-brand-600">
                        <i class="fa-solid fa-tags"></i> Lihat Promo
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-line bg-surface px-6 py-3.5 text-sm font-bold text-main transition hover:border-brand-500/50 hover:text-brandfg">
                        <i class="fa-solid fa-right-to-bracket"></i> Login SIRAKA
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-line pt-6 text-xs font-semibold text-muted">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-location-dot text-brand-500"></i> Marpoyan Damai, Pekanbaru</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-clock text-brand-500"></i> Buka 24 jam</span>
                </div>
            </div>

            <div class="reveal relative mx-auto w-full max-w-[560px] lg:ml-auto">
                <div class="relative aspect-[5/4] overflow-hidden rounded-[1.75rem] border border-line bg-gradient-to-br from-brand-500 to-brand-700 shadow-soft">
                    <img src="{{ asset('images/bengkel.jpg') }}" alt="Bengkel Mobil Baba Auto Service"
                         class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none';">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <i class="fa-solid fa-car-side absolute left-1/2 top-1/2 -z-0 -translate-x-1/2 -translate-y-1/2 text-8xl text-white/20"></i>
                    <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-black/30 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                            <i class="fa-solid fa-wrench text-brand-400"></i> BABA AUTO SERVICE
                        </div>
                        <p class="mt-3 text-2xl font-extrabold text-white sm:text-3xl">Rawat kendaraan dengan lebih nyaman</p>
                    </div>
                </div>

                {{-- Kartu melayang --}}
                <div class="float-a absolute -left-3 top-8 hidden rounded-2xl border border-line bg-surface p-3.5 shadow-soft sm:block">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-okbg text-okfg"><i class="fa-solid fa-circle-check"></i></span>
                        <span><strong class="block text-sm font-extrabold text-main">Riwayat tercatat</strong><span class="text-[11px] text-muted">Servis & nota digital</span></span>
                    </div>
                </div>
                <div class="float-b absolute -bottom-5 -right-2 hidden rounded-2xl border border-line bg-surface p-3.5 shadow-soft sm:block">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brandbg text-brandfg"><i class="fa-solid fa-bell"></i></span>
                        <span><strong class="block text-sm font-extrabold text-main">Pengingat servis</strong><span class="text-[11px] text-muted">Tidak lagi terlewat</span></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= INFO CEPAT ================= --}}
    <section class="border-y border-line bg-surface">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-3 px-5 py-6 sm:grid-cols-2 sm:px-8 lg:grid-cols-4">
            @foreach ([
                ['fa-location-dot', 'Lokasi Pekanbaru', 'Marpoyan Damai, Riau'],
                ['fa-clock', 'Buka 24 Jam', 'Setiap hari*'],
                ['fa-mobile-screen-button', 'Portal SIRAKA', 'Akses riwayat servis'],
                ['fa-whatsapp', 'Respon via WhatsApp', '0821-8141-4113'],
            ] as $i)
                <div class="flex items-center gap-3 rounded-2xl bg-surface2 p-4">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brandbg text-brandfg"><i class="{{ $i[0] === 'fa-whatsapp' ? 'fa-brands' : 'fa-solid' }} {{ $i[0] }}"></i></span>
                    <span class="min-w-0"><strong class="block text-sm text-main">{{ $i[1] }}</strong><span class="text-xs text-muted">{{ $i[2] }}</span></span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= TENTANG ================= --}}
    <section id="tentang" class="scroll-mt-24 py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-2 lg:gap-16">
            <div class="reveal relative">
                <div class="relative aspect-[5/4] overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-surface2 to-line shadow-soft">
                    <img src="{{ asset('images/bengkel_2.jpg') }}" alt="Aktivitas di Baba Auto Service"
                         class="h-full w-full object-cover"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="absolute inset-0 hidden items-center justify-center text-soft" aria-hidden="true">
                        <i class="fa-solid fa-warehouse text-6xl opacity-50"></i>
                    </div>
                </div>
                <div class="absolute -bottom-5 right-5 rounded-2xl bg-bas-ink px-5 py-4 text-white shadow-xl sm:right-8">
                    <p class="text-xs font-semibold text-slate-300">Bengkel Mobil</p>
                    <p class="mt-1 text-lg font-extrabold">Baba Auto Service</p>
                </div>
            </div>

            <div class="reveal">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brandfg">Tentang Kami</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold leading-tight tracking-tight text-main sm:text-4xl">
                    Mengenal Baba Auto Service dan <span class="font-accent text-[1.12em] font-normal text-brand-500">SIRAKA</span>
                </h2>
                <p class="mt-5 text-sm leading-7 text-muted">
                    Baba Auto Service (BAS) merupakan bengkel mobil yang berlokasi di Kota Pekanbaru, Riau. Halaman ini menjadi pusat informasi bagi calon pelanggan untuk mengenal bengkel, melihat layanan dan promo, serta menemukan lokasi bengkel.
                </p>
                <p class="mt-4 text-sm leading-7 text-muted">
                    Melalui SIRAKA, pelanggan yang telah memiliki akun dapat mengakses informasi kendaraan dan riwayat servis yang tercatat pada sistem.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#lokasi" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-bas-ink px-5 py-3 text-sm font-bold text-white transition hover:bg-bas-dark dark:bg-white dark:text-bas-ink">
                        Lihat Lokasi <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-line bg-surface px-5 py-3 text-sm font-bold text-main transition hover:border-brand-500/50 hover:text-brandfg">
                        Buat Akun SIRAKA
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= LAYANAN ================= --}}
    <section id="layanan" class="scroll-mt-24 border-t border-line bg-surface py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brandfg">Informasi Layanan</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight text-main sm:text-4xl">
                    Perawatan kendaraan yang <span class="font-accent text-[1.12em] font-normal text-brand-500">perlu diperhatikan</span>
                </h2>
                <p class="mt-4 text-sm leading-7 text-muted">Berikut kategori layanan umum pada bengkel mobil sebagai informasi awal bagi pengunjung.</p>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($layananUmum as $layanan)
                    <article class="reveal group rounded-2xl border border-line bg-page p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-500/40 hover:shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brandbg text-xl text-brandfg transition group-hover:bg-brand-500 group-hover:text-white">
                            <i class="fa-solid {{ $layanan['icon'] }}"></i>
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-main">{{ $layanan['judul'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">{{ $layanan['deskripsi'] }}</p>
                    </article>
                @endforeach
            </div>

            <p class="mt-6 text-center text-xs leading-5 text-soft">
                Catatan: kategori di atas merupakan gambaran layanan umum. Silakan konfirmasi ketersediaan layanan kepada pihak bengkel.
            </p>
        </div>
    </section>

    {{-- ================= PROMO ================= --}}
    <section id="promo" class="scroll-mt-24 px-5 py-20 sm:px-8 sm:py-24">
        <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-500 via-brand-500 to-brand-700 px-5 py-12 text-white shadow-soft sm:px-10 lg:px-14 lg:py-16">
            <div class="pointer-events-none absolute -right-20 -top-28 h-80 w-80 rounded-full border-[40px] border-white/10"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="reveal relative max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-white/80">Promo Bengkel</p>
                <h2 class="mt-3 text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">
                    Servis hemat, <span class="font-accent text-[1.15em] font-normal">mobil makin sehat</span>
                </h2>
                <p class="mt-3 text-sm leading-7 text-white/85">Salin kode promo, lalu sebutkan saat menghubungi atau datang ke bengkel.</p>
            </div>

            <div class="relative mt-10 grid gap-5 md:grid-cols-3">
                @foreach ($promos as $p)
                    <article class="reveal flex h-full flex-col rounded-2xl bg-surface p-6 text-main shadow-xl shadow-black/10">
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid h-12 w-12 place-items-center rounded-xl bg-brandbg text-xl text-brandfg"><i class="fa-solid {{ $p['icon'] }}"></i></span>
                            <span class="rounded-full bg-brand-500 px-3 py-1 text-[11px] font-extrabold text-white">{{ $p['badge'] }}</span>
                        </div>
                        <h3 class="mt-5 text-lg font-extrabold">{{ $p['judul'] }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ $p['deskripsi'] }}</p>

                        <div class="mt-5 border-t border-dashed border-line pt-5">
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-surface2 px-4 py-2.5">
                                <span class="min-w-0">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-soft">Kode promo</span>
                                    <span class="block truncate font-mono text-sm font-extrabold tracking-wider text-brandfg">{{ $p['kode'] }}</span>
                                </span>
                                <button type="button" data-copy="{{ $p['kode'] }}" class="js-copy inline-flex min-h-9 shrink-0 items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-bold text-main transition hover:border-brand-500/50 hover:text-brandfg">
                                    <i class="fa-regular fa-copy"></i><span>Salin</span>
                                </button>
                            </div>
                            <p class="mt-3 text-[11px] leading-5 text-soft">{{ $p['syarat'] }}</p>
                            <a href="{{ $waLink('Halo Baba Auto Service, saya tertarik promo ' . $p['judul'] . ' (kode ' . $p['kode'] . ').') }}" target="_blank" rel="noopener noreferrer"
                               class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-bas-ink px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-bas-dark dark:bg-brand-500 dark:hover:bg-brand-600">
                                <i class="fa-brands fa-whatsapp text-base"></i> Klaim via WhatsApp
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= PERAWATAN MOBIL (INTERAKTIF) ================= --}}
    <section id="perawatan" class="scroll-mt-24 border-y border-line bg-surface py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brandfg">Panduan Perawatan</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight text-main sm:text-4xl">
                    Kenali bagian mobil yang <span class="font-accent text-[1.12em] font-normal text-brand-500">rutin dicek</span>
                </h2>
                <p class="mt-4 text-sm leading-7 text-muted">
                    Pilih titik pada mobil atau kartu di sekelilingnya untuk melihat interval perawatan, tanda-tanda masalah, dan saran dari bengkel.
                </p>
            </div>

            <div id="carStage" class="relative mt-14 grid grid-cols-1 items-stretch gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(250px,330px)_minmax(0,1fr)] lg:gap-16">
                {{-- Garis penunjuk (desktop) --}}
                <svg id="carLines" class="pointer-events-none absolute inset-0 z-[1] hidden h-full w-full lg:block" aria-hidden="true"></svg>

                {{-- Kartu kiri --}}
                <div class="relative z-[2] grid grid-cols-2 gap-3 lg:flex lg:flex-col lg:justify-between lg:gap-3">
                    @foreach ($kiri as $k)
                        <button type="button" data-id="{{ $k['id'] }}" class="comp-card group flex w-full items-center gap-3 rounded-2xl border border-line bg-page p-3.5 text-left transition hover:border-brand-500/50">
                            <span class="comp-no grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brandbg text-sm font-extrabold text-brandfg transition">{{ $k['no'] }}</span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-extrabold leading-tight text-main">{{ $k['nama'] }}</span>
                                <span class="mt-0.5 hidden text-[11px] leading-4 text-muted xl:block">{{ $k['ringkas'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Mobil --}}
                <div class="order-first lg:order-none">
                    <div class="relative mx-auto aspect-[415/894] w-full max-w-[17rem] sm:max-w-[19rem] lg:max-w-none">
                        <img src="{{ asset('images/mobil-atas.png') }}" alt="Mobil tampak atas dengan titik komponen perawatan"
                             width="415" height="894" loading="lazy" decoding="async" draggable="false"
                             class="absolute inset-0 h-full w-full select-none object-contain drop-shadow-[0_24px_28px_rgba(0,0,0,.28)]">

                        @foreach ($komponen as $k)
                            <button type="button" data-id="{{ $k['id'] }}" aria-label="{{ $k['nama'] }}"
                                    class="car-pin absolute z-[3] grid h-8 w-8 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-brand-500 bg-white text-xs font-extrabold text-brand-700 shadow-lg shadow-black/20 hover:scale-110"
                                    style="left: {{ $k['x'] }}%; top: {{ $k['y'] }}%;">
                                <span class="pin-ring pointer-events-none absolute inset-0 hidden rounded-full border-2 border-brand-500"></span>
                                {{ $k['no'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Kartu kanan --}}
                <div class="relative z-[2] grid grid-cols-2 gap-3 lg:flex lg:flex-col lg:justify-between lg:gap-3">
                    @foreach ($kanan as $k)
                        <button type="button" data-id="{{ $k['id'] }}" class="comp-card group flex w-full items-center gap-3 rounded-2xl border border-line bg-page p-3.5 text-left transition hover:border-brand-500/50">
                            <span class="comp-no grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brandbg text-sm font-extrabold text-brandfg transition">{{ $k['no'] }}</span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-extrabold leading-tight text-main">{{ $k['nama'] }}</span>
                                <span class="mt-0.5 hidden text-[11px] leading-4 text-muted xl:block">{{ $k['ringkas'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Panel detail --}}
            <div id="compDetail" class="mt-16 overflow-hidden rounded-3xl border border-line bg-page shadow-soft" aria-live="polite">
                <div class="grid lg:grid-cols-[.8fr_1.2fr]">
                    <div class="bg-gradient-to-br from-brand-500 to-brand-700 p-6 text-white sm:p-8">
                        <span id="dIcon" class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20 text-2xl"><i class="fa-solid fa-oil-can"></i></span>
                        <p class="mt-5 text-xs font-bold uppercase tracking-wider text-white/80">Komponen <span id="dNo">1</span> dari {{ count($komponen) }}</p>
                        <h3 id="dNama" class="mt-1 text-2xl font-extrabold sm:text-3xl"></h3>
                        <div class="mt-5 rounded-2xl bg-white/15 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-white/80"><i class="fa-regular fa-clock mr-1.5"></i>Interval perawatan</p>
                            <p id="dInterval" class="mt-1 text-sm font-bold leading-6"></p>
                        </div>
                        <a id="dWa" href="#" target="_blank" rel="noopener noreferrer"
                           class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-brand-700 transition hover:bg-brand-50">
                            <i class="fa-brands fa-whatsapp text-base"></i> Jadwalkan pemeriksaan
                        </a>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-brandfg">Kenapa penting?</p>
                            <p id="dKenapa" class="mt-2 text-sm leading-7 text-main"></p>
                        </div>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-warnfg"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Tanda perlu dicek</p>
                            <ul id="dTanda" class="mt-3 space-y-2.5 text-sm leading-6 text-main"></ul>
                        </div>
                        <div class="self-start rounded-2xl border border-okfg/20 bg-okbg p-4">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-okfg"><i class="fa-solid fa-lightbulb mr-1.5"></i>Saran bengkel</p>
                            <p id="dSaran" class="mt-2 text-sm leading-6 text-main"></p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-5 text-center text-xs leading-5 text-soft">
                Interval di atas bersifat umum. Selalu ikuti buku manual kendaraan Anda dan saran mekanik sesuai kondisi pemakaian.
            </p>
        </div>
    </section>

    {{-- ================= TIPS ================= --}}
    <section id="tips" class="scroll-mt-24 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="reveal max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brandfg">Tips dari Bengkel</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight text-main sm:text-4xl">
                    Kebiasaan kecil, <span class="font-accent text-[1.12em] font-normal text-brand-500">mobil awet</span>
                </h2>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-[1.25fr_.75fr]">
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($tips as $t)
                        <article class="reveal rounded-2xl border border-line bg-surface p-6 transition hover:-translate-y-1 hover:shadow-soft">
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-brandbg text-lg text-brandfg"><i class="fa-solid {{ $t['icon'] }}"></i></span>
                            <h3 class="mt-4 text-base font-extrabold text-main">{{ $t['judul'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-muted">{{ $t['isi'] }}</p>
                        </article>
                    @endforeach
                </div>

                <aside class="reveal relative overflow-hidden rounded-2xl bg-bas-ink p-6 text-white sm:p-7">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-500/20 blur-3xl"></div>
                    <p class="relative text-xs font-extrabold uppercase tracking-[.18em] text-brand-400">Waspadai</p>
                    <h3 class="relative mt-2 text-xl font-extrabold">Tanda mobil perlu segera diperiksa</h3>
                    <ul class="relative mt-5 space-y-3">
                        @foreach ($tanda as $x)
                            <li class="flex items-start gap-3 text-sm leading-6 text-slate-200">
                                <span class="mt-1 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand-500 text-[10px] text-white"><i class="fa-solid fa-exclamation"></i></span>
                                {{ $x }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $waLink('Halo Baba Auto Service, mobil saya mengalami gejala dan ingin diperiksa.') }}" target="_blank" rel="noopener noreferrer"
                       class="relative mt-6 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-brand-600">
                        <i class="fa-brands fa-whatsapp text-base"></i> Konsultasi via WhatsApp
                    </a>
                </aside>
            </div>

            <div class="reveal mt-6 rounded-2xl border border-line bg-surface p-6 sm:p-7">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-extrabold text-main"><i class="fa-solid fa-route mr-2 text-brand-500"></i>Checklist sebelum perjalanan jauh</h3>
                    <p class="text-xs text-muted">Pastikan semuanya aman sebelum berangkat.</p>
                </div>
                <div class="mt-4 flex flex-wrap gap-2.5">
                    @foreach ($checklist as $c)
                        <span class="inline-flex items-center gap-2 rounded-full bg-okbg px-3.5 py-2 text-xs font-bold text-okfg"><i class="fa-solid fa-circle-check"></i>{{ $c }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ================= KEUNGGULAN DIGITAL ================= --}}
    <section id="keunggulan" class="scroll-mt-24 bg-bas-ink py-20 text-white sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-5 sm:px-8 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
            <div class="reveal">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brand-400">Layanan Digital</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">
                    Kenali riwayat kendaraan melalui <span class="font-accent text-[1.12em] font-normal text-brand-400">SIRAKA</span>
                </h2>
                <p class="mt-5 text-sm leading-7 text-slate-300">
                    SIRAKA membantu pelanggan mengakses informasi kendaraan dan catatan servis yang telah terhubung dengan akun pelanggan.
                </p>

                <ol class="mt-8 space-y-4">
                    @foreach ([['Servis di bengkel', 'Mekanik menangani kendaraan Anda.'], ['Tercatat otomatis', 'Diagnosa, tindakan, dan nota disimpan di sistem.'], ['Pantau dari mana saja', 'Buka portal pelanggan lewat HP atau laptop.']] as $i => $s)
                        <li class="flex items-start gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-500 text-sm font-extrabold">{{ $i + 1 }}</span>
                            <span><strong class="block text-sm font-extrabold">{{ $s[0] }}</strong><span class="text-sm text-slate-400">{{ $s[1] }}</span></span>
                        </li>
                    @endforeach
                </ol>

                <a href="{{ route('register') }}" class="mt-9 inline-flex min-h-12 items-center gap-2 rounded-xl bg-brand-500 px-6 py-3.5 text-sm font-extrabold text-white transition hover:bg-brand-600">
                    Mulai Registrasi <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['fa-car', 'Informasi Kendaraan', 'Lihat kendaraan yang telah terhubung dengan akun pelanggan.'],
                    ['fa-clock-rotate-left', 'Riwayat Servis', 'Akses catatan servis kendaraan yang tersedia pada sistem.'],
                    ['fa-file-invoice', 'Informasi Nota', 'Periksa informasi nota servis yang telah tercatat pada akun.'],
                    ['fa-shield-halved', 'Akses Akun', 'Masuk menggunakan akun pelanggan untuk melihat informasi pribadi.'],
                ] as $f)
                    <div class="reveal rounded-2xl border border-white/10 bg-white/[.06] p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500/15 text-brand-400"><i class="fa-solid {{ $f[0] }} text-lg"></i></span>
                        <h3 class="mt-5 font-extrabold">{{ $f[1] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $f[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= LOKASI ================= --}}
    <section id="lokasi" class="scroll-mt-24 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="reveal mb-10 flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div class="max-w-2xl">
                    <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brandfg">Temukan Kami</p>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-main sm:text-4xl">Lokasi Baba Auto Service</h2>
                    <p class="mt-3 text-sm leading-7 text-muted">Kunjungi bengkel kami di kawasan Marpoyan Damai, Kota Pekanbaru.</p>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&amp;destination=Bengkel%20Mobil%20Baba%20Auto%20Service%20(BAS)%2C%20Jl.%20Gulama%20Gg.%20Sikumbang%20No.6C%2C%20Pekanbaru"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600">
                    <i class="fa-solid fa-diamond-turn-right"></i> Petunjuk Arah
                </a>
            </div>

            <div class="reveal grid overflow-hidden rounded-[1.75rem] border border-line bg-surface shadow-soft lg:grid-cols-[.78fr_1.22fr]">
                <div class="p-6 sm:p-8 lg:p-9">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brandbg text-xl text-brandfg"><i class="fa-solid fa-location-dot"></i></div>
                    <h3 class="mt-5 text-xl font-extrabold text-main">Bengkel Mobil Baba Auto Service (BAS)</h3>
                    <div class="mt-6 space-y-5">
                        <div class="flex gap-3">
                            <i class="fa-solid fa-map-pin mt-1 w-4 text-brand-500"></i>
                            <p class="text-sm leading-6 text-muted">Jl. Gulama Gg. Sikumbang No. 6C, Tengkerang Barat, Marpoyan Damai, Kota Pekanbaru, Riau 28282</p>
                        </div>
                        <div class="flex gap-3">
                            <i class="fa-solid fa-phone mt-1 w-4 text-brand-500"></i>
                            <a href="tel:+6282181414113" class="text-sm font-semibold text-main hover:text-brandfg">0821-8141-4113</a>
                        </div>
                        <div class="flex gap-3">
                            <i class="fa-solid fa-clock mt-1 w-4 text-brand-500"></i>
                            <p class="text-sm text-muted">Setiap hari, 24 jam</p>
                        </div>
                    </div>
                    <a href="https://wa.me/6282181414113" target="_blank" rel="noopener noreferrer"
                       class="mt-8 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-okbg px-5 py-3 text-sm font-extrabold text-okfg transition hover:opacity-90">
                        <i class="fa-brands fa-whatsapp text-lg"></i> Hubungi melalui WhatsApp
                    </a>
                </div>

                <div class="min-h-[330px] bg-surface2 lg:min-h-[460px]">
                    <iframe
                        class="map-frame h-full min-h-[330px] w-full lg:min-h-[460px]"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.6673498727923!2d101.42681410000002!3d0.4984348999999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5a984257b17e3%3A0x64c16426c98595a2!2sBengkel%20Mobil%20Baba%20Auto%20Service%20(BAS)!5e0!3m2!1sen!2sid!4v1793672872433!5m2!1sen!2sid"
                        title="Peta lokasi Bengkel Mobil Baba Auto Service"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    <section class="px-5 pb-20 sm:px-8 sm:pb-24">
        <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-brand-500 to-brand-700 px-6 py-10 text-white shadow-soft sm:px-10 lg:px-14 lg:py-12">
            <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full border-[38px] border-white/10"></div>
            <div class="relative flex flex-col justify-between gap-8 lg:flex-row lg:items-center">
                <div class="max-w-2xl">
                    <p class="text-xs font-extrabold uppercase tracking-[.2em] text-orange-100">Mulai Sekarang</p>
                    <h2 class="mt-3 text-balance text-2xl font-extrabold leading-tight sm:text-3xl">Akses informasi kendaraan Anda melalui SIRAKA.</h2>
                    <p class="mt-3 text-sm leading-6 text-orange-50">Buat akun pelanggan untuk mengakses kendaraan dan riwayat servis yang terhubung.</p>
                </div>
                <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-white px-5 py-3.5 text-sm font-extrabold text-brand-700 transition hover:bg-brand-50">Registrasi Akun <i class="fa-solid fa-arrow-right text-xs"></i></a>
                    <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/40 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Login</a>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- ================= FOOTER ================= --}}
<footer class="bg-bas-ink text-slate-300">
    <div class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 md:grid-cols-[1.2fr_.8fr_.8fr]">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-brand-400"><i class="fa-solid fa-screwdriver-wrench text-lg"></i></span>
                <span><strong class="block text-sm font-extrabold tracking-wide text-white">BABA AUTO SERVICE</strong><span class="text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">SIRAKA</span></span>
            </div>
            <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">Informasi Bengkel Mobil Baba Auto Service dan portal digital SIRAKA untuk pelanggan.</p>
        </div>

        <div>
            <h3 class="text-sm font-extrabold text-white">Navigasi</h3>
            <div class="mt-4 flex flex-col gap-3 text-sm text-slate-400">
                <a href="#layanan" class="transition hover:text-brand-400">Layanan</a>
                <a href="#promo" class="transition hover:text-brand-400">Promo</a>
                <a href="#perawatan" class="transition hover:text-brand-400">Panduan Perawatan</a>
                <a href="#tips" class="transition hover:text-brand-400">Tips</a>
                <a href="#lokasi" class="transition hover:text-brand-400">Lokasi</a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-extrabold text-white">Hubungi Kami</h3>
            <div class="mt-4 flex flex-col gap-3 text-sm text-slate-400">
                <a href="tel:+6282181414113" class="hover:text-brand-400"><i class="fa-solid fa-phone mr-2"></i>0821-8141-4113</a>
                <a href="https://www.instagram.com/babaautoservice/" target="_blank" rel="noopener noreferrer" class="hover:text-brand-400"><i class="fa-brands fa-instagram mr-2"></i>Instagram</a>
                <a href="https://www.tiktok.com/@baba.auto.service" target="_blank" rel="noopener noreferrer" class="hover:text-brand-400"><i class="fa-brands fa-tiktok mr-2"></i>TikTok</a>
                <a href="https://wa.me/6282181414113" target="_blank" rel="noopener noreferrer" class="hover:text-brand-400"><i class="fa-brands fa-whatsapp mr-2"></i>WhatsApp</a>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>© {{ date('Y') }} Baba Auto Service. Seluruh hak cipta dilindungi.</p>
            <p>Didukung oleh <span class="font-bold text-slate-300">SIRAKA</span> — Sistem Informasi Riwayat dan Kendaraan.</p>
        </div>
    </div>
</footer>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Menu mobile ---------- */
    var toggle = document.getElementById('menuToggle');
    var menu = document.getElementById('mobileMenu');
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            var open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!open));
            menu.hidden = open;
            toggle.innerHTML = open ? '<i class="fa-solid fa-bars"></i>' : '<i class="fa-solid fa-xmark"></i>';
        });
        menu.querySelectorAll('.mobile-link').forEach(function (a) {
            a.addEventListener('click', function () {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
            });
        });
    }

    /* ---------- Salin kode promo ---------- */
    document.querySelectorAll('.js-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var kode = btn.dataset.copy;
            var label = btn.querySelector('span');
            var selesai = function () {
                label.textContent = 'Tersalin';
                setTimeout(function () { label.textContent = 'Salin'; }, 1800);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(kode).then(selesai, selesai);
            } else {
                var t = document.createElement('textarea');
                t.value = kode; document.body.appendChild(t); t.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(t); selesai();
            }
        });
    });

    /* ---------- Mobil interaktif ---------- */
    var data = @json($komponen);
    var stage = document.getElementById('carStage');
    var svg = document.getElementById('carLines');
    var mq = window.matchMedia('(min-width: 1024px)');
    var pins = {}, cards = {}, aktif = null;

    stage.querySelectorAll('.car-pin').forEach(function (el) { pins[el.dataset.id] = el; });
    stage.querySelectorAll('.comp-card').forEach(function (el) { cards[el.dataset.id] = el; });

    function drawLines() {
        if (!mq.matches) { svg.innerHTML = ''; return; }
        var sr = stage.getBoundingClientRect();
        svg.setAttribute('viewBox', '0 0 ' + sr.width + ' ' + sr.height);
        var out = '';
        data.forEach(function (d) {
            var cr = cards[d.id].getBoundingClientRect();
            var pr = pins[d.id].getBoundingClientRect();
            var kiri = d.sisi === 'l';
            var x1 = (kiri ? cr.right : cr.left) - sr.left;
            var y1 = cr.top + cr.height / 2 - sr.top;
            var x2 = pr.left + pr.width / 2 - sr.left;
            var y2 = pr.top + pr.height / 2 - sr.top;
            var dx = (x2 - x1) * 0.55;
            var on = d.id === aktif ? ' on' : '';
            out += '<path class="car-line' + on + '" d="M' + x1 + ' ' + y1 + ' C' + (x1 + dx) + ' ' + y1 + ', ' + (x2 - dx) + ' ' + y2 + ', ' + x2 + ' ' + y2 + '"/>';
            out += '<circle class="car-dot' + on + '" cx="' + x1 + '" cy="' + y1 + '" r="3.5"/>';
        });
        svg.innerHTML = out;
    }

    function pilih(id) {
        var d = data.find(function (x) { return x.id === id; });
        if (!d) return;
        aktif = id;

        Object.keys(pins).forEach(function (k) {
            var on = k === id;
            pins[k].classList.toggle('on', on);
            pins[k].querySelector('.pin-ring').classList.toggle('hidden', !on);
            cards[k].classList.toggle('on', on);
        });

        document.getElementById('dIcon').innerHTML = '<i class="fa-solid ' + d.ikon + '"></i>';
        document.getElementById('dNo').textContent = d.no;
        document.getElementById('dNama').textContent = d.nama;
        document.getElementById('dInterval').textContent = d.interval;
        document.getElementById('dKenapa').textContent = d.kenapa;
        document.getElementById('dSaran').textContent = d.saran;

        var ul = document.getElementById('dTanda');
        ul.innerHTML = '';
        d.tanda.forEach(function (t) {
            var li = document.createElement('li');
            li.className = 'flex items-start gap-2.5';
            li.innerHTML = '<i class="fa-solid fa-circle-exclamation mt-1.5 text-xs text-brand-500"></i><span></span>';
            li.lastChild.textContent = t;
            ul.appendChild(li);
        });

        var pesan = 'Halo Baba Auto Service, saya ingin memeriksakan ' + d.nama + ' pada kendaraan saya.';
        document.getElementById('dWa').href = 'https://wa.me/6282181414113?text=' + encodeURIComponent(pesan);

        drawLines();
    }

    stage.addEventListener('click', function (e) {
        var el = e.target.closest('[data-id]');
        if (el) pilih(el.dataset.id);
    });
    stage.addEventListener('mouseover', function (e) {
        var el = e.target.closest('.car-pin, .comp-card');
        if (el && mq.matches && el.dataset.id !== aktif) pilih(el.dataset.id);
    });

    window.addEventListener('resize', drawLines);
    window.addEventListener('load', drawLines);
    mq.addEventListener ? mq.addEventListener('change', drawLines) : mq.addListener(drawLines);

    pilih(data[0].id);
});
</script>
@endsection