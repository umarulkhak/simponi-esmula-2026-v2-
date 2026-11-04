<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\BankSekolah;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Requests\StorePembayaranRequest;

class PembayaranController extends Controller
{
    public function index()
    {
        $today = now();
        $income = DB::table('pembayarans')
            ->whereNull('deleted_at')
            ->where('status_konfirmasi', 'sudah')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN DATE(tanggal_bayar) = ? THEN jumlah_dibayar ELSE 0 END), 0) as uangMasukHariIni,
                 COALESCE(SUM(CASE WHEN MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ? THEN jumlah_dibayar ELSE 0 END), 0) as uangMasukBulanIni,
                 COALESCE(SUM(CASE WHEN YEAR(tanggal_bayar) = ? THEN jumlah_dibayar ELSE 0 END), 0) as uangMasukTahunIni,
                 COALESCE(SUM(jumlah_dibayar), 0) as totalUangMasuk',
                [$today->toDateString(), $today->month, $today->year, $today->year]
            )
            ->first();

        $query = DB::table('pembayarans as p')
            ->select(
                DB::raw('MAX(p.id) as id'),
                's.id as siswa_id',
                's.nama as nama_siswa',
                's.nisn',
                's.kelas',
                's.angkatan',
                'u.name as nama_wali',
                DB::raw('SUM(p.jumlah_dibayar) as total_dibayar'),
                DB::raw('MAX(p.tanggal_konfirmasi) as tanggal_konfirmasi'),
                DB::raw('MIN(p.status_konfirmasi) as status_konfirmasi')
            )
            ->join('tagihans as t', 'p.tagihan_id', '=', 't.id')
            ->join('siswas as s', 't.siswa_id', '=', 's.id')
            ->leftJoin('users as u', 'p.wali_id', '=', 'u.id')
            ->groupBy(
                's.id',
                's.nama',
                's.nisn',
                's.kelas',
                's.angkatan',
                'p.wali_id'
            );

        // Filter Bulan
        if (request('bulan')) {
            $query->whereRaw('MONTH(p.tanggal_bayar) = ?', [request('bulan')]);
        }

        // Filter Tahun
        if (request('tahun')) {
            $query->whereRaw('YEAR(p.tanggal_bayar) = ?', [request('tahun')]);
        }

        // Filter Status Konfirmasi
        if (request('status')) {
            if (request('status') === 'belum') {
                $query->where('p.status_konfirmasi', 'belum');
            } elseif (request('status') === 'sudah') {
                $query->where('p.status_konfirmasi', 'sudah');
            }
        }

        // Filter Kelas
        if (request('kelas')) {
            $query->where('s.kelas', request('kelas'));
        }

        // Filter Cari Siswa
        if (request('q')) {
            $query->where(function ($q) {
                $q->where('s.nama', 'like', '%' . request('q') . '%')
                ->orWhere('s.nisn', 'like', '%' . request('q') . '%');
            });
        }

        $results = $query->orderBy('tanggal_konfirmasi', 'desc')->paginate(50);
        return view('operator.pembayaran_index', [
            'results' => $results,
            'uangMasukHariIni' => $income->uangMasukHariIni,
            'uangMasukBulanIni' => $income->uangMasukBulanIni,
            'uangMasukTahunIni' => $income->uangMasukTahunIni,
            'totalUangMasuk' => $income->totalUangMasuk,
        ]);
    }

    public function store(StorePembayaranRequest $request)
    {
        $requestData = $request->validated();
        $tagihanUtama = Tagihan::findOrFail($requestData['tagihan_id']);
        $siswaId = $tagihanUtama->siswa_id;
        $requestData['wali_id'] = $tagihanUtama->siswa->wali_id;

        $tagihanBelumLunas = Tagihan::where('siswa_id', $siswaId)
            ->where('status', '!=', 'lunas')
            ->with('tagihanDetails')
            ->orderBy('created_at')
            ->get();

        $jumlahDibayar = $requestData['jumlah_dibayar'];
        $sisaUang = $jumlahDibayar;

        if ($jumlahDibayar <= 0) {
            flash('Jumlah pembayaran harus lebih dari Rp0.')->error();
            return back()->withInput();
        }

        $pembayaranList = [];
        $tagihanYangDibayar = [];

        $tagihanPas = $tagihanBelumLunas->first(function ($tagihan) use ($jumlahDibayar) {
            return $tagihan->tagihanDetails->sum('jumlah_biaya') == $jumlahDibayar;
        });

        if ($tagihanPas) {
            $pembayaranList[] = [
                'tagihan_id' => $tagihanPas->id,
                'jumlah_dibayar' => $jumlahDibayar,
                'tanggal_bayar' => $requestData['tanggal_bayar'],
                'tanggal_konfirmasi' => now(),
                'status_konfirmasi' => 'sudah',
                'metode_pembayaran' => 'manual',
                'user_id' => auth()->id(),
                'wali_id' => $requestData['wali_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $tagihanPas->status = 'lunas';
            $tagihanPas->save();
            $tagihanYangDibayar[] = "Tagihan '{$tagihanPas->keterangan}' (Rp" . number_format($jumlahDibayar, 0, ',', '.') . ")";
            $sisaUang = 0;
        } else {
            foreach ($tagihanBelumLunas as $tagihan) {
                if ($sisaUang <= 0) break;
                $totalTagihan = $tagihan->tagihanDetails->sum('jumlah_biaya');

                if ($sisaUang >= $totalTagihan) {
                    $pembayaranList[] = [
                        'tagihan_id' => $tagihan->id,
                        'jumlah_dibayar' => $totalTagihan,
                        'tanggal_bayar' => $requestData['tanggal_bayar'],
                        'status_konfirmasi' => 'sudah',
                        'metode_pembayaran' => 'manual',
                        'user_id' => auth()->id(),
                        'wali_id' => $requestData['wali_id'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $tagihan->status = 'lunas';
                    $tagihan->save();
                    $tagihanYangDibayar[] = "Tagihan '{$tagihan->keterangan}' (Rp" . number_format($totalTagihan, 0, ',', '.') . ")";
                    $sisaUang -= $totalTagihan;
                } else {
                    $pembayaranList[] = [
                        'tagihan_id' => $tagihan->id,
                        'jumlah_dibayar' => $sisaUang,
                        'tanggal_bayar' => $requestData['tanggal_bayar'],
                        'status_konfirmasi' => 'sudah',
                        'metode_pembayaran' => 'manual',
                        'user_id' => auth()->id(),
                        'wali_id' => $requestData['wali_id'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $tagihan->status = 'angsur';
                    $tagihan->save();
                    $tagihanYangDibayar[] = "Tagihan '{$tagihan->keterangan}' (sebagian: Rp" . number_format($sisaUang, 0, ',', '.') . ")";
                    $sisaUang = 0;
                    break;
                }
            }
        }

        if (empty($pembayaranList)) {
            flash('Tidak ada tagihan yang bisa dibayar dengan jumlah tersebut.')->warning();
            return back()->withInput();
        }

        DB::transaction(function () use ($pembayaranList) {
            foreach ($pembayaranList as $data) {
                Pembayaran::create($data);
            }
        });

        $pesan = "Pembayaran berhasil: " . implode(', ', $tagihanYangDibayar) . ".";
        flash($pesan)->success();
        return back();
    }

    public function show(Pembayaran $pembayaran)
    {
        if (auth()->check()) {
            auth()->user()
                ->unreadNotifications
                ->where('id', request('id'))
                ->first()
                ?->markAsRead();
        }

        $siswa = $pembayaran->tagihan?->siswa;

        if (!$siswa) {
            flash('Data siswa tidak ditemukan.')->error();
            return redirect()->route('pembayaran.index');
        }

        // Ambil SEMUA pembayaran dari siswa ini
        $pembayaranGroup = Pembayaran::whereHas('tagihan', function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
            ->with([
                'tagihan',
                'tagihan.siswa',
                'tagihan.tagihanDetails',
                'bankSekolah',
                'waliBank'
            ])
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        $totalDibayar = $pembayaranGroup
            ->where('status_konfirmasi', 'sudah')
            ->sum('jumlah_dibayar');

        // Hitung total tagihan & status di controller (hindari N+1 di view)
        $tagihanSiswa = Tagihan::with('tagihanDetails')
            ->where('siswa_id', $siswa->id)
            ->get();

        $totalTagihan = $tagihanSiswa->sum(function ($tagihan) {
            return $tagihan->tagihanDetails->sum('jumlah_biaya');
        });

        $totalTagihanSiswa = $tagihanSiswa->count();
        $statusPembayaran = 'Belum Bayar';
        if ($totalTagihanSiswa > 0 && $totalDibayar >= $totalTagihan) {
            $statusPembayaran = 'Lunas';
        } elseif ($totalDibayar > 0) {
            $statusPembayaran = 'Angsur';
        }

        return view('operator.pembayaran_show', [
            'model' => $pembayaran,
            'pembayaranGroup' => $pembayaranGroup,
            'siswa' => $siswa,
            'totalDibayar' => $totalDibayar,
            'totalTagihan' => $totalTagihan,
            'statusPembayaran' => $statusPembayaran,
            'route' => ['pembayaran.update.multiple'],
        ]);
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        flash('Gunakan fitur konfirmasi selektif.')->warning();
        return back();
    }

    public function updateMultiple(Request $request)
    {
        $request->validate([
            'pembayaran_ids' => 'required|array|min:1',
            'pembayaran_ids.*' => 'exists:pembayarans,id'
        ]);

        $pembayaranList = Pembayaran::with([
            'tagihan.siswa',
            'tagihan.tagihanDetails',
        ])->whereIn('id', $request->pembayaran_ids)->get();

        $yangDikonfirmasi = $pembayaranList->filter(fn($p) => $p->status_konfirmasi === 'belum');

        if ($yangDikonfirmasi->isEmpty()) {
            flash('Tidak ada pembayaran baru yang perlu dikonfirmasi.')->warning();
            return back();
        }

        DB::transaction(function () use ($yangDikonfirmasi) {
            foreach ($yangDikonfirmasi as $pembayaran) {
                $pembayaran->status_konfirmasi = 'sudah';
                $pembayaran->tanggal_konfirmasi = now();
                $pembayaran->user_id = auth()->id();
                $pembayaran->save();

                if ($pembayaran->tagihan) {
                    $pembayaran->tagihan->status = 'lunas';
                    $pembayaran->tagihan->save();
                }
            }
        });

        // Bangun pesan informatif
        $jumlah = $yangDikonfirmasi->count();
        $totalNominal = $yangDikonfirmasi->sum('jumlah_dibayar');
        $daftarSiswa = $yangDikonfirmasi
            ->map(fn($p) => optional($p->tagihan)->siswa?->nama ?? 'Siswa Tidak Diketahui')
            ->unique()
            ->values();

        $namaSiswa = $daftarSiswa->count() === 1
            ? $daftarSiswa[0]
            : $daftarSiswa->count() . ' siswa';

        $daftarBiaya = $yangDikonfirmasi
            ->flatMap(fn($p) => optional($p->tagihan)?->tagihanDetails?->pluck('nama_biaya') ?? collect([]))
            ->unique()
            ->take(5)
            ->implode(', ');

        $sisaBiaya = $yangDikonfirmasi->flatMap(fn($p) => optional($p->tagihan)?->tagihanDetails?->pluck('nama_biaya') ?? collect([]))->count() > 5
            ? ' + beberapa lainnya'
            : '';

        $pesan = "Berhasil mengkonfirmasi {$jumlah} pembayaran dari {$namaSiswa} "
               . "untuk biaya: {$daftarBiaya}{$sisaBiaya}. "
               . "Total nominal: " . formatRupiah($totalNominal) . ".";

        flash($pesan)->success();
        return back();
    }

    /**
     * Hapus SEMUA pembayaran milik siswa dari 1 baris di index.
     */
    public function destroy(Pembayaran $pembayaran)
    {
        $siswa = $pembayaran->tagihan?->siswa;

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $namaSiswa = $siswa->nama;

        DB::transaction(function () use ($siswa) {
            // Hapus semua pembayaran milik siswa ini
            Pembayaran::whereHas('tagihan', function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })->delete();

            // Kembalikan semua tagihan siswa ke status 'baru'
            Tagihan::where('siswa_id', $siswa->id)->update(['status' => 'baru']);
        });

        return redirect()->back()->with('success', "Semua pembayaran atas nama {$namaSiswa} berhasil dihapus.");
    }

    /**
     * Hapus SEMUA pembayaran untuk beberapa siswa (massal).
     */
    public function massDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        // Ambil ID siswa unik dari pembayaran yang dipilih
        $siswaIds = DB::table('pembayarans as p')
            ->join('tagihans as t', 'p.tagihan_id', '=', 't.id')
            ->whereIn('p.id', $ids)
            ->pluck('t.siswa_id')
            ->unique();

        if ($siswaIds->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data valid untuk dihapus.');
        }

        DB::transaction(function () use ($siswaIds) {
            // Hapus semua pembayaran milik siswa-siswa ini
            Pembayaran::whereHas('tagihan', function ($q) use ($siswaIds) {
                $q->whereIn('siswa_id', $siswaIds);
            })->delete();

            // Kembalikan semua tagihan mereka ke 'baru'
            Tagihan::whereIn('siswa_id', $siswaIds)->update(['status' => 'baru']);
        });

        return redirect()->back()->with('success', 'Berhasil menghapus semua pembayaran untuk ' . $siswaIds->count() . ' siswa.');
    }
        /**
     * Hapus SATU pembayaran dan kembalikan status tagihannya ke 'baru'.
     */
    public function destroySingle($id)
    {
        $pembayaran = Pembayaran::with('tagihan.siswa')->find($id);

        if (!$pembayaran) {
            return redirect()->route('pembayaran.index')->with('error', 'Pembayaran tidak ditemukan atau sudah dihapus.');
        }

        $siswaNama = optional($pembayaran->tagihan?->siswa)->nama ?? 'Siswa Tidak Diketahui';
        $periode = $pembayaran->tagihan?->tanggal_tagihan
            ? \Carbon\Carbon::parse($pembayaran->tagihan->tanggal_tagihan)->translatedFormat('M Y')
            : 'Tanpa Periode';

        DB::transaction(function () use ($pembayaran) {
            $tagihan = $pembayaran->tagihan;
            $pembayaran->delete();

            if ($tagihan) {
                $tagihan->status = 'baru';
                $tagihan->save();
            }
        });

        return redirect()->route('pembayaran.index')->with('success', "Pembayaran atas nama {$siswaNama} ({$periode}) berhasil dihapus.");
    }
    public function cetakInvoiceOperator(Siswa $siswa)
    {
        // Hanya operator yang boleh akses
        if (!auth()->check() || !in_array(auth()->user()->akses, ['operator', 'admin'], true)) {
            abort(403, 'Hanya operator sekolah yang dapat mencetak invoice.');
        }

        $tagihanList = $siswa->tagihan()
            ->with(['tagihanDetails', 'pembayaran'])
            ->get();

        // Hitung total dan cek kelengkapan pembayaran
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

        // Hanya izinkan cetak jika semua lunas & dikonfirmasi
        if (!$semuaDikonfirmasi) {
            return redirect()->back()->with('error', 'Invoice hanya dapat dicetak setelah semua tagihan lunas dan dikonfirmasi.');
        }

        // Data pendukung
        $banksekolah = BankSekolah::all();
        $now = now();
        $invNumber = "INV/ESMULA/{$now->year}/" . str_pad($now->month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($now->day, 2, '0', STR_PAD_LEFT);

        // QR Code
        $qrData = "NISN:{$siswa->nisn}|INV:{$invNumber}|TGL:" . now()->format('Y-m-d');
        $qrCodeBase64 = base64_encode(QrCode::format('png')->generate($qrData));

        // Logo
        $logoPath = public_path('sneat/assets/img/logo-esmula.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // Render PDF (gunakan view yang sama seperti wali)
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
        return $pdf->stream("invoice-{$siswa->nisn}-operator.pdf");
    }
}
