<?php

namespace App\Http\Controllers;

use App\Models\User as Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controller untuk manajemen data Wali Murid.
 *
 * Mengatur operasi CRUD (Create, Read, Update, Delete) data wali murid.
 * Semua flash message menyertakan nama wali untuk UX lebih baik.
 * Menggunakan scope `wali()` untuk memfilter hanya user dengan akses 'wali'.
 *
 * @author  SIMPONI Team
 * @date    5 April 2025
 * @updated 5 April 2025 — Clean code & dokumentasi lengkap
 */
class WaliController extends Controller
{
    /**
     * Prefix path untuk view operator.
     */
    private string $viewPath = 'operator.';

    /**
     * Prefix route untuk wali (tanpa group name prefix).
     * Contoh: 'wali.index', 'wali.create', dll.
     */
    private string $routePrefix = 'wali';

    /**
     * Nama view untuk halaman index.
     */
    private string $viewIndex = 'wali_index';

    /**
     * Nama view untuk form tambah/edit.
     * Catatan: Reuse view 'user_form' untuk konsistensi form.
     */
    private string $viewForm = 'user_form';

    /**
     * Nama view untuk halaman detail.
     */
    private string $viewShow = 'wali_show';

    /**
     * Menampilkan daftar wali murid dengan fitur pencarian.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Model::wali();

        // Jika ada pencarian, filter berdasarkan nama atau email
        if ($request->filled('q')) {
            $query = $query->where('name', 'LIKE', '%' . $request->q . '%')
                           ->orWhere('email', 'LIKE', '%' . $request->q . '%');
        }

        // Ambil data dengan pagination
        $models = $query->latest()->paginate(20);

        return view($this->viewPath . $this->viewIndex, [
            'models'      => $models,
            'routePrefix' => $this->routePrefix,
            'title'       => 'Data Wali Murid',
        ]);
    }

    /**
     * Menampilkan form tambah data wali murid.
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
            'title'  => 'Form Tambah Data Wali Murid',
        ]);
    }

    /**
     * Menyimpan data wali murid baru ke database.
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
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Hash password & set default values
        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();
        $validated['akses'] = 'wali';

        // Simpan ke database
        $wali = Model::create($validated);

        // Flash message sukses dengan nama wali
        flash("✅ Wali '{$wali->name}' berhasil ditambahkan")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menampilkan form edit data wali murid.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $wali = Model::wali()->findOrFail($id);

        return view($this->viewPath . $this->viewForm, [
            'model'  => $wali,
            'method' => 'PUT',
            'route'  => [$this->routePrefix . '.update', $wali->id],
            'button' => 'UPDATE',
            'title'  => 'Form Edit Data Wali Murid',
        ]);
    }

    /**
     * Memperbarui data wali murid di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $wali = Model::wali()->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $wali->id,
            'nohp'     => 'required|string|unique:users,nohp,' . $wali->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Handle password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // biarkan password lama
        }

        // Simpan nama lama untuk flash message
        $namaLama = $wali->name;

        // Update data wali
        $wali->update($validated);

        // Flash message sukses dengan detail perubahan
        $pesan = "✅ Wali berhasil diperbarui";
        if ($namaLama !== $validated['name']) {
            $pesan .= " ({$namaLama} → {$validated['name']})";
        } else {
            $pesan .= ": {$validated['name']}";
        }

        flash($pesan)->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menghapus data wali murid dari database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $wali = Model::wali()->findOrFail($id);

        // Simpan nama wali untuk flash message
        $namaWali = $wali->name;

        // Hapus data wali
        $wali->delete();

        // Flash message sukses dengan nama wali
        flash("🗑️ Wali '{$namaWali}' berhasil dihapus")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menampilkan detail wali murid beserta daftar siswa yang belum punya wali.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $wali = Model::with('siswa')->wali()->findOrFail($id);

        // Ambil daftar siswa yang belum punya wali (untuk dropdown)
        $siswa = \App\Models\Siswa::whereNull('wali_id')->pluck('nama', 'id');

        return view($this->viewPath . $this->viewShow, [
            'model'       => $wali,
            'siswa'       => $siswa,
            'title'       => 'Detail Data Wali',
            'routePrefix' => $this->routePrefix,
        ]);
    }

    /**
     * Menghapus beberapa wali murid sekaligus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $count = Model::wali()->whereIn('id', $request->ids)->delete();
        if ($count === 0) {
            flash('ℹ️ Tidak ada data wali yang bisa dihapus.')->info();
            return redirect()->back();
        }

        flash("🗑️ {$count} wali berhasil dihapus")->success();
        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Menghapus semua data wali murid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAll(Request $request)
    {
        if ($request->confirm !== 'hapus-semua-wali') {
            flash('⚠️ Konfirmasi diperlukan untuk menghapus semua data.')->warning();
            return redirect()->route($this->routePrefix . '.index');
        }

        $count = Model::wali()->count();
        if ($count > 0) {
            Model::wali()->delete();
            flash("✅ {$count} wali berhasil dihapus.")->success();
        } else {
            flash('ℹ️ Tidak ada data wali yang bisa dihapus.')->info();
        }

        return redirect()->route($this->routePrefix . '.index');
    }
}
