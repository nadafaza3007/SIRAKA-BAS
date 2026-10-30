<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Staff route guard independent from users.status (column absent in sirakabas). */
class StaffOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        abort_unless(in_array(auth()->user()->role, ['admin', 'anggota'], true), 403);
        return $next($request);
    }
}