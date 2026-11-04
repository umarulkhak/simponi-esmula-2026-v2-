<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Biaya;
use App\Models\Siswa;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTagihanRequest;
use App\Models\Pembayaran;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Controller untuk manajemen data Tagihan Siswa.
 *
 * Mengatur CRUD Tagihan, termasuk:
 * - Menampilkan daftar tagihan terbaru per siswa
 * - Membuat tagihan massal berdasarkan filter siswa & biaya
 * - Menampilkan detail tagihan per siswa
 * - Menghapus tagihan (per item, massal, atau seluruhnya per siswa)
 *
 * @author  SIMPONI Team
 * @date    6 September 2025
 * @updated 20 Oktober 2025 — Tambah filter status & kelas, hapus massal
 */
class TagihanController extends Controller
{
    /** Prefix path view dan route */
    private string $viewPath = 'operator.';
    private string $routePrefix = 'tagihan';

    /** Nama view blade */
    private string $viewIndex = 'tagihan_index';
    private string $viewForm  = 'tagihan_form';
    private string $viewShow  = 'tagihan_show';

    /** Status default untuk tagihan baru */
    private const STATUS_BARU = 'baru';

    // =======================
    // INDEX
    // =======================
    /**
     * Menampilkan daftar tagihan.
     *
     * Jika ada filter bulan & tahun → tampilkan semua tagihan sesuai periode.
     * Jika tanpa filter → tampilkan hanya TAGIHAN TERBARU PER SISWA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Query dasar untuk tagihan
        $baseQuery = Tagihan::with(['user', 'siswa', 'tagihanDetails']);

        // Filter hanya berdasarkan TAHUN (bulan dihapus)
        if ($request->filled('tahun')) {
            $baseQuery->whereYear('tanggal_tagihan', $request->tahun);

            // Ambil TAGIHAN TERBARU PER SISWA pada tahun tersebut
            $latestPerSiswa = Tagihan::select('siswa_id', DB::raw('MAX(id) as latest_id'))
                ->whereYear('tanggal_tagihan', $request->tahun)
                ->groupBy('siswa_id');
            $baseQuery->whereIn('id', $latestPerSiswa->pluck('latest_id'));
        } else {
            // Jika tidak ada filter tahun → ambil tagihan terbaru per siswa secara global
            $latestPerSiswa = Tagihan::select('siswa_id', DB::raw('MAX(id) as latest_id'))
                ->groupBy('siswa_id');
            $baseQuery->whereIn('id', $latestPerSiswa->pluck('latest_id'));
        }

        // Filter status tagihan (opsional: 'baru', 'lunas')
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }

        // Filter kelas
        if ($request->filled('kelas')) {
            $baseQuery->whereHas('siswa', fn($q) => $q->where('kelas', $request->kelas));
        }

        // Pencarian
        if ($request->filled('q')) {
            $baseQuery->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->q . '%')
                ->orWhere('nisn', 'like', '%' . $request->q . '%');
            });
        }

        // 💡 Salin query untuk statistik
        $statsQuery = clone $baseQuery;

        // === STATISTIK SAAT INI ===
        // Total siswa AKTIF (dari tabel siswa)
        $totalSiswaAktif = Siswa::where('status', 'aktif')->count();

        // Hitung jumlah SISWA UNIK yang sudah LUNAS (sesuai filter tahun)
        $totalLunasSiswa = $statsQuery
            ->where('status', 'lunas')
            ->distinct('siswa_id')
            ->count('siswa_id');

        // Belum bayar = total siswa aktif - yang sudah lunas
        $totalBelumBayar = $totalSiswaAktif - $totalLunasSiswa;

        // Persentase pembayaran
        $persentase = $totalSiswaAktif > 0 ? round(($totalLunasSiswa / $totalSiswaAktif) * 100, 1) : 0;

        // === STATISTIK PERIODE SEBELUMNYA (tahun lalu) ===
        $tahunSekarang = $request->filled('tahun') ? $request->tahun : now()->year;
        $tahunSebelumnya = $tahunSekarang - 1;

        // Query untuk tahun sebelumnya
        $prevQuery = Tagihan::query();

        // Ambil tagihan terbaru per siswa pada tahun sebelumnya
        $latestPrev = Tagihan::select('siswa_id', DB::raw('MAX(id) as latest_id'))
            ->whereYear('tanggal_tagihan', $tahunSebelumnya)
            ->groupBy('siswa_id');

        if ($latestPrev->exists()) {
            $prevQuery->whereIn('id', $latestPrev->pluck('latest_id'));
        } else {
            // Jika tidak ada data tahun lalu, biarkan kosong
            $prevQuery->whereRaw('1 = 0');
        }

        // Terapkan filter tambahan ke periode sebelumnya
        if ($request->filled('status')) {
            $prevQuery->where('status', $request->status);
        }
        if ($request->filled('kelas')) {
            $prevQuery->whereHas('siswa', fn($q) => $q->where('kelas', $request->kelas));
        }

        // Hitung statistik tahun lalu
        $lunasPrevSiswa = (clone $prevQuery)
            ->where('status', 'lunas')
            ->distinct('siswa_id')
            ->count('siswa_id');

        $belumPrevBayar = $totalSiswaAktif - $lunasPrevSiswa; // asumsi total siswa aktif tetap
        $persentasePrev = $totalSiswaAktif > 0 ? round(($lunasPrevSiswa / $totalSiswaAktif) * 100, 1) : 0;

        // === PAGINASI UNTUK TABEL ===
        $models = $baseQuery->latest()->paginate(50);

        return view($this->viewPath . $this->viewIndex, [
            'models'           => $models,
            'routePrefix'      => $this->routePrefix,
            'title'            => 'Data Tagihan',
            'totalSiswa'       => $totalSiswaAktif,
            'totalLunas'       => $totalLunasSiswa,
            'totalBelum'       => $totalBelumBayar,
            'persentase'       => $persentase,
            'totalSiswaPrev'   => $totalSiswaAktif,
            'lunasPrev'        => $lunasPrevSiswa,
            'belumPrev'        => $belumPrevBayar,
            'persentasePrev'   => $persentasePrev,
            'diffSiswa'        => 0,
            'diffLunas'        => $totalLunasSiswa - $lunasPrevSiswa,
            'diffBelum'        => $totalBelumBayar - $belumPrevBayar,
            'diffPersen'       => $persentase - $persentasePrev,
        ]);
    }

    // =======================
    // CREATE
    // =======================
    /**
     * Menampilkan form pembuatan tagihan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $siswa = Siswa::all();

        return view($this->viewPath . $this->viewForm, [
            'model'    => new Tagihan(),
            'method'   => 'POST',
            'route'    => $this->routePrefix . '.store',
            'button'   => 'SIMPAN',
            'title'    => 'Form Data Tagihan',
            'angkatan' => $siswa->pluck('angkatan', 'angkatan')->unique(),
            'kelas'    => $siswa->pluck('kelas', 'kelas')->unique(),
            'biaya'    => Biaya::get()->pluck('nama_biaya_full', 'id'),
        ]);
    }

    // =======================
    // STORE
    // =======================
    /**
     * Menyimpan tagihan baru (massal per siswa & biaya).
     *
     * @param  \App\Http\Requests\StoreTagihanRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTagihanRequest $request)
    {
        $data      = $request->validated();
        $biayaIds  = $data['biaya_id'] ?? [];
        $siswaList = $this->filterSiswa($data);
        $biayaList = Biaya::whereIn('id', $biayaIds)->get();

        $tanggalTagihan    = Carbon::parse($data['tanggal_tagihan']);
        $tanggalJatuhTempo = Carbon::parse($data['tanggal_jatuh_tempo']);
        $bulan             = $tanggalTagihan->format('m');
        $tahun             = $tanggalTagihan->format('Y');

        $jumlahTersimpan = 0;

        // Gunakan transaction untuk konsistensi data
        DB::transaction(function () use ($siswaList, $biayaList, $tanggalTagihan, $tanggalJatuhTempo, $bulan, $tahun, &$jumlahTersimpan) {
            foreach ($siswaList as $siswa) {
                foreach ($biayaList as $biaya) {
                    if ($this->cekDuplikat($siswa->id, $biaya->nama, $bulan, $tahun)) {
                        continue;
                    }

                    $tagihan = Tagihan::create([
                        'siswa_id'            => $siswa->id,
                        'angkatan'            => $siswa->angkatan,
                        'kelas'               => $siswa->kelas,
                        'tanggal_tagihan'     => $tanggalTagihan,
                        'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                        'keterangan'          => $data['keterangan'] ?? null,
                        'status'              => self::STATUS_BARU,
                    ]);

                    $tagihan->tagihanDetails()->create([
                        'nama_biaya'   => $biaya->nama,
                        'jumlah_biaya' => $biaya->jumlah,
                    ]);

                    $jumlahTersimpan++;
                }
            }
        });

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', "Tagihan berhasil dibuat: {$jumlahTersimpan} tagihan baru untuk " . count($siswaList) . " siswa.");
    }

    // =======================
    // SHOW
    // =======================
    /**
     * Menampilkan detail tagihan per siswa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, int $id)
    {
        $siswa = Siswa::findOrFail($id);

        // Ambil SEMUA tagihan milik siswa ini
        $tagihan = Tagihan::with('tagihanDetails')
            ->where('siswa_id', $siswa->id)
            ->orderBy('tanggal_tagihan', 'desc')
            ->get();

        // Ambil tagihan default (untuk form pembayaran)
        $tagihanDefault = $tagihan->firstWhere('status', '!=', 'lunas') ?? $tagihan->first();

        $pembayaran = Pembayaran::whereIn('tagihan_id', $tagihan->pluck('id'))
            ->with('user')
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        return view($this->viewPath . $this->viewShow, [
            'siswa'          => $siswa,
            'tagihan'        => $tagihan,
            'tagihanDefault' => $tagihanDefault,
            'pembayaran'     => $pembayaran,
            'title'          => "Daftar Tagihan {$siswa->nama}",
        ]);
    }

    // =======================
    // DESTROY (HAPUS SATU)
    // =======================
    /**
     * Menghapus satu tagihan beserta detailnya.
     *
     * @param  \App\Models\Tagihan  $tagihan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Tagihan $tagihan)
    {
        try {
            $siswa = $tagihan->siswa;
            $detailList   = $tagihan->tagihanDetails()->pluck('nama_biaya')->toArray();
            $jumlahDetail = count($detailList);
            $namaBiaya    = $jumlahDetail > 0 ? implode(', ', $detailList) : '—';

            $tagihan->tagihanDetails()->delete();
            $tagihan->delete();

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Tagihan siswa {$siswa->nama} ({$siswa->nisn}) berhasil dihapus ({$jumlahDetail} detail: {$namaBiaya})."
                );
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }

    // =======================
    // DESTROY SISWA (HAPUS SEMUA)
    // =======================
    /**
     * Menghapus SEMUA tagihan seorang siswa beserta detailnya.
     *
     * @param  int  $siswaId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroySiswa(int $siswaId)
    {
        try {
            $siswa = Siswa::findOrFail($siswaId);

            $tagihans = Tagihan::where('siswa_id', $siswaId)->get();
            $jumlahDetail = 0;
            $allBiaya = [];

            DB::transaction(function () use ($tagihans, &$jumlahDetail, &$allBiaya) {
                foreach ($tagihans as $tagihan) {
                    $detailList = $tagihan->tagihanDetails()->pluck('nama_biaya')->toArray();
                    $jumlahDetail += count($detailList);
                    $allBiaya = array_merge($allBiaya, $detailList);

                    $tagihan->tagihanDetails()->delete();
                    $tagihan->delete();
                }
            });

            $biayaText = $jumlahDetail > 0 ? implode(', ', $allBiaya) : '—';

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with(
                    'success',
                    "Semua tagihan siswa {$siswa->nama} ({$siswa->nisn}) berhasil dihapus ({$jumlahDetail} detail: {$biayaText})."
                );
        } catch (Exception $e) {
            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }

    // =======================
    // MASS DESTROY (HAPUS BANYAK → SEMUA TAGIHAN PER SISWA)
    // =======================
    /**
     * Menghapus SEMUA tagihan untuk setiap siswa yang terpilih.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tagihans,id',
        ]);

        // Ambil semua siswa_id unik dari tagihan yang dipilih
        $siswaIds = Tagihan::whereIn('id', $request->ids)
            ->pluck('siswa_id')
            ->unique()
            ->filter() // hapus null jika ada
            ->values()
            ->toArray();

        if (empty($siswaIds)) {
            return redirect()->back()->with('error', 'Tidak ada tagihan valid untuk dihapus.');
        }

        // Ambil data siswa untuk pesan flash
        $siswaList = Siswa::whereIn('id', $siswaIds)->get();
        $jumlahSiswa = $siswaList->count();

        DB::transaction(function () use ($siswaIds) {
            foreach ($siswaIds as $siswaId) {
                $tagihans = Tagihan::with('tagihanDetails')->where('siswa_id', $siswaId)->get();
                foreach ($tagihans as $tagihan) {
                    $tagihan->tagihanDetails()->delete();
                    $tagihan->delete();
                }
            }
        });

        // 🔥 Buat pesan flash yang informatif tapi rapi
        if ($jumlahSiswa === 1) {
            $siswa = $siswaList->first();
            $message = "Berhasil menghapus semua tagihan untuk <strong>{$siswa->nama} ({$siswa->kelas})</strong>.";
        } else {
            // Ambil maksimal 5 siswa pertama
            $tampilkan = $siswaList->take(5);
            $daftarSiswa = $tampilkan->map(fn($s) => "<li>{$s->nama} ({$s->kelas})</li>")->join('');

            if ($jumlahSiswa <= 5) {
                $message = "
                    <div class='flash-message-content'>
                        <p>Berhasil menghapus semua tagihan untuk <strong>{$jumlahSiswa} siswa</strong>:</p>
                        <ul class='flash-list'>{$daftarSiswa}</ul>
                    </div>
                ";
            } else {
                $sisanya = $jumlahSiswa - 5;
                $message = "
                    <div class='flash-message-content'>
                        <p>Berhasil menghapus semua tagihan untuk <strong>{$jumlahSiswa} siswa</strong>, di antaranya:</p>
                        <ul class='flash-list'>{$daftarSiswa}</ul>
                        <p>... dan <strong>{$sisanya} siswa lainnya</strong>.</p>
                    </div>
                ";
            }
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }
    /**
     * Hapus SATU tagihan dan detailnya.
     */
    public function destroySingle($id)
    {
        $tagihan = Tagihan::with('siswa')->find($id);

        if (!$tagihan) {
            return redirect()->back()->with('error', 'Tagihan tidak ditemukan atau sudah dihapus.');
        }

        $siswaNama = optional($tagihan->siswa)->nama ?? 'Siswa Tidak Diketahui';
        $periode = $tagihan->tanggal_tagihan
            ? \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->translatedFormat('M Y')
            : 'Tanpa Periode';

        DB::transaction(function () use ($tagihan) {
            $tagihan->tagihanDetails()->delete();
            $tagihan->delete();
        });

        return redirect()->back()->with('success', "Tagihan atas nama {$siswaNama} ({$periode}) berhasil dihapus.");
    }

