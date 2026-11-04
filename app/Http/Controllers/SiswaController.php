<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Siswa as Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Exports\SiswaExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller untuk manajemen data Siswa.
 *
 * Mengelola seluruh operasi CRUD (Create, Read, Update, Delete) data siswa,
 * termasuk fitur tambahan:
 * - Upload & hapus foto profil
 * - Relasi dengan wali murid dan user pembuat
 * - Pencarian berbasis nama
 * - Hapus massal (beberapa atau semua data)
 *
 * Semua operasi create/update dilindungi oleh FormRequest untuk validasi otomatis.
 * Operasi delete (termasuk massal) menangani penghapusan file foto secara aman.
 *
 * @author  SIMPONI Team
 * @date    3 Agustus 2025
 * @updated 14 Oktober 2025 — Tambah fitur hapus massal & perbarui dokumentasi
 */
class SiswaController extends Controller
{
    private string $viewPath = 'operator.';
    private string $routePrefix = 'siswa';
    private string $viewIndex = 'siswa_index';
    private string $viewForm = 'siswa_form';
    private string $viewShow = 'siswa_show';

    // =========================================================================
    // READ — Menampilkan Data
    // =========================================================================

    public function index(Request $request)
    {
        $query = Model::with('wali', 'user');

        // Filter status
        if ($request->filled('status')) {
            $statusValue = $request->status;
            if ($statusValue !== '') {
                $query->where('status', $statusValue);
            }
        } else {
            $query->where('status', 'aktif');
        }

        // Filter pencarian
        if ($request->filled('q')) {
            $query = $query->search($request->q);
        }

        $models = $query->latest()->paginate(20);

        // 🔹 HITUNG STATISTIK (SELALU HITUNG SEMUA DATA, TIDAK TERGANTUNG FILTER)
        $allSiswa = Model::all();
        // 🔹 HITUNG STATISTIK YANG KONSISTEN DENGAN LOGIKA STATUS
        $stats = [
            'kelas_vii' => $allSiswa->where('kelas', 'VII')->where('status', 'aktif')->count(),
            'kelas_viii' => $allSiswa->where('kelas', 'VIII')->where('status', 'aktif')->count(),
            'kelas_ix' => $allSiswa->where('kelas', 'IX')->where('status', 'aktif')->count(),
            'lulus' => $allSiswa->where('status', 'lulus')->count(),
            'tidak_aktif' => $allSiswa->where('status', 'tidak_aktif')->count(),
            'total_aktif' => $allSiswa->where('status', 'aktif')->count(), // untuk card "Siswa Aktif"
        ];

        return view($this->viewPath . $this->viewIndex, [
            'models'      => $models,
            'routePrefix' => $this->routePrefix,
            'title'       => 'Data Siswa',
            'stats'       => $stats,
        ]);
    }

    public function show(int $id)
    {
        $siswa = Model::findOrFail($id);

        return view($this->viewPath . $this->viewShow, [
            'model'       => $siswa,
            'title'       => 'Detail Siswa',
            'routePrefix' => $this->routePrefix,
        ]);
    }

    // =========================================================================
    // CREATE — Form & Penyimpanan
    // =========================================================================

    public function create()
    {
        return view($this->viewPath . $this->viewForm, [
            'model'  => new Model(),
            'method' => 'POST',
            'route'  => $this->routePrefix . '.store',
            'button' => 'SIMPAN',
            'title'  => 'Form Tambah Data Siswa',
            'wali'   => $this->getWaliOptions(),
        ]);
    }

    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        if ($request->filled('wali_id')) {
            $validated['wali_status'] = 'ok';
        }

        $validated['user_id'] = Auth::id();
        $siswa = Model::create($validated);

        flash("✅ Data siswa '{$siswa->nama}' berhasil disimpan")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    // =========================================================================
    // UPDATE — Form & Pembaruan
    // =========================================================================

    public function edit(int $id)
    {
        $siswa = Model::findOrFail($id);

        return view($this->viewPath . $this->viewForm, [
            'model'  => $siswa,
            'method' => 'PUT',
            'route'  => [$this->routePrefix . '.update', $siswa->id],
            'button' => 'UPDATE',
            'title'  => 'Form Edit Data Siswa',
            'wali'   => $this->getWaliOptions(),
        ]);
    }

