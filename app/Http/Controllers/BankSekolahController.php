<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankSekolahRequest;
use App\Http\Requests\UpdateBankSekolahRequest;
use App\Models\BankSekolah;
use Illuminate\Http\Request;

class BankSekolahController extends Controller
{
    private string $viewPath    = 'operator.';
    private string $routePrefix = 'banksekolah';
    private string $viewIndex   = 'banksekolah_index';
    private string $viewForm    = 'banksekolah_form';
    private string $viewShow    = 'banksekolah_show';

    public function index(Request $request)
    {
        $query = BankSekolah::query();

        if ($request->filled('q')) {
            $query->where('nama_rekening', 'like', '%' . $request->q . '%');
        }

        $models = $query->orderBy('id', 'desc')->paginate(50);

        return view($this->viewPath . $this->viewIndex, [
            'models'      => $models,
            'routePrefix' => $this->routePrefix,
            'title'       => 'Data Rekening Sekolah',
        ]);
    }

    public function create()
    {
        return view($this->viewPath . $this->viewForm, [
            'model'    => new BankSekolah(),
            'method'   => 'POST',
            'route'    => $this->routePrefix . '.store',
            'button'   => 'SIMPAN',
            'title'    => 'Form Tambah Rekening Sekolah',
            'listbank' => \App\Models\Bank::pluck('nama_bank', 'id'),
        ]);
    }

    public function store(StoreBankSekolahRequest $request)
    {
        $data = $request->validated();

        $bank = \App\Models\Bank::find($data['bank_id']);
        if (!$bank) {
            return back()->withErrors(['bank_id' => 'Bank tidak ditemukan']);
        }

        unset($data['bank_id']);
        $data['kode']      = $bank->sandi_bank;
        $data['nama_bank'] = $bank->nama_bank;

        BankSekolah::create($data);

        flash('✅ Data rekening sekolah berhasil disimpan')->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    public function edit(BankSekolah $banksekolah)
    {
        return view($this->viewPath . $this->viewForm, [
            'model'    => $banksekolah,
            'method'   => 'PUT',
            'route'    => [$this->routePrefix . '.update', $banksekolah->id],
            'button'   => 'UPDATE',
            'title'    => 'Form Edit Data Rekening Sekolah',
            'listbank' => \App\Models\Bank::pluck('nama_bank', 'id'),
        ]);
    }

    public function update(UpdateBankSekolahRequest $request, BankSekolah $banksekolah)
    {
        $validated = $request->validated();

        $namaLama = $banksekolah->nama_rekening;

        $banksekolah->update($validated);

        $pesan = "✅ Data rekening sekolah berhasil diperbarui";
        if ($banksekolah->wasChanged('nama_rekening')) {
            $pesan .= " — Atas nama rekening: {$namaLama} → {$banksekolah->nama_rekening}";
        } else {
            $pesan .= " — Bank: {$banksekolah->nama_bank}";
        }

        flash($pesan)->success();

        return redirect()->route($this->routePrefix . '.index');
    }

   public function destroy(BankSekolah $banksekolah)
{
    $namaBank = $banksekolah->nama_bank;
    $page     = request()->get('page', 1); // ambil halaman saat ini

    try {
        $banksekolah->delete();
        flash("🗑️ Bank '{$namaBank}' berhasil dihapus")->success();
    } catch (\Exception $e) {
        flash("❌ Gagal menghapus bank '{$namaBank}': " . $e->getMessage())->error();
        return redirect()->route($this->routePrefix . '.index', ['page' => $page]);
    }

    // cek apakah halaman saat ini masih ada data
    $models = BankSekolah::orderBy('id', 'desc')->paginate(50, ['*'], 'page', $page);
    if ($models->isEmpty() && $page > 1) {
        $page--; // pindah ke halaman sebelumnya jika kosong
    }

    return redirect()->route($this->routePrefix . '.index', ['page' => $page]);
}

}
