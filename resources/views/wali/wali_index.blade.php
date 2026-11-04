{{--
|--------------------------------------------------------------------------
| VIEW: Wali - Lihat Profil (Mobile-Pro)
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team (diperbarui untuk UX mobile profesional)
| Tujuan      : Menampilkan profil wali murid dalam mode read-only, optimal di HP.
| Fitur       :
|   - Desain minimalis & fokus
|   - Tombol aksi besar & mudah dijangkau
|   - Informasi terstruktur dengan jarak lega
|   - Visual bersih tanpa elemen berlebihan
|   - Mendukung dark mode (jika layout Sneat mendukung)
--}}

@extends('layouts.app_sneat_wali')

@section('content')
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header dengan tombol edit di pojok kanan atas -->
        <div class="card-header bg-white py-4 position-relative">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bx bx-user-circle me-2"></i>Profil Saya
            </h5>
            <a href="{{ route('wali.profile.edit', auth()->id()) }}" class="btn btn-primary btn-sm position-absolute top-50 end-0 translate-middle-y me-3 px-3">
                <i class="bx bx-edit me-1"></i>Edit
            </a>
        </div>

        <div class="card-body p-4">
            <!-- Nama sebagai highlight utama -->
            <div class="text-center mb-4 pb-3 border-bottom">
                <h5 class="fw-bold mb-1">{{ $model->name }}</h5>
                <span class="badge bg-label-info px-3 py-1">
                    {{ ucfirst($model->akses ?? 'Wali Murid') }}
                </span>
            </div>

            <!-- Daftar Informasi Profil -->
            <div class="profile-info-list">

                <!-- Email -->
                <div class="d-flex align-items-start mb-4">
                    <div class="icon-box bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                        <i class="bx bx-envelope text-muted fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-medium">Email</small>
                        <p class="mb-0 fw-medium">{{ $model->email }}</p>
                    </div>
                </div>

                <!-- No. HP -->
                <div class="d-flex align-items-start mb-4">
                    <div class="icon-box bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                        <i class="bx bx-phone text-muted fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-medium">Nomor HP</small>
                        <p class="mb-0 fw-medium">{{ $model->nohp ?? '–' }}</p>
                    </div>
                </div>

                <!-- Tanggal Daftar -->
                <div class="d-flex align-items-start mb-4">
                    <div class="icon-box bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                        <i class="bx bx-calendar-plus text-muted fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-medium">Terdaftar Sejak</small>
                        <p class="mb-0 fw-medium">{{ $model->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                </div>

                <!-- Terakhir Diperbarui -->
                <div class="d-flex align-items-start">
                    <div class="icon-box bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                        <i class="bx bx-time-five text-muted fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-medium">Terakhir Diperbarui</small>
                        <p class="mb-0 fw-medium">{{ $model->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
