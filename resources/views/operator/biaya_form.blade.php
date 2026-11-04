{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Form Biaya
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Menampilkan form tambah/edit data biaya.
| Fitur       :
|   - Form dinamis (bisa untuk create & edit)
|   - Validasi error dengan feedback visual
|   - Input nominal otomatis format rupiah (masking)
|   - Tombol aksi: Simpan & Batal
|   - Responsif di desktop & mobile
|   - Clean Code: struktur blade rapi, komentar jelas
|
| Variabel yang diharapkan dari Controller:
|   - $title   → Judul halaman (misal: "Form Tambah Data Biaya")
|   - $model   → Instance model Biaya (atau new Model untuk create)
|   - $method  → HTTP method ('POST' untuk create, 'PUT' untuk edit)
|   - $route   → Route tujuan (misal: 'biaya.store' atau ['biaya.update', $id])
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

                    {{-- Input: Nama Biaya --}}
                    <div class="col-12">
                        <label for="nama" class="form-label fw-semibold">Nama Biaya <span class="text-danger">*</span></label>
                        {!! Form::text('nama', null, [
                            'class'       => 'form-control' . ($errors->has('nama') ? ' is-invalid' : ''),
                            'id'          => 'nama',
                            'placeholder' => 'Contoh: SPP Bulanan, Uang Gedung',
                            'required',
                            'autofocus'
                        ]) !!}
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Masukkan nama biaya yang deskriptif.</div>
                    </div>

                    {{-- Input: Jumlah / Nominal --}}
                    <div class="col-12">
                        <label for="jumlah" class="form-label fw-semibold">Jumlah / Nominal <span class="text-danger">*</span></label>
                        {!! Form::text('jumlah', old('jumlah', $model->jumlah ? number_format($model->jumlah, 0, ',', '.') : null), [
                            'class'       => 'form-control rupiah' . ($errors->has('jumlah') ? ' is-invalid' : ''),
                            'id'          => 'jumlah',
                            'placeholder' => 'Contoh: 150000',
                            'required',
                            'oninput'     => 'formatRupiahInput(this)'
                        ]) !!}
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Nominal akan otomatis diformat ke Rupiah (Rp).</div>
                    </div>

                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex gap-2 mt-4">
                    {!! Form::submit($button, ['class' => 'btn btn-primary px-4']) !!}
                    <a href="{{ route('biaya.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function formatRupiahInput(el) {
        let value = el.value.replace(/[^0-9]/g, '');

        if (!value) {
            el.value = '';
            return;
        }

        let sisa = value.length % 3;
        let rupiah = value.substr(0, sisa);
        let ribuan = value.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        el.value = rupiah;
    }
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