    public function update(UpdateSiswaRequest $request, int $id)
    {
        $siswa = Model::findOrFail($id);
        $validated = $request->validated();

        $kelasLama = $siswa->kelas;
        $namaSiswa = $siswa->nama;

        if ($request->hasFile('foto')) {
            $this->deleteFotoIfExists($siswa->foto);
            $validated['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        $validated['wali_status'] = $request->filled('wali_id') ? 'ok' : null;
        $validated['user_id'] = Auth::id();

        $siswa->update($validated);

        $pesan = "✅ Data siswa '{$namaSiswa}' berhasil diperbarui";
        if ($kelasLama !== $validated['kelas']) {
            $pesan .= " (Kelas: {$kelasLama} → {$validated['kelas']})";
        }

        flash($pesan)->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    // =========================================================================
    // DELETE — Penghapusan Data
    // =========================================================================

    public function destroy(int $id)
    {
        $siswa = Model::findOrFail($id);
        $this->deleteFotoIfExists($siswa->foto);

        $namaSiswa = $siswa->nama;
        $siswa->delete();

        flash("🗑️ Data siswa '{$namaSiswa}' berhasil dihapus")->success();

        return redirect()->route($this->routePrefix . '.index');
    }

    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|string',
            'delete_all' => 'nullable|boolean',
        ]);

        try {
            if ($request->boolean('delete_all')) {
                $siswas = Model::select('nama', 'kelas')->get();
                $total = $siswas->count();

                Model::chunk(200, function ($chunk) {
                    foreach ($chunk as $siswa) {
                        $this->deleteFotoIfExists($siswa->foto);
                        $siswa->delete();
                    }
                });

                if ($total > 0) {
                    $pesan = $this->formatDaftarSiswa($siswas);
                    flash("🗑️ Berhasil menghapus semua data siswa ($total):<br>{$pesan}")->success();
                } else {
                    flash("🗑️ Tidak ada data siswa untuk dihapus")->warning();
                }
            } else {
                $ids = [];
                if ($request->filled('ids')) {
                    $parsed = json_decode($request->ids, true);
                    if (is_array($parsed)) {
                        $ids = array_filter($parsed, fn($id) => is_numeric($id));
                    }
                }

                if (!empty($ids)) {
                    $siswas = Model::whereIn('id', $ids)->select('id', 'nama', 'kelas')->get();

                    foreach ($siswas as $siswa) {
                        $this->deleteFotoIfExists($siswa->foto);
                        $siswa->delete();
                    }

                    if ($siswas->isNotEmpty()) {
                        $pesan = $this->formatDaftarSiswa($siswas);
                        flash("🗑️ Berhasil menghapus " . count($siswas) . " data siswa:<br>{$pesan}")->success();
                    } else {
                        flash("❌ Tidak ada data siswa yang valid untuk dihapus")->warning();
                    }
                } else {
                    flash("❌ Tidak ada data yang dipilih untuk dihapus")->warning();
                }
            }
        } catch (\Exception $e) {
            flash("❌ Gagal menghapus data: " . $e->getMessage())->error();
        }

        return redirect()->route($this->routePrefix . '.index');
    }

    private function formatDaftarSiswa($siswas): string
    {
        $maxTampil = 10;
        $total = $siswas->count();

        if ($total <= $maxTampil) {
            return $siswas->map(fn($s) => "{$s->nama} ({$s->kelas})")->join(', ');
        }

        $daftarAwal = $siswas->take($maxTampil)
            ->map(fn($s) => "{$s->nama} ({$s->kelas})")
            ->join(', ');

        $sisa = $total - $maxTampil;
        return "{$daftarAwal}, dan {$sisa} lainnya";
    }

    // =========================================================================
    // HELPER — Fungsi Bantuan Internal
    // =========================================================================

    private function getWaliOptions()
    {
        return User::where('akses', 'wali')
                   ->orderBy('name')
                   ->pluck('name', 'id');
    }

