<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WaliSiswaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wali_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('akses', 'wali'),
            ],
            'siswa_id' => ['required', 'integer', 'exists:siswas,id'],
        ]);

        $siswa = DB::transaction(function () use ($validated) {
            $siswa = Siswa::query()
                ->whereKey($validated['siswa_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($siswa->wali_id !== null) {
                return null;
            }

            $siswa->wali_id = $validated['wali_id'];
            $siswa->wali_status = 'ok';
            $siswa->save();

            return $siswa;
        });

        if ($siswa === null) {
            return redirect()->back()
                ->withErrors(['siswa_id' => 'Siswa sudah terhubung dengan wali lain.'])
                ->withInput();
        }

        flash('Data sudah ditambahkan')->success();
        return redirect()->route('wali.show', $validated['wali_id']);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $waliId = $siswa->wali_id; // Simpan wali lama untuk redirect

        $siswa->wali_id = null;
        $siswa->wali_status = null;
        $siswa->save();

        flash('Data sudah dihapus')->success();
        return redirect()->route('wali.show', $waliId);
    }
}
