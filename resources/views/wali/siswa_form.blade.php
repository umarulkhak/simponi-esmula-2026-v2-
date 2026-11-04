@extends('layouts.app_sneat_wali')

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto">
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="mb-0">{{ $title ?? 'Edit Data Siswa' }}</h5>
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

          {{-- Nama Lengkap Siswa (Editable) --}}
          <div class="col-12">
            <label for="nama" class="form-label fw-semibold">
              <i class="bx bx-user me-2 text-muted"></i>Nama Lengkap Siswa <span class="text-danger">*</span>
            </label>
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

          {{-- Foto Siswa (Editable) --}}
          <div class="col-12">
            <label for="foto" class="form-label fw-semibold">
              <i class="bx bx-image me-2 text-muted"></i>Foto Siswa
            </label>
            <div class="border rounded p-3 mb-2 bg-light text-center">
                @php
                    $fotoPath = $model->foto ? \Storage::url($model->foto) : asset('images/no-image.png');
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

          {{-- NISN (Read-only) --}}
          <div class="col-12">
            <label class="form-label fw-semibold">
              <i class="bx bx-id-card me-2 text-muted"></i>NISN
            </label>
            {!! Form::text('nisn', null, [
              'class' => 'form-control',
              'disabled' => 'disabled'
            ]) !!}
            {!! Form::hidden('nisn', $model->nisn) !!} {{-- Pastikan nilainya tetap dikirim --}}
          </div>

          {{-- Kelas (Read-only) --}}
          <div class="col-12">
            <label class="form-label fw-semibold">
              <i class="bx bx-book me-2 text-muted"></i>Kelas
            </label>
            {!! Form::select('kelas', [
              'VII' => 'VII',
              'VIII' => 'VIII',
              'IX' => 'IX'
            ], null, [
              'class' => 'form-control',
              'disabled' => 'disabled'
            ]) !!}
            {!! Form::hidden('kelas', $model->kelas) !!}
          </div>

          {{-- Angkatan (Read-only) --}}
          <div class="col-12">
            <label class="form-label fw-semibold">
              <i class="bx bx-calendar me-2 text-muted"></i>Tahun Angkatan
            </label>
            {!! Form::selectRange('angkatan', 2020, date('Y') + 2, null, [
              'class' => 'form-control',
              'disabled' => 'disabled'
            ]) !!}
            {!! Form::hidden('angkatan', $model->angkatan) !!}
          </div>

          {{-- Wali Murid (Read-only) --}}
          <div class="col-12">
            <label class="form-label fw-semibold">
              <i class="bx bx-group me-2 text-muted"></i>Wali Murid
            </label>
            <div class="form-control" disabled>
              {{ $model->wali->name ?? '–' }}
            </div>
            {!! Form::hidden('wali_id', $model->wali_id) !!}
          </div>

        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>{{ $button ?? 'Simpan Perubahan' }}
          </button>
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="bx bx-x me-1"></i>Batal
          </a>
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
    const fotoInput = document.getElementById('foto');
    const previewImg = document.getElementById('preview-foto');

    if (fotoInput && previewImg) {
      fotoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file && /^image\//.test(file.type)) {
          const reader = new FileReader();
          reader.onload = (evt) => {
            previewImg.src = evt.target.result;
          };
          reader.readAsDataURL(file);
        } else {
          previewImg.src = "{{ asset('images/no-image.png') }}";
        }
      });
    }

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
