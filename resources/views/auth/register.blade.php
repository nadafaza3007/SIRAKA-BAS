<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SIRAKA</title>
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
<body class="bg-ink-950 text-neutral-200 min-h-screen flex flex-col items-center justify-center p-4">

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

        <!-- Card Form Register -->
        <div class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div>
                <h2 class="text-base font-extrabold text-white">Pendaftaran Akun Baru</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Daftar sebagai pelanggan untuk memantau rekam medis kendaraan.</p>
            </div>

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-neutral-300">Nama Lengkap</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-neutral-500 text-xs">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required autofocus
                            class="w-full py-2.5 pl-10 pr-4 bg-ink-800 border border-neutral-700 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            placeholder="Masukkan nama lengkap">
                    </div>
                </div>

                <!-- Nomor HP -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-neutral-300">Nomor HP (WhatsApp)</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-neutral-500 text-xs">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" required
                            class="w-full py-2.5 pl-10 pr-4 bg-ink-800 border border-neutral-700 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            placeholder="Contoh: 081234567890">
                    </div>
                </div>

                <!-- Kata Sandi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-neutral-300">Kata Sandi</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-neutral-500 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" required
                            class="w-full py-2.5 pl-10 pr-10 bg-ink-800 border border-neutral-700 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePassword('passwordInput', 'toggleIcon1')" class="absolute right-3.5 text-neutral-500 hover:text-white text-xs">
                            <i class="fa-solid fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Kata Sandi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-neutral-300">Konfirmasi Kata Sandi</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-neutral-500 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password_confirmation" id="passwordConfirmationInput" required
                            class="w-full py-2.5 pl-10 pr-10 bg-ink-800 border border-neutral-700 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            placeholder="Ulangi kata sandi">
                        <button type="button" onclick="togglePassword('passwordConfirmationInput', 'toggleIcon2')" class="absolute right-3.5 text-neutral-500 hover:text-white text-xs">
                            <i class="fa-solid fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Daftar -->
                <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-brand-500/25 transition flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Daftar Sekarang</span>
                </button>
            </form>

            <!-- Link Kembali ke Login -->
            <div class="text-center text-xs text-neutral-400 pt-2 border-t border-neutral-800">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-brand-500 font-bold hover:underline">Masuk di sini</a>
            </div>
        </div>

        <!-- Footer Copyright -->
        <div class="text-center text-[11px] text-neutral-500">
            SIRAKA &copy; {{ date('Y') }} Baba Auto Service &bull; Kelompok 3 PPSI
        </div>

    </div>

    <!-- Script Toggle Password -->
    <script>
        function togglePassword(fieldId, iconId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
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
    </script>
</body>
</html>