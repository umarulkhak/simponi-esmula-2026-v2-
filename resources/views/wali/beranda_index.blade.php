@extends('layouts.app_sneat_wali')

@section('content')
<div class="row justify-content-center">
    <div class="col-12">

        {{-- === HEADER === --}}
        <div class="d-flex justify-content-between align-items-center mb-4 p-2">
            <div>
                <h5 class="fw-bold mb-0">Halo, {{ Auth::user()->name ?? 'Bapak/Ibu' }} 👋</h5>
                <small class="text-muted">Dashboard Wali Murid</small>
            </div>
        </div>

        {{-- === QUICK STATS === --}}
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card shadow-sm border-0 rounded-3 bg-light-primary">
                    <div class="card-body p-3 text-center">
                        <div class="fs-5 fw-bold text-primary">{{ $jumlahAnak }}</div>
                        <small class="text-muted">Anak</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card shadow-sm border-0 rounded-3 bg-light-warning">
                    <div class="card-body p-3 text-center">
                        <div class="fs-5 fw-bold text-warning">{{ $tagihanBelumLunas }}</div>
                        <small class="text-muted">Tagihan Belum Lunas</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- === ACTION BUTTONS === --}}
        <div class="d-grid gap-2 mb-4">
            <a href="{{ route('wali.tagihan.index') }}" class="btn btn-primary rounded-3 py-3">
                <i class="bx bx-credit-card me-2"></i>Bayar Tagihan Sekarang
            </a>
            <a href="{{ route('wali.siswa.index') }}" class="btn btn-outline-secondary rounded-3 py-3">
                <i class="bx bx-user me-2"></i>Lihat Data Anak
            </a>
        </div>

        {{-- === PAPAN INFORMASI (COMING SOON) === --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-0 py-2">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-news text-info me-1"></i> Papan Informasi
                </h6>
            </div>
            <div class="card-body p-3 text-center">
                <div class="py-4">
                    <i class="bx bx-hourglass fs-1 text-muted mb-2"></i>
                    <p class="mb-0 text-muted">Fitur ini sedang dalam pengembangan</p>
                    <small class="text-muted">Coming Soon</small>
                </div>
            </div>
        </div>

        {{-- === RIWAYAT PEMBAYARAN TERBARU === --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-0 py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Riwayat Pembayaran Terbaru</h6>
                <a href="{{ route('wali.tagihan.index') }}" class="small text-decoration-none">Lihat semua</a>
            </div>
            <div class="card-body p-0">
                @if($riwayatPembayaran->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($riwayatPembayaran as $pembayaran)
                            <div class="list-group-item border-0 px-3 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-medium">
                                            {{ $pembayaran->tagihan?->judul_dinamis ?? 'Tagihan #' . $pembayaran->tagihan_id }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $pembayaran->tanggal_bayar ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d M Y') : '-' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">
                                            {{ 'Rp ' . number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}
                                        </div>
                                        @if($pembayaran->status_konfirmasi === 'sudah')
                                            <small class="badge bg-success">Sudah</small>
                                        @elseif($pembayaran->status_konfirmasi === 'ditolak')
                                            <small class="badge bg-danger">Ditolak</small>
                                        @else
                                            <small class="badge bg-warning">Menunggu</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="bx bx-receipt fs-2 mb-1"></i>
                        <p class="mb-0">Belum ada riwayat pembayaran</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
