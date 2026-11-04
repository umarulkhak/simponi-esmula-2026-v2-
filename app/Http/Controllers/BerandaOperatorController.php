<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\TagihanSiswaExport;
use Maatwebsite\Excel\Facades\Excel;

class BerandaOperatorController extends Controller
{
    public function index()
    {
        $tahun = request('tahun', Carbon::now()->format('Y'));

        $stats = $this->calculateStats($tahun);
        $kelasStats = $this->getKelasStatsOptimized($tahun);
        $recentPayments = $this->getRecentPaymentsOptimized();

        return view('operator.beranda_index', [
            'title' => 'Dashboard Operator',
            'stats' => $stats,
            'kelasStats' => $kelasStats,
            'recentPayments' => $recentPayments,
        ]);
    }

    private function calculateStats($tahun)
    {
        // === TOTAL SISWA AKTIF (basis semua perhitungan) ===
        $totalSiswaAktif = Siswa::where('status', 'aktif')->count();

        // === QUERY: Tagihan terbaru per siswa pada TAHUN SEKARANG ===
        $latestPerSiswaSekarang = Tagihan::select('siswa_id', DB::raw('MAX(id) as latest_id'))
            ->whereYear('tanggal_tagihan', $tahun)
            ->groupBy('siswa_id');

        // Hitung jumlah SISWA UNIK yang LUNAS (berdasarkan tagihan terbaru mereka)
        $totalLunasSiswa = 0;
        if ($latestPerSiswaSekarang->exists()) {
            $totalLunasSiswa = Tagihan::whereIn('id', $latestPerSiswaSekarang->pluck('latest_id'))
                ->where('status', 'lunas')
                ->distinct('siswa_id')
                ->count('siswa_id');
        }

        $totalBelumBayar = $totalSiswaAktif - $totalLunasSiswa;
        $persentase = $totalSiswaAktif > 0 ? round(($totalLunasSiswa / $totalSiswaAktif) * 100, 1) : 0;

        // === PERIODE SEBELUMNYA: TAHUN LALU ===
        $tahunSebelumnya = $tahun - 1;

        $latestPerSiswaSebelumnya = Tagihan::select('siswa_id', DB::raw('MAX(id) as latest_id'))
            ->whereYear('tanggal_tagihan', $tahunSebelumnya)
            ->groupBy('siswa_id');

        $lunasPrevSiswa = 0;
        if ($latestPerSiswaSebelumnya->exists()) {
            $lunasPrevSiswa = Tagihan::whereIn('id', $latestPerSiswaSebelumnya->pluck('latest_id'))
                ->where('status', 'lunas')
                ->distinct('siswa_id')
                ->count('siswa_id');
        }

        $belumPrevBayar = $totalSiswaAktif - $lunasPrevSiswa;
        $persentasePrev = $totalSiswaAktif > 0 ? round(($lunasPrevSiswa / $totalSiswaAktif) * 100, 1) : 0;

        return [
            'total_siswa' => $totalSiswaAktif,
            'siswa_baru' => 0,
            'pembayaran_bulan_ini' => 0, // opsional, tidak dipakai di logika per-siswa
            'pertumbuhan_bulan_lalu' => 0,
            'tingkat_pembayaran' => $persentase,
            'peningkatan_bulan_lalu' => $persentase - $persentasePrev,
            'tunggakan' => $totalBelumBayar,
            'tagihan_belum_bayar' => $totalBelumBayar,
            'total_lunas' => $totalLunasSiswa,
            'total_pending' => 0,
            'total_belum_bayar' => $totalBelumBayar,

            // === DATA PERBANDINGAN (SESUAI TAGIHAN_CONTROLLER) ===
            'diff_siswa' => 0,
            'diff_belum' => $totalBelumBayar - $belumPrevBayar,
            'diff_persen' => $persentase - $persentasePrev,
        ];
    }

    private function getKelasStatsOptimized($tahun)
    {
        // Ambil tagihan terbaru per siswa pada tahun ini
        $latestTagihan = Tagihan::select('siswa_id', DB::raw('MAX(id) as latest_id'))
            ->whereYear('tanggal_tagihan', $tahun)
            ->groupBy('siswa_id')
            ->pluck('latest_id');

        // Jika tidak ada tagihan, buat array kosong
        $latestTagihan = $latestTagihan->isNotEmpty() ? $latestTagihan : collect([0]);

        // Ambil data per kelas dengan join ke tagihan terbaru
        $actual = DB::table('siswas')
            ->select('siswas.kelas')
            ->selectRaw('COUNT(*) as jumlah_siswa')
            ->selectRaw('SUM(CASE WHEN t.status = "lunas" THEN 1 ELSE 0 END) as lunas')
            ->selectRaw('SUM(CASE WHEN t.status = "baru" THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN t.status NOT IN ("lunas", "baru") AND t.status IS NOT NULL THEN 1 ELSE 0 END) as belum_bayar')
            ->leftJoin('tagihans as t', function ($join) use ($latestTagihan) {
                $join->on('siswas.id', '=', 't.siswa_id')
                    ->whereIn('t.id', $latestTagihan);
            })
            ->where('siswas.status', 'aktif')
            ->groupBy('siswas.kelas')
            ->orderBy('siswas.kelas')
            ->get()
            ->keyBy('kelas');

        $kelasAll = ['VII', 'VIII', 'IX'];
        $result = [];

        foreach ($kelasAll as $kelas) {
            if (isset($actual[$kelas])) {
                $data = $actual[$kelas];
                $result[] = [
                    'nama' => $kelas,
                    'jumlah_siswa' => (int) $data->jumlah_siswa,
                    'lunas' => (int) $data->lunas,
                    'pending' => (int) $data->pending,
                    'belum_bayar' => (int) $data->belum_bayar,
                ];
            } else {
                $jumlah = Siswa::where('kelas', $kelas)->where('status', 'aktif')->count();
                $result[] = [
                    'nama' => $kelas,
                    'jumlah_siswa' => $jumlah,
                    'lunas' => 0,
                    'pending' => 0,
                    'belum_bayar' => 0,
                ];
            }
        }

        return $result;
    }

    private function getRecentPaymentsOptimized()
    {
        $payments = DB::table('tagihans as t')
            ->select(
                't.id',
                't.siswa_id',
                't.status',
                't.tanggal_tagihan',
                't.created_at',
                DB::raw('SUM(td.jumlah_biaya) as total_biaya')
            )
            ->join('tagihan_details as td', 't.id', '=', 'td.tagihan_id')
            ->where('t.status', 'lunas')
            ->groupBy('t.id', 't.siswa_id', 't.status', 't.tanggal_tagihan', 't.created_at')
            ->orderBy('t.created_at', 'desc')
            ->take(5)
            ->get();

        $siswaIds = $payments->pluck('siswa_id')->unique()->toArray();
        $siswas = Siswa::whereIn('id', $siswaIds)->get()->keyBy('id');

        foreach ($payments as $payment) {
            $payment->siswa = $siswas[$payment->siswa_id] ?? null;
        }

        return $payments;
    }

    public function exportTagihanSiswa()
    {
        return Excel::download(new TagihanSiswaExport(), 'laporan_tagihan_siswa_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }
}