    private function deleteFotoIfExists(?string $path): void
    {
        if (!empty($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
    public function export()
    {
        return Excel::download(new SiswaExport, 'data_siswa_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    // =========================================================================
    // PROMOSI & KELULUSAN
    // =========================================================================

    public function promosiForm()
    {
        $jumlahVII = Model::where('kelas', 'VII')->where('status', 'aktif')->count();
        $jumlahVIII = Model::where('kelas', 'VIII')->where('status', 'aktif')->count();
        $jumlahIX = Model::where('kelas', 'IX')->where('status', 'aktif')->count();

        return view($this->viewPath . 'siswa_promosi', [
            'title' => 'Promosi & Kelulusan Siswa',
            'jumlahVII' => $jumlahVII,
            'jumlahVIII' => $jumlahVIII,
            'jumlahIX' => $jumlahIX,
            'tahunSekarang' => now()->year,
        ]);
    }

    public function prosesPromosi(Request $request)
    {
        // 🔒 Pastikan hanya operator yang bisa memproses
        if (auth()->user()->akses !== 'operator') {
            abort(403, 'Hanya operator yang boleh melakukan promosi siswa.');
        }

        // ✅ Validasi konfirmasi
        $request->validate([
            'confirm' => 'required|accepted',
        ], [
            'confirm.accepted' => 'Anda harus mencentang konfirmasi untuk melanjutkan.'
        ]);

        \DB::beginTransaction();
        try {
            $tahunSekarang = now()->year;

            // Hitung jumlah siswa sebelum promosi (untuk laporan)
            $jumlahVII   = Model::where('kelas', 'VII')->where('status', 'aktif')->count();
            $jumlahVIII  = Model::where('kelas', 'VIII')->where('status', 'aktif')->count();
            $jumlahIX    = Model::where('kelas', 'IX')->where('status', 'aktif')->count();

            // ⚠️ Urutan HARUS dari atas ke bawah agar tidak tumpang tindih
            // 1️⃣ Luluskan kelas IX
            Model::where('kelas', 'IX')
                ->where('status', 'aktif')
                ->update([
                    'status' => 'lulus',
                    'tahun_lulus' => $tahunSekarang,
                ]);

            // 2️⃣ Naikkan kelas VIII → IX
            Model::where('kelas', 'VIII')
                ->where('status', 'aktif')
                ->update(['kelas' => 'IX']);

            // 3️⃣ Naikkan kelas VII → VIII
            Model::where('kelas', 'VII')
                ->where('status', 'aktif')
                ->update(['kelas' => 'VIII']);

            \DB::commit();

            // 🟩 Pesan notifikasi hasil
            $pesan = "✅ Promosi & kelulusan tahun {$tahunSekarang} berhasil!<br>";
            $pesan .= "<ul class='mb-0'>";
            if ($jumlahIX > 0) {
                $pesan .= "<li>{$jumlahIX} siswa kelas IX ditandai sebagai <strong>LULUS</strong></li>";
            }
            if ($jumlahVIII > 0) {
                $pesan .= "<li>{$jumlahVIII} siswa kelas VIII naik ke kelas IX</li>";
            }
            if ($jumlahVII > 0) {
                $pesan .= "<li>{$jumlahVII} siswa kelas VII naik ke kelas VIII</li>";
            }
            if ($jumlahVII == 0 && $jumlahVIII == 0 && $jumlahIX == 0) {
                $pesan .= "<li>Tidak ada siswa aktif yang diproses.</li>";
            }
            $pesan .= "</ul>";

            flash($pesan)->success();
            return redirect()->route($this->routePrefix . '.index');

        } catch (\Exception $e) {
            \DB::rollBack();
            flash("❌ Gagal memproses promosi: " . $e->getMessage())->error();
            return back();
        }
    }

    public function rollbackPromosi(Request $request)
    {
        if (auth()->user()->akses !== 'operator') {
            abort(403, 'Hanya operator yang boleh melakukan rollback promosi.');
        }

        $request->validate([
            'confirm_rollback' => 'required|accepted',
        ], [
            'confirm_rollback.accepted' => 'Anda harus mencentang konfirmasi sebelum rollback.',
        ]);

        \DB::beginTransaction();
        try {
            $tahunSekarang = now()->year;

            // 🔹 1️⃣ Kembalikan siswa yang LULUS (IX sebelumnya)
            $jumlahLulus = Model::where('status', 'lulus')
                ->where('tahun_lulus', $tahunSekarang)
                ->update([
                    'status' => 'aktif',
                    'kelas'  => 'IX',
                    'tahun_lulus' => null,
                ]);

            // 🔹 2️⃣ Kembalikan siswa yang sebelumnya naik ke IX → jadi VIII
            $jumlahIX = Model::where('kelas', 'IX')
                ->where('status', 'aktif')
                ->update(['kelas' => 'VIII']);

            // 🔹 3️⃣ Kembalikan siswa yang sebelumnya naik ke VIII → jadi VII
            $jumlahVIII = Model::where('kelas', 'VIII')
                ->where('status', 'aktif')
                ->update(['kelas' => 'VII']);

            \DB::commit();

            // 🔹 Pesan hasil
            $pesan = "🔁 Rollback promosi & kelulusan tahun {$tahunSekarang} berhasil.<br><ul class='mb-0'>";
            if ($jumlahLulus > 0) $pesan .= "<li>{$jumlahLulus} siswa dikembalikan dari status LULUS ke kelas IX</li>";
            if ($jumlahIX > 0) $pesan .= "<li>{$jumlahIX} siswa kelas IX dikembalikan ke VIII</li>";
            if ($jumlahVIII > 0) $pesan .= "<li>{$jumlahVIII} siswa kelas VIII dikembalikan ke VII</li>";
            if ($jumlahLulus == 0 && $jumlahIX == 0 && $jumlahVIII == 0)
                $pesan .= "<li>Tidak ada data yang diubah.</li>";
            $pesan .= "</ul>";

            flash($pesan)->success();
            return redirect()->route($this->routePrefix . '.index');

        } catch (\Exception $e) {
            \DB::rollBack();
            flash("❌ Gagal melakukan rollback: " . $e->getMessage())->error();
            return back();
        }
    }


}
