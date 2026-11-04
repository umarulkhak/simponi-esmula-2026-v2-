<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Operator
{
    public function handle(Request $request, Closure $next)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        $akses = Auth::user()->akses;

        // Izinkan operator dan admin
        if ($akses === 'operator' || $akses === 'admin') {
            return $next($request);
        }

        // Jika user adalah wali, redirect ke dashboard wali
        if ($akses === 'wali') {
            return redirect()->route('wali.beranda')->with('error', 'Anda tidak memiliki akses ke halaman operator.');
        }

        // Jika akses tidak dikenali (misal: null, atau role lain)
        abort(403, 'Akses ditolak. Hanya operator dan admin yang diizinkan.');
    }
}
