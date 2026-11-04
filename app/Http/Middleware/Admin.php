<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        $akses = Auth::user()->akses;

        // 2. Izinkan hanya admin
        if ($akses === 'admin') {
            return $next($request);
        }

        // 3. Redirect berdasarkan peran user
        if ($akses === 'wali') {
            return redirect()->route('wali.beranda')
                ->with('error', 'Anda tidak memiliki akses ke halaman admin.');
        }

        if ($akses === 'operator') {
            return redirect()->route('operator.beranda')
                ->with('error', 'Anda tidak memiliki akses ke halaman admin.');
        }

        // 4. Jika role tidak dikenali
        abort(403, 'Akses ditolak. Hanya admin yang diizinkan.');
    }
}
