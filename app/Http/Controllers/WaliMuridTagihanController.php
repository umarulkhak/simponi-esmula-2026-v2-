<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\BankSekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WaliMuridTagihanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->siswa || $user->siswa->isEmpty()) {
            return view('wali.tagihan_index', [
                'tagihanGrouped' => collect(),
            ])->with('info', 'Anda belum memiliki siswa terdaftar.');
        }

        $tagihan = Tagihan::waliSiswa()
            ->orderBy('tanggal_tagihan', 'desc')
            ->get();

        $tagihanGrouped = $tagihan->groupBy('siswa_id');

        return view('wali.tagihan_index', compact('tagihanGrouped'));
    }

    // public function show($siswaId)
    // {
    //     try {
    //         $user = Auth::user();
    //         $userSiswaIds = $user->getAllSiswaId();

    //         if (!in_array($siswaId, $userSiswaIds)) {
    //             abort(403, 'Anda tidak memiliki akses ke data ini.');
    //         }

    //         // ✅ Load SEMUA pembayaran (tanpa filter status_konfirmasi)
    //         $tagihanList = Tagihan::with([
    //                 'siswa',
    //                 'tagihanDetails',
    //                 'pembayaran' // 👈 Ambil SEMUA pembayaran
    //             ])
    //             ->waliSiswa()
    //             ->where('siswa_id', $siswaId)
    //             ->orderBy('tanggal_tagihan', 'desc')
    //             ->get();

    //         if ($tagihanList->isEmpty()) {
    //             return redirect()->route('wali.tagihan.index')
    //                 ->with('error', 'Tidak ada tagihan ditemukan untuk siswa ini.');
    //         }

    //         $siswa = $tagihanList->first()->siswa;
    //         $banksekolah = BankSekolah::all();
    //         $listbank = \App\Models\Bank::pluck('nama_bank', 'id');

    //         // Hitung total tagihan
    //         $grandTotal = $tagihanList->sum(fn($t) => $t->tagihanDetails->sum('jumlah_biaya'));

    //         // ✅ Hitung total yang SUDAH DIKONFIRMASI (status_konfirmasi = 'sudah')
    //         $totalDibayarDikonfirmasi = $tagihanList->sum(function ($tagihan) {
    //             return $tagihan->pembayaran
    //                 ->where('status_konfirmasi', 'sudah')
    //                 ->sum('jumlah_dibayar');
    //         });

    //         $statusGlobal = $this->determinePaymentStatus($grandTotal, $totalDibayarDikonfirmasi);
    //         $semuaDikonfirmasi = ($statusGlobal === 'lunas');

    //         return view('wali.tagihan_show', compact(
    //             'tagihanList',
    //             'siswa',
    //             'banksekolah',
    //             'grandTotal',
    //             'totalDibayarDikonfirmasi',
    //             'statusGlobal',
    //             'listbank',
    //             'semuaDikonfirmasi'
    //         ));

    //     } catch (\Exception $e) {
    //         Log::error('Error loading tagihan siswa: ' . $e->getMessage(), [
    //             'user_id' => Auth::id(),
    //             'siswa_id' => $siswaId,
    //             'exception' => $e,
    //         ]);

    //         return redirect()->route('wali.tagihan.index')
    //             ->with('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
    //     }
    // }

    /**
     * Update the show method to use pagination for tagihan list.
     */
    public function show($siswaId)
    {
        $user = Auth::user();
        $userSiswaIds = $user->getAllSiswaId();

        if (!in_array($siswaId, $userSiswaIds)) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        try {
            // 1. Buat Query Dasar (Base Query)
            // Kita siapkan query-nya dulu tapi jangan di-get() atau paginate() dulu
            $query = Tagihan::with([
                    'siswa',
                    'tagihanDetails',
                    'pembayaran'
                ])
                ->waliSiswa()
                ->where('siswa_id', $siswaId)
                ->orderBy('tanggal_tagihan', 'desc');

            // 2. Ambil SEMUA data untuk perhitungan Total & Status (Agar banner merah angkanya BENAR)
            $allTagihan = $query->get();

            if ($allTagihan->isEmpty()) {
                return redirect()->route('wali.tagihan.index')
                    ->with('error', 'Tidak ada tagihan ditemukan untuk siswa ini.');
            }

            // 3. Ambil data PAGINATION untuk Tabel (10 per halaman)
            // Kita gunakan $query yang sama, otomatis Laravel akan memproses limit/offset
            $tagihanList = $query->paginate(10);

            // Data pendukung lainnya
            $siswa = $allTagihan->first()->siswa; // Ambil dari allTagihan biar aman
            $banksekolah = BankSekolah::all();
            $listbank = \App\Models\Bank::pluck('nama_bank', 'id');

            // 4. Hitung Total menggunakan $allTagihan (BUKAN $tagihanList yang cuma 10 biji)
            $grandTotal = $allTagihan->sum(fn($t) => $t->tagihanDetails->sum('jumlah_biaya'));

            // Hitung total yang SUDAH DIKONFIRMASI
            $totalDibayarDikonfirmasi = $allTagihan->sum(function ($tagihan) {
                return $tagihan->pembayaran
                    ->where('status_konfirmasi', 'sudah')
                    ->sum('jumlah_dibayar');
            });

            $statusGlobal = $this->determinePaymentStatus($grandTotal, $totalDibayarDikonfirmasi);
            $semuaDikonfirmasi = ($statusGlobal === 'lunas');

            return view('wali.tagihan_show', compact(
                'tagihanList', // Ini objek Pagination
                'siswa',
                'banksekolah',
                'grandTotal',
                'totalDibayarDikonfirmasi',
                'statusGlobal',
                'listbank',
                'semuaDikonfirmasi'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading tagihan siswa: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'siswa_id' => $siswaId,
                'exception' => $e,
            ]);

            return redirect()->route('wali.tagihan.index')
                ->with('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    private function determinePaymentStatus(float $grandTotal, float $totalDibayar): string
    {
        if ($totalDibayar >= $grandTotal) {
            return 'lunas';
        } elseif ($totalDibayar > 0) {
            return 'angsur';
        }
        return 'belum_bayar';
    }

    public function cetakInvoice(Siswa $siswa)
    {
        if (!$siswa->wali_id || $siswa->wali_id !== auth()->id()) {
            abort(403, 'Tidak diizinkan mengakses data ini.');
        }

        $tagihanList = $siswa->tagihan()
            ->with(['tagihanDetails', 'pembayaran'])
            ->get();

        $grandTotal = $tagihanList->sum(fn($t) => $t->tagihanDetails->sum('jumlah_biaya'));

        $semuaDikonfirmasi = true;
        foreach ($tagihanList as $tagihan) {
            $totalDibayar = $tagihan->pembayaran
                ->where('status_konfirmasi', 'sudah')
                ->sum('jumlah_dibayar');
            $subtotal = $tagihan->tagihanDetails->sum('jumlah_biaya');
            if ($totalDibayar < $subtotal) {
                $semuaDikonfirmasi = false;
                break;
            }
        }

        $banksekolah = BankSekolah::all();
        $now = now();
        $invNumber = "INV/ESMULA/{$now->year}/" . str_pad($now->month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($now->day, 2, '0', STR_PAD_LEFT);

        // Generate QR Code (tanpa size() — biar default)
        $qrData = "NISN:{$siswa->nisn}|INV:{$invNumber}|TGL:" . now()->format('Y-m-d');
        /** @var \SimpleSoftwareIO\QrCode\Generator $qr */
        $qr = QrCode::format('png');
        $qrCode = $qr->generate($qrData);
        $qrCodeBase64 = base64_encode($qrCode);

        // Load logo (hardcode jika perlu)
        $logoPath = public_path('sneat/assets/img/logo-esmula.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        } else {
            $logoBase64 = 'image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" fill="#f0f0f0"/><text x="40" y="45" font-size="10" text-anchor="middle" fill="#999">LOGO</text></svg>');
        }

        $pdf = Pdf::loadView('wali.invoice-pdf', compact(
            'siswa',
            'tagihanList',
            'banksekolah',
            'grandTotal',
            'semuaDikonfirmasi',
            'invNumber',
            'qrCodeBase64',
            'logoBase64'
        ));

        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("invoice-{$siswa->nisn}.pdf");
    }
}
