<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Konsumen;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username atau Nomor HP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->username);
        $user = User::where('username', $loginInput)
            ->orWhere('no_hp', $loginInput)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withInput()->withErrors([
                'login' => 'Username atau password salah.',
            ]);
        }

        if ($user->status !== 'aktif') {
            return back()->withInput()->withErrors([
                'login' => 'Akun Anda dinonaktifkan, silakan hubungi bengkel/Admin.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditLog::catat($user->name, 'Autentikasi', 'Login ke sistem (' . strtoupper($user->role) . ')', null, null, $user->id);

        return $this->redirectByRole($user);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|min:9|max:20|unique:users,no_hp',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'no_hp.unique' => 'Nomor HP sudah terdaftar. Silakan login atau gunakan fitur lupa password.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $noHp = preg_replace('/[^0-9]/', '', $request->no_hp);
        if (str_starts_with($noHp, '62')) {
            $noHp = '0' . substr($noHp, 2);
        }

        // Cek apakah No HP sudah ada di data Konsumen bengkel
        $konsumen = Konsumen::where('no_hp', $noHp)
            ->orWhere('no_hp', $request->no_hp)
            ->first();

        if (!$konsumen) {
            // Jika belum ada di master konsumen, buat data konsumen baru
            $konsumen = Konsumen::create([
                'nama_lengkap' => $request->nama_lengkap,
                'no_hp' => $request->no_hp,
                'status' => 'aktif',
            ]);
        }

        // Buat username unik jika tidak ditentukan
        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $request->nama_lengkap)[0]));
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = User::create([
            'name' => $request->nama_lengkap,
            'username' => $username,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'status' => 'aktif',
            'konsumen_id' => $konsumen->id,
        ]);

        AuditLog::catat($user->name, 'Registrasi Akun', 'Pendaftaran akun pelanggan baru', null, [
            'username' => $username,
            'no_hp' => $request->no_hp,
            'konsumen_id' => $konsumen->id
        ], $user->id);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Gunakan Username "' . $username . '" atau Nomor HP untuk masuk.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::catat(Auth::user()->name, 'Autentikasi', 'Logout dari sistem', null, null, Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    private function redirectByRole($user)
    {
        if ($user->isPelanggan()) {
            return redirect()->route('pelanggan.rekam-servis');
        }
        return redirect()->route('admin.dashboard');
    }
}

