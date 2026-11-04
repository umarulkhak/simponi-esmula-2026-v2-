<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BerandaWaliController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Jumlah anak (siswa yang dimiliki wali)
        $jumlahAnak = $user->siswa()->count();

        // Hitung tagihan yang BELUM LUNAS
        $tagihanBelumLunas = Cache::remember("tagihan_belum_lunas_wali_{$userId}", now()->addMinutes(1), function () use ($user) {
            $siswaIds = $user->siswa()->pluck('id');
            if ($siswaIds->isEmpty()) {
                return 0;
            }

            $tagihanList = Tagihan::whereIn('siswa_id', $siswaIds)
                ->with(['tagihanDetails', 'pembayaran'])
                ->get();

            $belumLunasCount = 0;
            foreach ($tagihanList as $tagihan) {
                $totalTagihan = $tagihan->tagihanDetails->sum('jumlah_biaya');
                $totalDibayarDikonfirmasi = $tagihan->pembayaran
                    ->where('status_konfirmasi', 'sudah')
                    ->sum('jumlah_dibayar');

                if ($totalDibayarDikonfirmasi < $totalTagihan) {
                    $belumLunasCount++;
                }
            }

            return $belumLunasCount;
        });

        // Riwayat pembayaran terbaru — ambil via siswa (lebih akurat)
        $riwayatPembayaran = Cache::remember("riwayat_pembayaran_wali_{$userId}", now()->addMinutes(1), function () use ($user) {
            $siswaIds = $user->siswa()->pluck('id');
            if ($siswaIds->isEmpty()) {
                return collect();
            }

            $tagihanIds = Tagihan::whereIn('siswa_id', $siswaIds)->pluck('id');
            if ($tagihanIds->isEmpty()) {
                return collect();
            }

            return Pembayaran::whereIn('tagihan_id', $tagihanIds)
                ->where('status_konfirmasi', 'sudah')
                ->with(['tagihan.siswa'])
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
        });

        return view('wali.beranda_index', compact(
            'jumlahAnak',
            'tagihanBelumLunas',
            'riwayatPembayaran'
        ));
    }
}
