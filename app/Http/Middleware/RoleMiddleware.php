<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan masuk terlebih dahulu.']);
        }

        $user = auth()->user();

        if ($user->status !== 'aktif') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['login' => 'Akun Anda dinonaktifkan, silakan hubungi bengkel/Admin.']);
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            if ($user->isPelanggan()) {
                return redirect()->route('pelanggan.rekam-servis');
            }
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        }

        return $next($request);
    }
}

