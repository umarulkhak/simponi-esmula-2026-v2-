@extends('layouts.app_sneat')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>
        @if($method === 'PUT')
          <small class="text-muted">ID: {{ $model->id }}</small>
        @endif
      </div>
      <div class="card-body">

        {!! Form::model($model, [
          'route'  => $route,
          'method' => $method,
          'files'  => true,
          'class'  => 'needs-validation',
          'novalidate'
        ]) !!}

        <div class="row g-3">

          {{-- Wali Murid & Nama Siswa --}}
          <div class="col-12 col-lg-6">
            <label for="wali_id" class="form-label fw-semibold">Wali Murid</label>
            {!! Form::select('wali_id', $wali, null, [
              'class' => 'form-select select2' . ($errors->has('wali_id') ? ' is-invalid' : ''),
              'placeholder' => 'Pilih Wali Murid',
              'id' => 'wali_id',
            ]) !!}
            @error('wali_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div class="form-text">Pastikan wali murid sudah terdaftar.</div>
          </div>

          <div class="col-12 col-lg-6">
            <label for="nama" class="form-label fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
            {!! Form::text('nama', null, [
              'class' => 'form-control' . ($errors->has('nama') ? ' is-invalid' : ''),
              'id' => 'nama',
              'placeholder' => 'Contoh: Budi Santoso',
              'required',
              'autofocus'
            ]) !!}
            @error('nama')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- NISN & Kelas --}}
          <div class="col-12 col-lg-6">
            <label for="nisn" class="form-label fw-semibold">NISN</label>
            {!! Form::text('nisn', null, [
              'class' => 'form-control' . ($errors->has('nisn') ? ' is-invalid' : ''),
              'id' => 'nisn',
              'placeholder' => 'Contoh: 1234567890',
              'pattern' => '[0-9]*',
              'title' => 'Hanya angka yang diperbolehkan'
            ]) !!}
            @error('nisn')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Nomor Induk Siswa Nasional (opsional).</div>
          </div>

          <div class="col-12 col-lg-6">
            <label for="kelas" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
            {!! Form::select('kelas', [
              '' => 'Pilih Kelas',
              'VII' => 'VII',
              'VIII' => 'VIII',
              'IX' => 'IX'
            ], null, [
              'class' => 'form-select' . ($errors->has('kelas') ? ' is-invalid' : ''),
              'id' => 'kelas',
              'required'
            ]) !!}
            @error('kelas')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Pilih tingkat kelas: VII, VIII, atau IX.</div>
          </div>

          {{-- Tahun Angkatan & Status Siswa --}}
          <div class="col-12 col-lg-6">
            <label for="angkatan" class="form-label fw-semibold">Tahun Angkatan <span class="text-danger">*</span></label>
            {!! Form::selectRange('angkatan', 2023, date('Y') + 1, null, [
              'class' => 'form-select' . ($errors->has('angkatan') ? ' is-invalid' : ''),
              'id' => 'angkatan',
              'required'
            ]) !!}
            @error('angkatan')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- 🔹 FIELD BARU: STATUS SISWA 🔹 --}}
          <div class="col-12 col-lg-6">
            <label for="status" class="form-label fw-semibold">Status Siswa <span class="text-danger">*</span></label>
            {!! Form::select('status', [
                'aktif' => 'Aktif',
                'lulus' => 'Lulus',
                'tidak_aktif' => 'Tidak Aktif (DO)'
            ], null, [
                'class' => 'form-select' . ($errors->has('status') ? ' is-invalid' : ''),
                'id' => 'status',
                'required'
            ]) !!}
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
              Pilih status akademik siswa.
              @if($method === 'PUT' && $model->status === 'lulus')
                <strong class="text-warning">Siswa ini sudah lulus.</strong>
              @endif
            </div>
          </div>

          {{-- Foto --}}
          <div class="col-12">
            <label for="foto" class="form-label fw-semibold">Foto Siswa</label>
            <div class="border rounded p-3 mb-2 bg-light text-center">
                @php
                    $fotoPath = $model->foto
                                ? \Storage::url($model->foto)
                                : asset('images/no-image.png');
                @endphp
              <img
                src="{{ $fotoPath }}"
                onerror="this.src='{{ asset('images/no-image.png') }}'"
                alt="Foto Profil {{ $model->nama }}"
                class="img-fluid rounded shadow-sm"
                style="max-width: 100%; max-height: 200px; object-fit: cover; border: 3px solid #f8f9fa;">
            </div>
            {!! Form::file('foto', [
              'class' => 'form-control' . ($errors->has('foto') ? ' is-invalid' : ''),
              'accept' => 'image/jpeg, image/png',
              'id' => 'foto',
              'aria-describedby' => 'fotoHelp'
            ]) !!}
            @error('foto')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div id="fotoHelp" class="form-text">Format: JPG/PNG, maksimal 5MB.</div>
          </div>

        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex gap-2 mt-4 flex-wrap">
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
  document.addEventListener('DOMContentLoaded', function () {
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

    // Optional: Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $('.select2').select2({
        width: '100%',
        placeholder: "Pilih...",
      });
    }
  });
</script>
@endpush
