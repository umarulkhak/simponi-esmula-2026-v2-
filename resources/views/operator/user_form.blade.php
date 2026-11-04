{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Form User (Admin/Operator/Wali)
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Menampilkan form tambah/edit data user (admin, operator, wali).
| Fitur       :
|   - Form dinamis (bisa untuk create & edit)
|   - Validasi error dengan feedback visual (Bootstrap)
|   - Password opsional saat edit
|   - Tombol aksi: Simpan & Batal
|   - Responsif di desktop & mobile
|   - Clean Code: struktur blade rapi, komentar jelas
|
| Variabel yang diharapkan dari Controller:
|   - $title   → Judul halaman (misal: "Form Tambah Data User")
|   - $model   → Instance model User (atau new Model untuk create)
|   - $method  → HTTP method ('POST' untuk create, 'PUT' untuk edit)
|   - $route   → Route tujuan (misal: 'user.store' atau ['user.update', $id])
|   - $button  → Teks tombol submit (misal: "SIMPAN" atau "UPDATE")
|
--}}

@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    {{ $title }}
                </h5>
                @if($method === 'PUT')
                    <small class="text-muted">ID: {{ $model->id }}</small>
                @endif
            </div>
            <div class="card-body">

                {!! Form::model($model, [
                    'route'  => $route,
                    'method' => $method,
                    'class'  => 'needs-validation',
                    'novalidate'
                ]) !!}

                <div class="row g-3">

                    {{-- Input: Nama --}}
                    <div class="col-12">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        {!! Form::text('name', null, [
                            'class'       => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''),
                            'id'          => 'name',
                            'placeholder' => 'Contoh: Budi Santoso',
                            'required',
                            'autofocus'
                        ]) !!}
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input: Email --}}
                    <div class="col-12">
                        <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        {!! Form::email('email', null, [
                            'class'       => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''),
                            'id'          => 'email',
                            'placeholder' => 'Contoh: budi@email.com',
                            'required'
                        ]) !!}
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input: No HP --}}
                    <div class="col-12">
                        <label for="nohp" class="form-label fw-semibold">No. HP <span class="text-danger">*</span></label>
                        {!! Form::text('nohp', null, [
                            'class'       => 'form-control' . ($errors->has('nohp') ? ' is-invalid' : ''),
                            'id'          => 'nohp',
                            'placeholder' => 'Contoh: 081234567890',
                            'required'
                        ]) !!}
                        @error('nohp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input: Akses --}}
                    <div class="col-12">
                        <label for="akses" class="form-label fw-semibold">Akses <span class="text-danger">*</span></label>
                        {!! Form::select('akses', [
                            '' => '-- Pilih Akses --',
                            'admin' => 'Administrator',
                            'operator' => 'Operator Sekolah',
                            'wali' => 'Wali Murid'
                        ], null, [
                            'class' => 'form-select' . ($errors->has('akses') ? ' is-invalid' : ''),
                            'id' => 'akses',
                            'required'
                        ]) !!}
                        @error('akses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input: Password --}}
                    <div class="col-12">
                        <label for="password" class="form-label fw-semibold">
                            {{ $method === 'POST' ? 'Password' : 'Password (Opsional)' }}
                            @if($method === 'POST') <span class="text-danger">*</span> @endif
                        </label>
                        {!! Form::password('password', [
                            'class'       => 'form-control' . ($errors->has('password') ? ' is-invalid' : ''),
                            'id'          => 'password',
                            'placeholder' => 'Masukkan password',
                            $method === 'POST' ? 'required' : 'autocomplete' => 'new-password'
                        ]) !!}
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($method === 'PUT')
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah password.</div>
                        @endif
                    </div>

                    {{-- Input: Konfirmasi Password --}}
                    <div class="col-12">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            {{ $method === 'POST' ? 'Konfirmasi Password' : 'Konfirmasi Password (Opsional)' }}
                            @if($method === 'POST') <span class="text-danger">*</span> @endif
                        </label>
                        {!! Form::password('password_confirmation', [
                            'class'       => 'form-control' . ($errors->has('password_confirmation') ? ' is-invalid' : ''),
                            'id'          => 'password_confirmation',
                            'placeholder' => 'Ulangi password',
                            $method === 'POST' ? 'required' : 'autocomplete' => 'new-password'
                        ]) !!}
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex gap-2 mt-4">
                    {!! Form::submit($button, ['class' => 'btn btn-primary px-4']) !!}
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Batal</a>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validasi Bootstrap
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