    /**
     * Menandai tagihan sebagai LUNAS oleh operator.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tagihan  $tagihan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bayar(Request $request, Tagihan $tagihan)
    {
        $request->validate([
            'tanggal_bayar' => 'required|date',
        ]);

        $tanggalBayar = Carbon::parse($request->tanggal_bayar);

        // Simpan ke tabel pembayaran
        Pembayaran::create([
            'tagihan_id'       => $tagihan->id,
            'siswa_id'         => $tagihan->siswa_id,
            'wali_id'          => $tagihan->siswa->wali_id,
            'user_id'          => auth()->id(),
            'tanggal_bayar'    => $tanggalBayar,
            'tanggal_konfirmasi' => $tanggalBayar,
            'jumlah_dibayar'   => $tagihan->tagihanDetails->sum('jumlah_biaya'),
            'metode_pembayaran' => 'manual',
            'status_konfirmasi'=> 'sudah',
        ]);

        // Update status tagihan
        $tagihan->update(['status' => 'lunas']);

        return redirect()
            ->back()
            ->with('success', 'Tagihan berhasil ditandai sebagai LUNAS.');
    }

    /**
     * Membatalkan pembayaran tagihan (kembali ke BELUM BAYAR).
     */
    public function batal(Tagihan $tagihan)
    {
        if ($tagihan->status !== 'lunas') {
            return redirect()->back()->with('error', 'Tagihan ini belum lunas.');
        }

        DB::transaction(function () use ($tagihan) {

            // Hapus data pembayaran terkait tagihan ini
            Pembayaran::where('tagihan_id', $tagihan->id)->delete();

            // Kembalikan status tagihan
            $tagihan->update([
                'status' => 'baru',
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Pembayaran berhasil dibatalkan. Status kembali menjadi BELUM BAYAR.');
    }


    /**
     * Memfilter siswa berdasarkan kelas dan/atau angkatan.
     * Hanya mengambil siswa dengan status 'aktif'.
     *
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function filterSiswa(array $data): \Illuminate\Database\Eloquent\Collection
    {
        return Siswa::where('status', 'aktif') // 🔑 FILTER UTAMA: hanya siswa aktif
            ->when(!empty($data['kelas']), fn($q) => $q->where('kelas', $data['kelas']))
            ->when(!empty($data['angkatan']), fn($q) => $q->where('angkatan', $data['angkatan']))
            ->get();
    }

    /**
     * Mengecek duplikasi tagihan berdasarkan:
     * - Siswa
     * - Nama biaya
     * - Bulan & Tahun tagihan
     *
     * @param  int  $siswaId
     * @param  string  $namaBiaya
     * @param  string  $bulan
     * @param  string  $tahun
     * @return bool
     */
    private function cekDuplikat(int $siswaId, string $namaBiaya, string $bulan, string $tahun): bool
    {
        return Tagihan::where('siswa_id', $siswaId)
            ->whereMonth('tanggal_tagihan', $bulan)
            ->whereYear('tanggal_tagihan', $tahun)
            ->whereHas('tagihanDetails', fn($q) => $q->where('nama_biaya', $namaBiaya))
            ->exists();
    }

    /**
     * Export data tagihan ke format Excel/PDF
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export(Request $request)
    {
        // Untuk sementara, redirect ke index dulu
        flash('Fitur export akan segera tersedia')->info();
        return redirect()->route('tagihan.index');

        // Nanti bisa diisi dengan:
        // return Excel::download(new TagihanExport, 'tagihan.xlsx');
        // atau
        // return PDF::loadView('tagihan.export')->download('tagihan.pdf');
    }

    /**
     * Menampilkan halaman modul hapus massal
     */
    public function hapusMassalIndex()
    {
        $data['listBiaya'] = \App\Models\TagihanDetail::distinct()->pluck('nama_biaya');
        $data['listAngkatan'] = Tagihan::distinct()->pluck('angkatan');
        $data['listKelas'] = ['VII', 'VIII', 'IX']; // Dropdown statis
        $data['title'] = 'Hapus Tagihan Massal';
        $data['routePrefix'] = $this->routePrefix;

        return view($this->viewPath . 'tagihan_hapus_massal', $data);
    }

    /**
     * Menghitung jumlah data yang akan terhapus secara real-time (AJAX)
     */
    public function hapusMassalPreview(Request $request)
    {
        $query = Tagihan::where('status', 'baru'); // Safety: hanya status baru

        if ($request->filled('nama_biaya')) {
            $query->whereHas('tagihanDetails', function($q) use ($request) {
                $q->where('nama_biaya', $request->nama_biaya);
            });
        } else {
            return response()->json(['count' => 0]);
        }

        if ($request->filled('angkatan')) $query->where('angkatan', $request->angkatan);
        if ($request->filled('kelas')) $query->where('kelas', $request->kelas);

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Eksekusi penghapusan massal
     */
    public function hapusMassalProses(Request $request)
    {
        $request->validate(['nama_biaya' => 'required']);

        $query = Tagihan::where('status', 'baru');
        $query->whereHas('tagihanDetails', fn($q) => $q->where('nama_biaya', $request->nama_biaya));

        if ($request->filled('angkatan')) $query->where('angkatan', $request->angkatan);
        if ($request->filled('kelas')) $query->where('kelas', $request->kelas);

        $tagihans = $query->get();

        if ($tagihans->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan atau tagihan sudah lunas.');
        }

        try {
            DB::beginTransaction();
            $count = 0;
            foreach ($tagihans as $tagihan) {
                $tagihan->tagihanDetails()->delete(); // Hapus detail
                $tagihan->delete(); // Hapus utama
                $count++;
            }
            DB::commit();

            return redirect()->route('tagihan.index')->with('success', "Berhasil menghapus $count data tagihan {$request->nama_biaya}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
