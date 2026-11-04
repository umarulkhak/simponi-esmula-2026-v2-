@extends('layouts.app_sneat')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 p-md-4">

                    {{-- === HEADER === --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Dashboard Operator</h5>
                            <p class="text-muted mb-0 small">Selamat datang di Simponi SMP Muhammadiyah Larangan</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('tagihan.export.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3" target="_blank">
                                <i class="bx bx-file me-1"></i> Export Excel
                            </a>
                            <a href="{{ route('tagihan.create') }}" class="btn btn-primary btn-sm px-3">
                                <i class="bx bx-calendar-plus me-1"></i> Tagihan Baru
                            </a>
                        </div>
                    </div>

                    {{-- === STATISTIK RINGKAS === --}}
                    <div class="row g-3 mb-4">
                        @php
                            $statsList = [
                                [
                                    'label' => 'Total Siswa',
                                    'value' => number_format($stats['total_siswa']),
                                    'desc' => ($stats['diff_siswa'] >= 0 ? '+' : '') . $stats['diff_siswa'],
                                    'color' => 'primary',
                                    'icon' => 'bx-user',
                                    'desc_class' => $stats['diff_siswa'] >= 0 ? 'text-success' : 'text-danger'
                                ],
                                [
                                    'label' => 'Lunas',
                                    'value' => $stats['total_lunas'],
                                    'desc' => ($stats['diff_siswa'] >= 0 ? '+' : '') . ($stats['total_lunas'] - ($stats['total_siswa'] - $stats['total_belum_bayar'] - $stats['total_lunas'])), // fallback
                                    'color' => 'success',
                                    'icon' => 'bx-check-circle',
                                    'desc_class' => $stats['diff_siswa'] >= 0 ? 'text-success' : 'text-danger'
                                ],
                                [
                                    'label' => 'Belum Bayar',
                                    'value' => $stats['total_belum_bayar'],
                                    'desc' => ($stats['diff_belum'] >= 0 ? '+' : '') . $stats['diff_belum'],
                                    'color' => 'danger',
                                    'icon' => 'bx-time',
                                    'desc_class' => $stats['diff_belum'] <= 0 ? 'text-success' : 'text-danger'
                                ],
                                [
                                    'label' => 'Pembayaran',
                                    'value' => $stats['tingkat_pembayaran'] . '%',
                                    'desc' => ($stats['diff_persen'] >= 0 ? '+' : '') . number_format($stats['diff_persen'], 1) . '%',
                                    'color' => 'purple',
                                    'icon' => 'bx-bar-chart-alt-2',
                                    'desc_class' => $stats['diff_persen'] >= 0 ? 'text-success' : 'text-danger'
                                ],
                            ];
                        @endphp

                        @foreach($statsList as $item)
                            <div class="col-6 col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-3 text-center">
                                        <div class="mb-2">
                                            <i class="bx {{ $item['icon'] }} text-{{ $item['color'] }} fs-4"></i>
                                        </div>
                                        <h6 class="mb-1 fw-bold">{{ $item['value'] }}</h6>
                                        <p class="text-muted small mb-0">{{ $item['label'] }}</p>
                                        <small class="{{ $item['desc_class'] }} fst-italic">
                                            {{ $item['desc'] }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- === RINGKASAN PER KELAS & PEMBAYARAN TERBARU === --}}
                    <div class="row g-4">
                        {{-- Ringkasan Per Kelas --}}
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold mb-0">Ringkasan Per Kelas</h6>
                                    <a href="{{ route('siswa.create') }}" class="btn btn-primary btn-sm px-2 py-1">
                                        <i class="bx bx-user-plus fs-5"></i>
                                    </a>
                                </div>
                                <div class="card-body pt-3">
                                    @foreach($kelasStats as $kelas)
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <h6 class="fw-medium mb-0">{{ $kelas['nama'] }}</h6>
                                                <small class="text-muted">{{ $kelas['jumlah_siswa'] }} siswa</small>
                                            </div>
                                            <div class="d-flex gap-1 flex-wrap">
                                                @if($kelas['lunas'] > 0)
                                                    <span class="badge bg-label-success">Lunas: {{ $kelas['lunas'] }}</span>
                                                @endif
                                                @if($kelas['pending'] > 0)
                                                    <span class="badge bg-label-warning">Pending: {{ $kelas['pending'] }}</span>
                                                @endif
                                                @if($kelas['belum_bayar'] > 0)
                                                    <span class="badge bg-label-danger">Belum: {{ $kelas['belum_bayar'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="d-flex justify-content-around mt-3 pt-2 border-top">
                                        <div class="text-center">
                                            <div class="fw-bold text-success">{{ $stats['total_lunas'] }}</div>
                                            <small class="text-muted">Lunas</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-bold text-warning">{{ $stats['total_pending'] }}</div>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-bold text-danger">{{ $stats['total_belum_bayar'] }}</div>
                                            <small class="text-muted">Belum Bayar</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pembayaran Terbaru --}}
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-bottom py-3">
                                    <h6 class="fw-semibold mb-0">Pembayaran Terbaru</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Siswa</th>
                                                    <th scope="col">Kelas</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Jumlah</th>
                                                    <th scope="col">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentPayments as $payment)
                                                    <tr>
                                                        <td class="fw-medium">{{ $payment->siswa?->nama ?? '-' }}</td>
                                                        <td><small>{{ $payment->siswa?->kelas ?? '-' }}</small></td>
                                                        <td>
                                                            @php
                                                                $status = match($payment->status) {
                                                                    'lunas' => ['text' => 'Lunas', 'color' => 'success'],
                                                                    'baru' => ['text' => 'Pending', 'color' => 'warning'],
                                                                    default => ['text' => 'Belum', 'color' => 'danger']
                                                                };
                                                            @endphp
                                                            <span class="badge bg-label-{{ $status['color'] }}">{{ $status['text'] }}</span>
                                                        </td>
                                                        <td class="fw-bold">{{ formatRupiah($payment->total_biaya, 0, ',', '.') }}</td>
                                                        <td>
                                                            <a href="{{ route('tagihan.show', $payment->siswa_id) }}" class="btn btn-xs btn-outline-secondary p-1">
                                                                <i class="bx bx-show fs-5"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">
                                                            <i class="bx bx-receipt fs-2 mb-1"></i><br>
                                                            Tidak ada data
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('tagihan.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                                            Lihat Semua
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Optimasi untuk mobile */
    @media (max-width: 767px) {
        .card-body {
            padding: 1rem !important;
        }
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        .btn-xs {
            padding: 0.25rem 0.4rem !important;
            font-size: 0.75rem !important;
        }
        .card-header {
            padding: 0.75rem 1rem !important;
        }
    }
</style>
@endpush
