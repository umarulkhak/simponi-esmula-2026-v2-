<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login_sneat');
    }

    public function showLoginFormWali()
    {
        return view('auth.login_sneat_wali');
    }

    /**
     * Handle post-login redirect based on user role.
     */
    public function authenticated(Request $request, $user)
    {
        if ($user->akses == 'operator' || $user->akses == 'admin') {
            return redirect()->route('operator.beranda');
        } elseif ($user->akses == 'wali') {
            return redirect()->route('wali.beranda');
        }

        // Fallback: jika ada role lain di masa depan
        return redirect()->route('home');
    }
}
