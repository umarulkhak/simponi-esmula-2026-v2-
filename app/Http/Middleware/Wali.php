<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Wali
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('login-wali');
        }

        // 2. Ambil nilai akses
        $akses = Auth::user()->akses;

        // 3. Cek apakah akses = 'wali'
        if ($akses === 'wali') {
            return $next($request);
        }

        // 4. Jika bukan wali, tampilkan pesan error
        abort(403, 'Akses khusus wali murid — Anda login sebagai: ' . ($akses ?? 'tidak diketahui'));
    }
}
