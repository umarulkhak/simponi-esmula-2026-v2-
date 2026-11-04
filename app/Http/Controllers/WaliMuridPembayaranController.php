<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Tagihan;
use App\Models\WaliBank;
use App\Models\Pembayaran;
use App\Models\BankSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PembayaranNotification;

class WaliMuridPembayaranController extends Controller
{
    public function create(Request $request)
    {
        $tagihanIds = $request->input('tagihan_id', []);

        if (empty($tagihanIds)) {
            flash('Silakan pilih tagihan yang ingin dibayar.')->error();
            return redirect()->route('wali.tagihan.index');
        }

        // Ambil tagihan yang dipilih (milik wali ini)
        $tagihanSelected = Tagihan::waliSiswa()
            ->whereIn('id', $tagihanIds)
            ->with('tagihanDetails')
            ->get();

        if ($tagihanSelected->isEmpty()) {
            flash('Tagihan tidak ditemukan atau tidak berhak.')->error();
            return redirect()->route('wali.tagihan.index');
        }

        // Hitung total keseluruhan (untuk ditampilkan di form)
        $totalTagihan = $tagihanSelected->sum(function ($tagihan) {
            return $tagihan->tagihanDetails->sum('jumlah_biaya');
        });

        // Ambil data yang dibutuhkan oleh form
        $listWaliBank = WaliBank::where('wali_id', Auth::id())
            ->get()
            ->pluck('nama_bank_full', 'id');

        $listBank = Bank::pluck('nama_bank', 'id');

        $bankSekolahList = BankSekolah::all();
        $listBankSekolah = $bankSekolahList->pluck('nama_bank', 'id');

        return view('wali.pembayaran_form', compact(
            'tagihanSelected',
            'totalTagihan',
            'bankSekolahList',
            'listBankSekolah',
            'listWaliBank',
            'listBank'
        ));
    }

    public function store(Request $request)
    {
        $tagihanIds = $request->input('tagihan_id', []);
        if (empty($tagihanIds) || !is_array($tagihanIds)) {
            flash('Tagihan tidak valid.')->error();
            return back();
        }

        if (!$request->filled('bank_sekolah_id')) {
            flash('Silakan pilih rekening tujuan.')->error();
            return back();
        }

        $request->validate([
            'bank_sekolah_id' => 'required|exists:bank_sekolahs,id',
        ]);

        // Validasi input utama (bukti, tanggal)
        $request->validate([
            'tanggal_bayar' => 'required|date',
            'bukti_bayar'   => 'required|mimes:jpeg,png,jpg,gif,svg,pdf|max:5048',
        ]);

        // Validasi rekening pengirim
        if (!$request->filled('wali_bank_id') && !$request->filled('nomor_rekening')) {
            flash('Silakan pilih bank pengirim atau isi data rekening baru')->error();
            return back()->withInput();
        }

        if (!$request->filled('wali_bank_id')) {
            $request->validate([
                'bank_id'         => 'required|exists:banks,id',
                'nama_rekening'   => 'required|string|max:255',
                'nomor_rekening'  => 'required|string|max:50',
            ]);
        }

        // Ambil data rekening pengirim (sama untuk semua pembayaran)
        if ($request->filled('wali_bank_id')) {
            $waliBank = WaliBank::where('wali_id', Auth::id())
                ->findOrFail($request->wali_bank_id);
            $namaRek = $waliBank->nama_rekening;
            $nomorRek = $waliBank->nomor_rekening;
            $namaBank = $waliBank->nama_bank;
            $kodeBank = $waliBank->kode;
            $waliBankId = $waliBank->id;
        } else {
            $bank = Bank::findOrFail($request->bank_id);
            $namaRek = $request->nama_rekening;
            $nomorRek = $request->nomor_rekening;
            $namaBank = $bank->nama_bank;
            $kodeBank = $bank->sandi_bank;

            if ($request->filled('simpan_data_rekening')) {
                $waliBank = WaliBank::firstOrCreate(
                    ['wali_id' => Auth::id(), 'nomor_rekening' => $nomorRek],
                    ['nama_rekening' => $namaRek, 'nama_bank' => $namaBank, 'kode' => $kodeBank]
                );
                $waliBankId = $waliBank->id;
            } else {
                $waliBankId = null;
            }
        }

        $groupId = Str::uuid()->toString();
        $buktiBayar = null;

        DB::beginTransaction();
        try {
            $buktiBayar = $request->file('bukti_bayar')->store('public');
            if (!$buktiBayar) {
                throw new \RuntimeException('Bukti pembayaran gagal disimpan.');
            }

            $pembayaranPertama = null;

            foreach ($tagihanIds as $tagihanId) {
                // Ambil tagihan & subtotal
                $tagihan = Tagihan::waliSiswa()->findOrFail($tagihanId);
                $subtotal = $tagihan->tagihanDetails->sum('jumlah_biaya');

                // Cek duplikat per tagihan
                $duplikat = Pembayaran::where('tagihan_id', $tagihanId)
                    ->where('jumlah_dibayar', $subtotal)
                    ->where('status_konfirmasi', 'belum')
                    ->exists();

                if ($duplikat) {
                    throw new \Exception("Pembayaran untuk tagihan {$tagihanId} sudah tercatat dan menunggu konfirmasi.");
                }

                // Simpan pembayaran
                $pembayaran = Pembayaran::create([
                    'tagihan_id'              => $tagihanId,
                    'wali_id'                 => Auth::id(),
                    'wali_bank_id'            => $waliBankId,
                    'nama_rekening_pengirim'  => $namaRek,
                    'nomor_rekening_pengirim' => $nomorRek,
                    'nama_bank_pengirim'      => $namaBank,
                    'kode_bank_pengirim'      => $kodeBank,
                    'bank_sekolah_id'         => $request->bank_sekolah_id,
                    'tanggal_bayar'           => $request->tanggal_bayar,
                    'status_konfirmasi'       => 'belum',
                    'jumlah_dibayar'          => $subtotal,
                    'bukti_bayar'             => $buktiBayar,
                    'metode_pembayaran'       => 'transfer',
                    'user_id'                 => 0,
                    'group_id'                => $groupId,
                ]);

                if (!$pembayaranPertama) {
                    $pembayaranPertama = $pembayaran;
                }
            }

            // Kirim notifikasi (cukup 1x)
            $userOperator = User::where('akses', 'operator')->get();
            Notification::send($userOperator, new PembayaranNotification($pembayaranPertama));

            DB::commit();

            flash('Berhasil! Pembayaran telah tercatat dan akan segera diverifikasi oleh operator sekolah.')->success();
            return redirect()->route('wali.tagihan.index');

        } catch (\Throwable $th) {
            DB::rollBack();
            if ($buktiBayar) {
                Storage::delete($buktiBayar);
            }
            flash('Gagal menyimpan data: ' . $th->getMessage())->error();
            return back();
        }
    }
}
