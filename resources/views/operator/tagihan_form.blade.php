{{-- resources/views/operator/tagihan_form.blade.php --}}
@extends('layouts.app_sneat')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-12">
    <div class="card">

      {{-- Judul Form --}}
      <h5 class="card-header">{{ $title }}</h5>

      <div class="card-body">
        {{-- Form create & edit --}}
        {!! Form::model($model, [
            'route' => $route,
            'method' => $method,
            'class' => 'needs-validation',
            'novalidate' => true
        ]) !!}

        <div class="row">

          {{-- Biaya --}}
          <div class="col-12 mb-3">
            <label for="biaya_id" class="form-label fw-bold">Biaya Yang Ditagihkan</label>
            <p class="text-muted small mb-2">Pilih satu atau lebih jenis biaya yang akan ditagihkan ke siswa.</p>

            @if($biaya->isEmpty())
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    Belum ada data biaya. Silakan tambah data biaya terlebih dahulu di menu <strong>Master Biaya</strong>.
                </div>
            @else
                <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="toggleAllBiaya()">
                    <i class="fa fa-check-square me-1"></i> Pilih Semua / Batal Pilih
                </button>

                <div class="row g-3">
                    @foreach($biaya as $id => $nama)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm biaya-card cursor-pointer border rounded-3">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="form-check m-0">
                                        {!! Form::checkbox(
                                            'biaya_id[]',
                                            $id,
                                            in_array($id, old('biaya_id', $model->biaya_id ?? [])),
                                            [
                                                'class' => 'form-check-input me-2 biaya-checkbox',
                                                'id' => 'biaya_' . $id,
                                            ]
                                        ) !!}
                                        <label for="biaya_{{ $id }}" class="form-check-label mb-0">
                                            <strong>{{ $nama }}</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('biaya_id')
                    <div class="invalid-feedback d-block mt-2">
                        <i class="fa fa-times-circle me-1"></i> {{ $message }}
                    </div>
                @enderror
            @endif
          </div>

          {{-- Angkatan --}}
          <div class="col-md-6 mb-3">
            <label for="angkatan" class="form-label fw-bold">Tagihan Untuk Angkatan</label>
            <div class="form-text text-muted small mb-2">
                <i class="fa fa-info-circle me-1"></i>
                Kosongkan untuk tagihkan ke <strong>semua angkatan</strong>.
            </div>
            {!! Form::select(
                'angkatan',
                $angkatan->toArray(),
                old('angkatan', $model->angkatan ?? null),
                [
                    'class'       => 'form-select' . ($errors->has('angkatan') ? ' is-invalid' : ''),
                    'id'          => 'angkatan',
                    'placeholder' => '-- Pilih Tahun Angkatan (Opsional) --',
                    'autofocus'   => true,
                ]
            ) !!}
            @error('angkatan')
                <div class="invalid-feedback">
                    <i class="fa fa-times-circle me-1"></i> {{ $message }}
                </div>
            @enderror
          </div>

          {{-- Kelas --}}
          <div class="col-md-6 mb-3">
            <label for="kelas" class="form-label fw-bold">Kelas</label>
            <div class="form-text text-muted small mb-2">
                <i class="fa fa-info-circle me-1"></i>
                Kosongkan untuk tagihkan ke <strong>semua kelas</strong>.
            </div>
            {!! Form::select(
                'kelas',
                $kelas->toArray(),
                old('kelas', $model->kelas ?? null),
                [
                    'class'       => 'form-select' . ($errors->has('kelas') ? ' is-invalid' : ''),
                    'id'          => 'kelas',
                    'placeholder' => '-- Pilih Kelas (Opsional) --',
                ]
            ) !!}
            @error('kelas')
                <div class="invalid-feedback">
                    <i class="fa fa-times-circle me-1"></i> {{ $message }}
                </div>
            @enderror
          </div>

          {{-- Tanggal Tagihan --}}
          <div class="col-md-6 mb-3">
            <label for="tanggal_tagihan" class="form-label fw-bold">Tanggal Tagihan</label>
            {!! Form::date(
                'tanggal_tagihan',
                old('tanggal_tagihan', $model->tanggal_tagihan ?? date('Y-m-d')),
                [
                    'class' => 'form-control' . ($errors->has('tanggal_tagihan') ? ' is-invalid' : ''),
                    'id'    => 'tanggal_tagihan',
                    'required' => true,
                ]
            ) !!}
            @error('tanggal_tagihan')
                <div class="invalid-feedback">
                    <i class="fa fa-times-circle me-1"></i> {{ $message }}
                </div>
            @enderror
          </div>

          {{-- Tanggal Jatuh Tempo --}}
          @php
              $tanggalTagihan = old('tanggal_tagihan', $model->tanggal_tagihan ?? date('Y-m-d'));
              $defaultJatuhTempo = \Carbon\Carbon::parse($tanggalTagihan)->addMonth()->format('Y-m-d');
          @endphp
          <div class="col-md-6 mb-3">
              <label for="tanggal_jatuh_tempo" class="form-label fw-bold">Tanggal Jatuh Tempo</label>
              {!! Form::date(
                  'tanggal_jatuh_tempo',
                  old('tanggal_jatuh_tempo', $model->tanggal_jatuh_tempo ?? $defaultJatuhTempo),
                  [
                      'class' => 'form-control' . ($errors->has('tanggal_jatuh_tempo') ? ' is-invalid' : ''),
                      'id'    => 'tanggal_jatuh_tempo',
                      'required' => true,
                  ]
              ) !!}
              @error('tanggal_jatuh_tempo')
                  <div class="invalid-feedback">
                      <i class="fa fa-times-circle me-1"></i> {{ $message }}
                  </div>
              @enderror
          </div>

          {{-- Keterangan --}}
          <div class="col-12 mb-3">
            <label for="keterangan" class="form-label fw-bold">Keterangan (Opsional)</label>
            <div class="form-text mb-2">Contoh: “Tagihan bulan September 2025”, “Pembayaran ujian akhir”, dll.</div>
            {!! Form::textarea(
                'keterangan',
                old('keterangan', $model->keterangan ?? null),
                [
                    'class' => 'form-control' . ($errors->has('keterangan') ? ' is-invalid' : ''),
                    'id'    => 'keterangan',
                    'rows'  => 3,
                    'placeholder' => 'Masukkan keterangan tambahan...'
                ]
            ) !!}
            @error('keterangan')
                <div class="invalid-feedback">
                    <i class="fa fa-times-circle me-1"></i> {{ $message }}
                </div>
            @enderror
          </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-4 d-flex gap-2 flex-wrap">
          {!! Form::submit($button, ['class' => 'btn btn-primary px-4']) !!}
          <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">
              <i class="fa fa-times me-1"></i> Batal
          </a>
        </div>

        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .biaya-card {
    transition: all 0.25s ease-in-out;
    cursor: pointer;
    border: 2px solid #dee2e6;
    border-radius: 0.75rem;
    background-color: #fff;
    height: 100%;
  }
  .biaya-card:hover {
    border-color: #0d6efd;
    background-color: #f0f6ff;
    box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.15);
    transform: translateY(-2px);
  }
  .biaya-card .form-check-input { display: none; }
  .biaya-card .form-check-label {
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    width: 100%;
    text-align: center;
    transition: all 0.2s ease;
  }
  .biaya-card input[type="checkbox"]:checked ~ .form-check-label {
    font-weight: 600;
    color: #fff;
    background-color: #0d6efd;
    border: none;
  }
  .biaya-card input[type="checkbox"]:checked ~ .form-check-label::before {
    content: "✓ ";
    font-weight: bold;
  }
  @media (max-width: 768px) {
    .biaya-card .form-check-label {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
    }
  }
