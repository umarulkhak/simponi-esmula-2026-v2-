@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-user-circle me-2"></i>
                    {{ $title ?? 'Detail Profil Siswa' }}
                </h5>
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    {{-- === SECTION: FOTO PROFILE === --}}
                    <div class="col-12 col-md-4 text-center">
                        @php
                            $fotoPath = $model->foto
                                ? \Storage::url($model->foto)
                                : asset('images/no-image.png');
                        @endphp

                        <div class="mb-3 position-relative d-inline-block">
                            <img
                                src="{{ $fotoPath }}"
                                onerror="this.src='{{ asset('images/no-image.png') }}'"
                                alt="Foto Profil {{ $model->nama }}"
                                class="img-fluid rounded shadow-sm"
                                style="max-width: 100%; max-height: 280px; object-fit: cover; border: 3px solid #f8f9fa;">
                            <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2 shadow-sm">
                                <i class="bx bx-image text-muted fs-5"></i>
                            </div>
                        </div>

                        <div class="mt-2">
                            <span class="badge bg-label-primary rounded-pill px-3 py-1">
                                ID: #{{ $model->id }}
                            </span>
                        </div>
                    </div>

                    {{-- === SECTION: DETAIL DATA === --}}
                    <div class="col-12 col-md-8">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="fw-bold mb-4">
                                <i class="bx bx-info-circle me-2"></i>
                                Informasi Siswa
                            </h6>

                            <div class="row g-3">

                                {{-- Baris 1 --}}
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-primary rounded p-2 me-3">
                                            <i class="bx bx-user fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Nama Lengkap</div>
                                            <div class="fw-semibold">{{ $model->nama }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-info rounded p-2 me-3">
                                            <i class="bx bx-id-card fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">NISN</div>
                                            <div class="fw-semibold">{{ $model->nisn ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Baris 2 --}}
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-success rounded p-2 me-3">
                                            <i class="bx bx-book fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Kelas</div>
                                            <div class="fw-semibold">
                                                <span class="badge bg-label-{{ $model->kelas == 'VII' ? 'success' : ($model->kelas == 'VIII' ? 'warning' : 'danger') }} rounded-pill px-3 py-1">
                                                    {{ $model->kelas }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-secondary rounded p-2 me-3">
                                            <i class="bx bx-calendar fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Angkatan</div>
                                            <div class="fw-semibold">{{ $model->angkatan }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 🔹 BARIS BARU: STATUS SISWA 🔹 --}}
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        @php
                                            $statusConfig = match($model->status) {
                                                'lulus' => ['color' => 'secondary', 'icon' => 'bx-graduation', 'label' => 'LULUS'],
                                                'tidak_aktif' => ['color' => 'danger', 'icon' => 'bx-x-circle', 'label' => 'Tidak Aktif'],
                                                default => ['color' => 'success', 'icon' => 'bx-check-circle', 'label' => 'Aktif'],
                                            };
                                        @endphp
                                        <div class="flex-shrink-0 bg-label-{{ $statusConfig['color'] }} rounded p-2 me-3">
                                            <i class="bx {{ $statusConfig['icon'] }} fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Status Siswa</div>
                                            <div class="fw-semibold">
                                                <span class="badge bg-label-{{ $statusConfig['color'] }} rounded-pill px-3 py-1">
                                                    <i class="bx {{ $statusConfig['icon'] }} me-1"></i> {{ $statusConfig['label'] }}
                                                </span>
                                                @if($model->status === 'lulus' && $model->tahun_lulus)
                                                    <small class="text-muted d-block mt-1">Tahun Lulus: {{ $model->tahun_lulus }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Baris 3 (Wali Murid) --}}
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-warning rounded p-2 me-3">
                                            <i class="bx bx-group fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Wali Murid</div>
                                            <div class="fw-semibold">{{ $model->wali->name ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-{{ $model->wali_status === 'ok' ? 'success' : 'secondary' }} rounded p-2 me-3">
                                            <i class="bx bx-check-circle fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Status Wali</div>
                                            <div class="fw-semibold">
                                                @if ($model->wali_status === 'ok')
                                                    <span class="badge bg-label-success rounded-pill px-3 py-1">Aktif</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill px-3 py-1">Tidak Ada</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Baris 4 --}}
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-dark rounded p-2 me-3">
                                            <i class="bx bx-user-plus fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Dibuat Oleh</div>
                                            <div class="fw-semibold">{{ $model->user->name ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-primary rounded p-2 me-3">
                                            <i class="bx bx-time fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Dibuat Pada</div>
                                            <div class="fw-semibold">{{ $model->created_at?->format('d M Y H:i') ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Baris 5 --}}
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-info rounded p-2 me-3">
                                            <i class="bx bx-edit fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Diubah Terakhir</div>
                                            <div class="fw-semibold">{{ $model->updated_at?->format('d M Y H:i') ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="mt-4 pt-3 border-top">
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route($routePrefix . '.edit', $model->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bx bx-edit me-1"></i> Edit Data
                                    </a>
                                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
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
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        transition: all 0.3s ease;
    }
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.card').classList.add('fade-in');
    });
</script>
@endpush
