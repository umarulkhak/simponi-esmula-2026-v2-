<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User as Model;
use Illuminate\Support\Facades\Hash;

class WaliMuridProfileController extends Controller
{
    private string $viewPath = 'wali.';
    private string $routePrefix = 'wali.profile';
    private string $viewIndex = 'wali_index';
    private string $viewForm  = 'wali_form';
    private string $viewShow  = 'profile_show';

    // 👇 wajib ada biar route index tidak error
    public function index()
    {
        $user = auth()->user();

        return view($this->viewPath . $this->viewIndex, [
            'model' => $user,
            'title' => 'Profil Wali Murid',
        ]);
    }

    public function edit()
    {
        $user = auth()->user();

        return view($this->viewPath . $this->viewForm, [
            'model'  => $user,
            'method' => 'PUT',
            'route'  => [$this->routePrefix . '.update', $user->id],
            'button' => 'UPDATE',
            'title'  => 'Edit Profil Wali Murid',
        ]);
    }

    public function update(Request $request, $id)
    {
        if ((int) $id !== (int) auth()->id()) {
            abort(403, 'Anda hanya dapat memperbarui profil sendiri.');
        }

        $user = auth()->user();

        // Aturan validasi dasar
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'nohp'     => 'required|string|unique:users,nohp,' . $user->id,
        ];

        // Tambahkan aturan password hanya jika diisi
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        // Pesan error kustom dalam bahasa Indonesia
        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string'   => 'Nama lengkap harus berupa teks.',
            'name.max'      => 'Nama lengkap maksimal :max karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
            'email.unique'  => 'Email sudah digunakan oleh pengguna lain.',
            'nohp.required' => 'Nomor HP wajib diisi.',
            'nohp.string'   => 'Nomor HP harus berupa teks.',
            'nohp.unique'   => 'Nomor HP sudah digunakan oleh pengguna lain.',
            'password.min'  => 'Password minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];

        $validated = $request->validate($rules, $messages);

        // Hash password hanya jika diisi
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $namaLama = $user->name;
        $user->update($validated);

        $pesan = "✅ Profil berhasil diperbarui";
        $pesan .= ($namaLama !== $validated['name'])
            ? " ({$namaLama} → {$validated['name']})"
            : ": {$validated['name']}";

        flash($pesan)->success();

        return redirect()->route('wali.profile.index');
    }

    public function show($id)
    {
        // bisa disiapkan nanti kalau mau detail
    }
}
