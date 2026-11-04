{{--
|--------------------------------------------------------------------------
| VIEW: Wali - Daftar Tagihan Sekolah (Card Per Siswa)
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Tampilan card siswa minimalis dengan icon status & jumlah tagihan
| Fitur       :
|   - Avatar lingkaran hitam
|   - Nama & kelas di samping avatar
|   - Jumlah tagihan: icon + teks, kotak kecil, warna soft
|   - Status: icon + teks, warna soft (hijau/merah)
|   - Tombol Bayar di bawah, warna dark theme (#2A363B)
|   - Animasi hover & transisi smooth
|
| Variabel dari Controller:
|   - $tagihanGrouped → \Illuminate\Support\Collection (groupBy siswa_id)
--}}

@extends('layouts.app_sneat_wali')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                {{-- Header Card --}}
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold">Tagihan Sekolah</h4>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-info-circle fs-5 text-muted"></i>
                        <small class="text-muted">Total {{ $tagihanGrouped->count() ?? 0 }} siswa</small>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($tagihanGrouped->isEmpty())
                        <div class="text-center py-5">
                            <i class="bx bx-empty fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted fw-normal">Belum ada tagihan tersedia</h5>
                            <p class="text-muted small">Silakan cek kembali nanti atau hubungi operator sekolah.</p>
                        </div>
                    @else
                        <div class="row g-3 px-3 py-3">
                            @foreach ($tagihanGrouped as $siswaId => $tagihanList)
                                @php
                                    $siswa = $tagihanList->first()->siswa;
                                    $kelas = $siswa->kelas ?? '-';
                                    $accentColor = match($kelas) {
                                        'VII' => '#28a745',
                                        'VIII' => '#fd7e14',
                                        'IX' => '#6f42c1',
                                        default => '#6c757d'
                                    };

                                    $totalTagihan = $tagihanList->count();
                                    $sudahDibayar = $tagihanList->where('status', 'lunas')->count();
                                    $statusPembayaran = $sudahDibayar == $totalTagihan ? 'Lunas' : ($sudahDibayar > 0 ? 'Angsur' : 'Belum Dibayar');
                                    $statusColor = $sudahDibayar == $totalTagihan ? 'success' : ($sudahDibayar > 0 ? 'warning' : 'danger');
                                @endphp

                                {{-- CARD PER SISWA (MINIMALIS + ICON + ANIMASI) --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card siswa-card shadow-sm border-0 rounded-3 p-4" style="background-color: #ffffff;">
                                        <!-- Header: Jumlah Tagihan & Status -->
                                        <div class="d-flex justify-content-between mb-3">
                                            <!-- Jumlah Tagihan -->
                                            <span class="badge rounded-pill bg-light text-dark small">
                                                <i class="bx bx-exclamation-circle me-1"></i> {{ $totalTagihan }} tagihan
                                            </span>

                                            <!-- Status -->
                                            <span class="badge rounded-pill bg-light text-dark small">
                                                @if ($statusColor == 'success')
                                                    <i class="bx bx-check-circle" style="color: rgba(40, 167, 69, 0.6);"></i> {{ $statusPembayaran }}
                                                @elseif ($statusColor == 'warning')
                                                    <i class="bx bx-history" style="color: rgba(255, 193, 7, 0.5);"></i> {{ $statusPembayaran }}
                                                @else
                                                    <i class="bx bx-error-circle" style="color: rgba(220, 53, 69, 0.5);"></i> {{ $statusPembayaran }}
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Avatar + Nama & Kelas -->
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="rounded-full bg-black" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $siswa->nama ?? 'Tanpa Nama' }}</div>
                                                <small class="text-muted">{{ $kelas }}</small>
                                            </div>
                                        </div>

                                        <!-- Tombol Bayar -->
                                        <div class="mt-3 text-center">
                                            @if ($sudahDibayar == $totalTagihan)
                                                <a href="{{ route('wali.tagihan.show', $siswaId) }}"
                                                   class="btn btn-sm w-100 tombol-status"
                                                   style="background: #495057; color: white; font-weight: 500;"
                                                   title="Lihat riwayat pembayaran {{ $siswa->nama ?? 'siswa ini' }}">
                                                    <i class="bx bx-history me-1"></i> Lihat Riwayat
                                                </a>
                                            @else
                                                <a href="{{ route('wali.tagihan.show', $siswaId) }}"
                                                   class="btn btn-sm w-100 tombol-bayar"
                                                   style="background: #2A363B; color: white; font-weight: 500;"
                                                   title="Bayar tagihan untuk {{ $siswa->nama ?? 'siswa ini' }}">
                                                    <i class="bx bx-credit-card me-1"></i> Bayar
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- PINDAHKAN INFO KE SINI --}}
                        <div class="px-4 pb-3">
                            <small class="text-muted fst-italic">* Klik tombol untuk melihat atau bayar tagihan</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Badge Soft */
    .badge.rounded-pill.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    /* Status Icons */
    .text-success {
        color: #28a745 !important;
    }
    .text-warning {
        color: #ffc107 !important;
    }
    .text-danger {
        color: #dc3545 !important;
    }

    /* Card */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    /* Animasi Hover Card Siswa */
    .siswa-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .siswa-card:hover {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        background-color: #f8f9fa !important;
    }

    /* Tombol Bayar Hover */
    .tombol-bayar {
        transition: all 0.25s ease;
    }

    .tombol-bayar:hover {
        background: #1E2C2F !important;
        transform: translateY(-1px) scale(1.02);
        box-shadow: 0 4px 12px rgba(42, 54, 59, 0.3);
    }

    /* Tombol Status (Lihat Riwayat) Hover */
    .tombol-status {
        transition: all 0.25s ease;
    }

    .tombol-status:hover {
        background: #343a40 !important;
        transform: translateY(-1px) scale(1.02);
        box-shadow: 0 4px 12px rgba(52, 58, 64, 0.3);
    }

    /* Layout */
    .row.g-3 {
        gap: 1rem;
    }

    /* Mobile Optimization */
    @media (max-width: 576px) {
        .col-md-6 {
            width: 100%;
        }

        .siswa-card:hover {
            transform: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
    }

    /* Avatar */
    .rounded-full {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
</style>
@endpush
