{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Form Bank Sekolah
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team (Modifikasi oleh AI Assistant)
| Tujuan      : Menampilkan form tambah/edit data rekening bank sekolah.
| Fitur       :
|   - Form dinamis (create & edit)
|   - Validasi error dengan feedback visual
|   - Input wajib bertanda bintang merah
|   - Dropdown bank menggunakan Select2 (hanya di create)
|   - Nomor rekening: numeric input + font monospace
|   - Tombol aksi: Simpan & Batal
|   - Responsif & mobile-friendly
|   - Clean Code: struktur blade rapi, komentar jelas
|
| Variabel yang diharapkan dari Controller:
|   - $title       → Judul halaman
|   - $model       → Instance model BankSekolah
|   - $method      → HTTP method ('POST'/'PUT')
|   - $route       → Route tujuan
|   - $button      → Teks tombol submit
|   - $listbank    → Array [id => nama] untuk dropdown bank (hanya dibutuhkan di create)
|
--}}

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
                        'class'  => 'needs-validation',
                        'novalidate'
                    ]) !!}

                        <div class="row g-3">
                            {{-- Nama Bank (Dropdown Select2) --}}
                            <div class="col-12">
                                <label for="bank_id" class="form-label fw-semibold">
                                    Nama Bank <span class="text-danger">*</span>
                                </label>

                                @if($method === 'POST')
                                    {!! Form::select('bank_id', $listbank ?? [], null, [
                                        'class'        => 'form-control select2' . ($errors->has('bank_id') ? ' is-invalid' : ''),
                                        'id'           => 'bank_id',
                                        'placeholder'  => 'Pilih Bank...',
                                        'required',
                                        'data-allow-clear' => 'true',
                                    ]) !!}
                                    @error('bank_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Pilih bank dari daftar yang tersedia.</div>
                                @else
                                    {{-- Mode Edit: Tampilkan sebagai teks biasa --}}
                                    <input type="text" class="form-control" value="{{ $model->nama_bank }}" readonly>
                                    {!! Form::hidden('bank_id', $model->bank_id) !!} {{-- Tetap kirim bank_id lama --}}
                                @endif
                            </div>

                            {{-- Atas Nama Rekening --}}
                            <div class="col-12">
                                <label for="nama_rekening" class="form-label fw-semibold">
                                    Atas Nama Rekening <span class="text-danger">*</span>
                                </label>
                                {!! Form::text('nama_rekening', null, [
                                    'class'       => 'form-control' . ($errors->has('nama_rekening') ? ' is-invalid' : ''),
                                    'id'          => 'nama_rekening',
                                    'placeholder' => 'Contoh: SMP Muhammadiyah Larangan',
                                    'required',
                                ]) !!}
                                @error('nama_rekening')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nama yang tercantum di buku rekening.</div>
                            </div>

                            {{-- Nomor Rekening --}}
                            <div class="col-12">
                                <label for="nomor_rekening" class="form-label fw-semibold">
                                    Nomor Rekening <span class="text-danger">*</span>
                                </label>
                                {!! Form::text('nomor_rekening', null, [
                                    'class'       => 'form-control font-monospace' . ($errors->has('nomor_rekening') ? ' is-invalid' : ''),
                                    'id'          => 'nomor_rekening',
                                    'placeholder' => 'Contoh: 1234567890',
                                    'required',
                                    'inputmode'   => 'numeric',
                                    'pattern'     => '[0-9]+',
                                    'title'       => 'Hanya angka, tanpa spasi atau simbol',
                                ]) !!}
                                @error('nomor_rekening')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Masukkan nomor rekening tanpa spasi atau karakter khusus.</div>
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex gap-2 mt-4">
                            {!! Form::submit($button, ['class' => 'btn btn-primary px-4']) !!}
                            <a href="{{ route('banksekolah.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .font-monospace {
            font-family: var(--bs-font-monospace);
        }
    </style>
@endpush

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

            // Inisialisasi Select2 (hanya jika ada)
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').each(function () {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: $(this).attr('placeholder') || 'Pilih...',
                        allowClear: $(this).data('allow-clear') === true
                    });
                });
            }
        });
    </script>
@endpush
