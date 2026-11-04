<?php

namespace App\Http\Controllers;

use App\Models\User as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controller untuk manajemen data pengguna (Admin & Operator).
 *
 * Mengatur operasi CRUD (Create, Read, Update, Delete) data user.
 * Mengecualikan user dengan akses 'wali' di halaman index.
 * Semua flash message menyertakan nama user untuk UX lebih baik.
 *
 * @author  SIMPONI Team
 * @date    5 April 2025
 * @updated 5 April 2025 — Clean code & dokumentasi lengkap
 */
class UserController extends Controller
{
    /**
     * Prefix path untuk view operator.
     */
    private string $viewPath = 'operator.';

    /**
     * Prefix route untuk user (tanpa group name prefix).
     * Contoh: 'user.index', 'user.create', dll.
     */
    private string $routePrefix = 'user';

    /**
     * Nama view untuk halaman index.
     */
    private string $viewIndex = 'user_index';

    /**
     * Nama view untuk form tambah/edit.
     */
    private string $viewForm = 'user_form';

    /**
     * Nama view untuk halaman detail (placeholder untuk fitur mendatang).
     */
    private string $viewShow = 'user_show';

    /**
     * Menampilkan daftar user (kecuali wali) dengan fitur pencarian.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Model::where('akses', '<>', 'wali');

        // Jika ada pencarian, filter berdasarkan nama, email, atau nohp
        if ($request->filled('q')) {
            $query = $query->where('name', 'LIKE', '%' . $request->q . '%')
                           ->orWhere('email', 'LIKE', '%' . $request->q . '%')
                           ->orWhere('nohp', 'LIKE', '%' . $request->q . '%');
        }

        // Ambil data dengan pagination
        $models = $query->latest()->paginate(50);

        return view($this->viewPath . $this->viewIndex, [
            'models'      => $models,
            'routePrefix' => $this->routePrefix,
            'title'       => 'Data User',
        ]);
    }

    /**
     * Menampilkan form tambah data user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view($this->viewPath . $this->viewForm, [
            'model'  => new Model(),
            'method' => 'POST',
            'route'  => $this->routePrefix . '.store',
            'button' => 'SIMPAN',
            'title'  => 'Form Tambah Data User',
        ]);
    }

    /**
     * Menyimpan data user baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'nohp'     => 'required|string|unique:users,nohp',
            'akses'    => 'required|in:operator,admin,wali',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Hash password & set default values
        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        // Simpan ke database
        $user = Model::create($validated);

        // Flash message sukses dengan nama user
        flash("✅ User '{$user->name}' berhasil ditambahkan")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menampilkan form edit data user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Model::findOrFail($id);

        return view($this->viewPath . $this->viewForm, [
            'model'  => $user,
            'method' => 'PUT',
            'route'  => [$this->routePrefix . '.update', $user->id],
            'button' => 'UPDATE',
            'title'  => 'Form Edit Data User',
        ]);
    }

    /**
     * Memperbarui data user di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Model::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'nohp'     => 'required|string|unique:users,nohp,' . $user->id,
            'akses'    => 'required|in:operator,admin,wali',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Handle password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // biarkan password lama
        }

        // Simpan nama lama untuk flash message
        $namaLama = $user->name;

        // Update data user
        $user->update($validated);

        // Flash message sukses dengan detail perubahan
        $pesan = "✅ User berhasil diperbarui";
        if ($namaLama !== $validated['name']) {
            $pesan .= " ({$namaLama} → {$validated['name']})";
        } else {
            $pesan .= ": {$validated['name']}";
        }

        flash($pesan)->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menghapus data user dari database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = Model::findOrFail($id);

        // Proteksi akun penting
        if ($user->email === config('app.admin_default_email')) {
            flash('🔒 Akun ini tidak dapat dihapus karena merupakan akun penting.')->error();
            return redirect()->route($this->routePrefix . '.index');
        }

        // Simpan nama user untuk flash message
        $namaUser = $user->name;

        // Hapus data user
        $user->delete();

        // Flash message sukses dengan nama user
        flash("🗑️ User '{$namaUser}' berhasil dihapus")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menampilkan detail user (placeholder untuk fitur mendatang).
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Siapkan jika ingin menampilkan detail
        // $user = Model::findOrFail($id);
        // return view($this->viewPath . $this->viewShow, compact('user'));
    }
}
