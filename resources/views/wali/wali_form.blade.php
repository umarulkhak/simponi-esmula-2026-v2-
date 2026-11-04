{{--
|--------------------------------------------------------------------------
| VIEW: Wali - Form Profil (Mobile-Optimized)
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Form edit profil yang optimal di perangkat mobile.
| Fitur       :
|   - Layout vertikal penuh, fokus pada satu kolom
|   - Input besar & mudah disentuh
|   - Label jelas tanpa ikon berlebih
|   - Pesan error & bantuan mudah dibaca
|   - Tombol aksi besar & kontras tinggi
--}}

@extends('layouts.app_sneat_wali')

@section('content')
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-4 text-center">
            <h5 class="mb-1 text-dark fw-bold">
                {{ $title }}
            </h5>
            <p class="text-muted mb-0 small">Perbarui data profil Anda</p>
        </div>

        <div class="card-body p-4">
            {!! Form::model($model, [
                'route'  => $route,
                'method' => $method,
                'class'  => 'needs-validation',
                'novalidate'
            ]) !!}

            {{-- Nama Lengkap --}}
            <div class="mb-4">
                <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                {!! Form::text('name', null, [
                    'class'       => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''),
                    'id'          => 'name',
                    'placeholder' => 'Masukkan nama lengkap',
                    'required',
                    'autofocus'
                ]) !!}
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                {!! Form::email('email', null, [
                    'class'       => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''),
                    'id'          => 'email',
                    'placeholder' => 'nama@email.com',
                    'required'
                ]) !!}
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- No. HP --}}
            <div class="mb-4">
                <label for="nohp" class="form-label fw-semibold">Nomor HP <span class="text-danger">*</span></label>
                {!! Form::text('nohp', null, [
                    'class'       => 'form-control' . ($errors->has('nohp') ? ' is-invalid' : ''),
                    'id'          => 'nohp',
                    'placeholder' => '081234567890',
                    'required'
                ]) !!}
                @error('nohp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">
                    Password
                    @if($method === 'PUT')
                        <span class="text-muted">(opsional)</span>
                    @else
                        <span class="text-danger">*</span>
                    @endif
                </label>
                {!! Form::password('password', [
                    'class'       => 'form-control' . ($errors->has('password') ? ' is-invalid' : ''),
                    'id'          => 'password',
                    'placeholder' => $method === 'PUT' ? 'Biarkan kosong jika tidak diubah' : 'Minimal 6 karakter',
                    $method === 'POST' ? 'required' : 'autocomplete' => 'new-password'
                ]) !!}
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">
                    Konfirmasi Password
                    @if($method === 'PUT')
                        <span class="text-muted">(opsional)</span>
                    @else
                        <span class="text-danger">*</span>
                    @endif
                </label>
                {!! Form::password('password_confirmation', [
                    'class'       => 'form-control' . ($errors->has('password_confirmation') ? ' is-invalid' : ''),
                    'id'          => 'password_confirmation',
                    'placeholder' => $method === 'PUT' ? 'Ulangi password baru (jika diisi)' : 'Ulangi password',
                    $method === 'POST' ? 'required' : 'autocomplete' => 'new-password'
                ]) !!}
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-check-circle me-1"></i> {{ $button }}
                </button>
                <a href="{{ route('wali.profile.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-x me-1"></i> Batal
                </a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>
@endpush
