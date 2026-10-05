<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIRAKA</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#f97316',
                            600: '#ea580c',
                        },
                        ink: {
                            950: '#0a0a0a',
                            900: '#141414',
                            800: '#1f1f1f',
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
    </style>
</head>
<body class="bg-ink-950 text-neutral-200 min-h-screen flex flex-col items-center justify-center p-4 relative">

    <!-- POP-UP MODAL BERHASIL MENDAFTAR (Muncul otomatis jika ada session success) -->
    @if(session('success'))
        <div id="successModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
            <div class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 shadow-2xl relative">
                <!-- Icon Sukses -->
                <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                
                <div class="space-y-1">
                    <h3 class="text-base font-black text-white">Pendaftaran Berhasil!</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">{{ session('success') }}</p>
                </div>

                <!-- Tombol Tutup Pop-up -->
                <button onclick="closeModal()" class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-500/20 transition">
                    Masuk Sekarang
                </button>
            </div>
        </div>
    @endif

    <!-- Container Utama -->
    <div class="w-full max-w-md space-y-6">
        
        <!-- Header / Logo di Atas -->
        <div class="text-center space-y-3">
            <div class="inline-flex p-2.5 bg-black border border-neutral-800 rounded-3xl shadow-xl">
                <img src="{{ asset('images/logo-siraka.png') }}" alt="Logo SIRAKA" class="w-14 h-14 object-contain">
            </div>
            <div>
                <h1 class="text-xl font-black tracking-wider text-white">SIRAKA</h1>
                <p class="text-[11px] text-neutral-400">Sistem Informasi Rekam Kendaraan & Administrasi</p>
                <p class="text-[11px] text-brand-500 font-bold tracking-widest mt-0.5">BABA AUTO SERVICE</p>
            </div>
        </div>

        <!-- Error Alert -->
        @if($errors->any())
            <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-400 text-xs font-semibold text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Card Form Login -->
        <div class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div>
                <h2 class="text-base font-extrabold text-white">Selamat Datang</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Masuk untuk mengakses rekam medis kendaraan & layanan.</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Input Username / No HP -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-neutral-300">Username atau Nomor HP</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-neutral-500 text-xs">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" name="no_hp" required autofocus
                            class="w-full py-2.5 pl-10 pr-4 bg-ink-800 border border-neutral-700 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            placeholder="Contoh: admin / 081234567890">
                    </div>
                </div>

                <!-- Input Password -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-neutral-300">Kata Sandi</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-neutral-500 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" required
                            class="w-full py-2.5 pl-10 pr-10 bg-ink-800 border border-neutral-700 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            placeholder="Masukkan kata sandi">
                        <button type="button" onclick="togglePassword()" class="absolute right-3.5 text-neutral-500 hover:text-white text-xs">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Ingat Saya -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-neutral-400">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-ink-800 border-neutral-700 text-brand-500 focus:ring-brand-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Tombol Masuk -->
                <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-brand-500/25 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Link Daftar -->
            <div class="text-center text-xs text-neutral-400 pt-2 border-t border-neutral-800">
                Belum punya akun? <a href="{{ route('register') }}" class="text-brand-500 font-bold hover:underline">Daftar di sini</a>
            </div>
        </div>

        <!-- Footer Copyright -->
        <div class="text-center text-[11px] text-neutral-500">
            SIRAKA &copy; {{ date('Y') }} Baba Auto Service &bull; Kelompok 3 PPSI
        </div>

    </div>

    <!-- Script Toggle Password & Close Modal -->
    <script>
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
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>