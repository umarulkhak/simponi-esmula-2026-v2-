<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBiayaRequest;
use App\Http\Requests\UpdateBiayaRequest;
use App\Models\Biaya;
use Illuminate\Http\Request;

/**
 * Controller untuk manajemen data Biaya.
 *
 * Mengatur operasi CRUD (Create, Read, Update, Delete) data biaya.
 *
 * @author SIMPONI Team
 */
class BiayaController extends Controller
{
    private string $viewPath    = 'operator.';
    private string $routePrefix = 'biaya';
    private string $viewIndex   = 'biaya_index';
    private string $viewForm    = 'biaya_form';
    private string $viewShow    = 'biaya_show';

    public function index(Request $request)
    {
        $query = Biaya::with('user');

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        return view($this->viewPath . $this->viewIndex, [
            'models'      => $query->latest()->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title'       => 'Data Biaya',
        ]);
    }

    public function create()
    {
        return view($this->viewPath . $this->viewForm, [
            'model'  => new Biaya(),
            'method' => 'POST',
            'route'  => $this->routePrefix . '.store',
            'button' => 'SIMPAN',
            'title'  => 'Form Tambah Data Biaya',
        ]);
    }

    public function store(StoreBiayaRequest $request)
    {
        $biaya = Biaya::create($request->validated());

        flash("✅ Biaya '{$biaya->nama}' berhasil disimpan")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    public function edit(Biaya $biaya)
    {
        return view($this->viewPath . $this->viewForm, [
            'model'  => $biaya,
            'method' => 'PUT',
            'route'  => [$this->routePrefix . '.update', $biaya->id],
            'button' => 'UPDATE',
            'title'  => 'Form Edit Data Biaya',
        ]);
    }

    public function update(UpdateBiayaRequest $request, Biaya $biaya)
    {
        $validated = $request->validated();
        $namaLama  = $biaya->nama;

        $biaya->update($validated);

        $pesan = "✅ Biaya berhasil diperbarui";
        $pesan .= ($namaLama !== $validated['nama'])
            ? " ({$namaLama} → {$validated['nama']})"
            : ": {$validated['nama']}";

        flash($pesan)->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Hapus satu data biaya.
     */
    public function destroy(Biaya $biaya)
    {
        $nama = $biaya->nama;
        $biaya->delete();

        flash("🗑️ Biaya '{$nama}' berhasil dihapus")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Hapus massal: terpilih atau semua.
     */
    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids'        => ['nullable', 'array'],
            'ids.*'      => ['exists:biayas,id'],
            'delete_all' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('delete_all')) {
            $count = Biaya::count();
            Biaya::query()->delete();

            flash("🗑️ Semua ({$count}) data biaya berhasil dihapus.")->success();
        } elseif ($request->filled('ids')) {
            $ids   = $request->ids;
            $names = Biaya::whereIn('id', $ids)->pluck('nama')->toArray();

            Biaya::whereIn('id', $ids)->delete();

            flash("🗑️ " . count($names) . " biaya berhasil dihapus: " . implode(', ', $names))->success();
        } else {
            flash("⚠️ Tidak ada data yang dipilih untuk dihapus.")->warning();
        }

        return redirect()->route($this->routePrefix . '.index');
    }

    public function show(Biaya $biaya)
    {
        return view($this->viewPath . $this->viewShow, [
            'model'       => $biaya,
            'title'       => 'Detail Biaya',
            'routePrefix' => $this->routePrefix,
        ]);
    }
}
