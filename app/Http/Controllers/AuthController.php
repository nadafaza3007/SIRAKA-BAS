<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
    return view('auth.login');
    }

    public function login(Request $request)
    {
    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */
    $request->validate([
        'no_hp' => [
            'required',
            'string',
        ],
        'password' => [
            'required',
            'string',
        ],
    ], [
        'no_hp.required'    => 'Username atau nomor HP wajib diisi.',
        'password.required' => 'Kata sandi wajib diisi.',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Ambil input
    |--------------------------------------------------------------------------
    |
    | Field form tetap bernama no_hp, tetapi nilainya boleh:
    |
    | - username
    | - nomor HP
    |
    */
    $loginInput = trim($request->input('no_hp'));
    $password   = $request->input('password');
    $remember   = $request->boolean('remember');

    /*
    |--------------------------------------------------------------------------
    | Percobaan 1: login menggunakan nomor HP
    |--------------------------------------------------------------------------
    */
    $berhasil = Auth::attempt(
        [
            'no_hp'    => $loginInput,
            'password' => $password,
        ],
        $remember
    );

    /*
    |--------------------------------------------------------------------------
    | Percobaan 2: login menggunakan username
    |--------------------------------------------------------------------------
    */
    if (! $berhasil) {
        $berhasil = Auth::attempt(
            [
                'username' => strtolower($loginInput),
                'password' => $password,
            ],
            $remember
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Login berhasil
    |--------------------------------------------------------------------------
    */
    if ($berhasil) {
        $request->session()->regenerate();
        return $this->redirectByRole();
    }

    /*
    |--------------------------------------------------------------------------
    | Login gagal
    |--------------------------------------------------------------------------
    */
    return back()
        ->withInput($request->only('no_hp'))
        ->withErrors([
            'no_hp' => 'Username / Nomor HP atau kata sandi salah.',
        ]);
    }

    public function showRegister()
    {
    return view('auth.register');
    }

    public function checkUsername(Request $request)
    {
    $username = strtolower(trim((string) $request->query('username')));

    if ($username === '') {
        return response()->json([
            'available' => false,
            'message'   => 'Username wajib diisi.',
        ]);
    }

    if (strlen($username) < 3) {
        return response()->json([
            'available' => false,
            'message'   => 'Username minimal 3 karakter.',
        ]);
    }

    if (strlen($username) > 30) {
        return response()->json([
            'available' => false,
            'message'   => 'Username maksimal 30 karakter.',
        ]);
    }

        if (! preg_match('/^[a-z0-9_]+$/', $username)) {
        return response()->json([
            'available' => false,
            'message' => 'Username hanya boleh berisi huruf, angka, dan underscore (_).',
        ]);
    }

    $usernameSudahAda = User::whereRaw('LOWER(username) = ?', [$username])->exists();

    if ($usernameSudahAda) {
        return response()->json([
            'available' => false,
            'message'   => 'Username sudah digunakan.',
        ]);
    }

    return response()->json([
        'available' => true,
        'message'   => 'Username tersedia.',
    ]);
    }

    public function register(Request $request)
    {
    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */
    $request->validate([
        'nama_lengkap' => [
            'required',
            'string',
            'max:255',
        ],
        'username' => [
            'required',
            'string',
            'min:3',
            'max:30',
            'alpha_dash',
            'unique:users,username',
        ],
        'no_hp' => [
            'required',
            'string',
            'regex:/^(?:08[0-9]{8,11}|(?:\+?62)8[0-9]{8,11})$/',
        ],
        'password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
        ],
    ], [
        'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
        'username.required'     => 'Username wajib diisi.',
        'username.min'          => 'Username minimal 3 karakter.',
        'username.max'          => 'Username maksimal 30 karakter.',
        'username.max'          => 'Username maksimal 30 karakter.',
        'username.alpha_dash'   => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan underscore.',
        'username.unique'       => 'Username sudah digunakan. Silakan gunakan username lain.',
        'no_hp.required'        => 'Nomor HP wajib diisi.',
        'no_hp.regex'           => 'Masukkan nomor HP yang valid.',
        'no_hp.unique'          => 'Nomor HP sudah terdaftar. Silakan masuk menggunakan akun tersebut.',
        'password.required'     => 'Kata sandi wajib diisi.',
        'password.min'          => 'Kata sandi minimal 8 karakter.',
        'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
    ]);

    // Simpan nomor telepon dalam format lokal yang konsisten (08...).
    // Pemeriksaan keunikan dilakukan setelah normalisasi agar 08..., 628..., dan +628...
    // tidak dapat digunakan untuk membuat beberapa akun bagi nomor yang sama.
    $nomorInput = trim((string) $request->input('no_hp'));
    $nomorNormal = str_starts_with($nomorInput, '+62')
        ? '0' . substr($nomorInput, 3)
        : (str_starts_with($nomorInput, '62') ? '0' . substr($nomorInput, 2) : $nomorInput);
    $nomorInternasional = '62' . substr($nomorNormal, 1);

    if (User::whereIn('no_hp', [$nomorNormal, $nomorInternasional, '+' . $nomorInternasional])->exists()) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'no_hp' => 'Nomor HP sudah terdaftar. Silakan masuk menggunakan akun tersebut.',
        ]);
    }

    // Pencocokan Master Konsumen mengikuti perilaku lama; hanya menyamakan variasi format.
    // Catatan keamanan: verifikasi kepemilikan nomor tetap dibutuhkan sebelum
    // mengizinkan akses ke riwayat dan kendaraan pelanggan (di luar cakupan patch ini).
    $konsumen = Konsumen::whereIn('no_hp', [$nomorNormal, $nomorInternasional, '+' . $nomorInternasional])->first();

    /*
    |--------------------------------------------------------------------------
    | Simpan akun baru
    |--------------------------------------------------------------------------
    */
    User::create([
        'name'        => trim($request->nama_lengkap),
        'username'    => strtolower(trim($request->username)),
        'no_hp'       => $nomorNormal,
        'role'        => 'pelanggan',
        'konsumen_id' => $konsumen ? $konsumen->id : null,
        'password'    => bcrypt($request->password),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Kembali ke login
    |--------------------------------------------------------------------------
    */
    return redirect()
        ->route('login')
        ->with(
            'success',
            'Pendaftaran akun berhasil! Silakan masuk menggunakan username atau nomor HP dan kata sandi Anda.'
        );
    }

    public function logout(Request $request)
    {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
    }

    private function redirectByRole()
    {
        $role = auth()->user()->role;
        if ($role === 'pelanggan') {
            return redirect()->route('pelanggan.dashboard');
        }
        return redirect('/admin/dashboard');
    }
}