</style>
@endpush

@push('scripts')
<script>
function toggleAllBiaya() {
    var checkboxes = document.querySelectorAll('.biaya-checkbox');
    var allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}

// Validasi Form Kustom
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.querySelectorAll('.needs-validation');
        var biayaCheckboxes = document.querySelectorAll('.biaya-checkbox');

        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                var angkatan = form.querySelector('#angkatan');
                var kelas = form.querySelector('#kelas');
                if (angkatan) angkatan.removeAttribute('required');
                if (kelas) kelas.removeAttribute('required');

                var biayaChecked = Array.from(biayaCheckboxes).some(cb => cb.checked);
                if (!biayaChecked) {
                    event.preventDefault();
                    event.stopPropagation();
                    var biayaContainer = document.querySelector('[name="biaya_id[]"]').closest('.col-12');
                    var errorDiv = biayaContainer.querySelector('.invalid-feedback');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback d-block mt-2';
                        errorDiv.innerHTML = '<i class="fa fa-times-circle me-1"></i> Pilih minimal satu jenis biaya.';
                        biayaContainer.appendChild(errorDiv);
                    }
                    biayaContainer.classList.add('was-validated');
                }

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Auto-update jatuh tempo ketika tanggal tagihan berubah
document.addEventListener('DOMContentLoaded', function() {
    const tglTagihan = document.getElementById('tanggal_tagihan');
    const tglJatuhTempo = document.getElementById('tanggal_jatuh_tempo');
    if (tglTagihan && tglJatuhTempo) {
        tglTagihan.addEventListener('change', function() {
            if (this.value) {
                let tgl = new Date(this.value);
                tgl.setMonth(tgl.getMonth() + 1);
                let year = tgl.getFullYear();
                let month = String(tgl.getMonth() + 1).padStart(2, '0');
                let day = String(tgl.getDate()).padStart(2, '0');
                tglJatuhTempo.value = `${year}-${month}-${day}`;
            }
        });
    }
});
</script>
@endpush
