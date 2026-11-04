{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Detail Wali Murid
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Menampilkan detail profil wali murid + manajemen data anak (siswa).
| Fitur       :
|   - Tampilan detail data wali (nama, email, nohp, dll)
|   - Form tambah anak (select2 dropdown siswa — searchable via layout global)
|   - Tabel daftar anak yang sudah terdaftar
|   - Tombol hapus relasi anak
|   - Responsif di desktop & mobile
|   - UX Friendly: form validation, konfirmasi hapus, spacing konsisten
|
--}}

@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    {{ $title ?? 'Detail Wali Murid' }}
                </h5>
                <a href="{{ route('wali.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    {{-- === SECTION: DETAIL DATA WALI === --}}
                    <div class="col-12 col-md-8">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="fw-bold mb-4">
                                <i class="fa fa-user me-2"></i>
                                Informasi Wali Murid
                            </h6>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-primary rounded p-2 me-3">
                                            <i class="fa fa-id-badge fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">ID</div>
                                            <div class="fw-semibold">#{{ $model->id }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-info rounded p-2 me-3">
                                            <i class="fa fa-user fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Nama Lengkap</div>
                                            <div class="fw-semibold">{{ $model->name }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-success rounded p-2 me-3">
                                            <i class="fa fa-envelope fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Email</div>
                                            <div class="fw-semibold">{{ $model->email }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-warning rounded p-2 me-3">
                                            <i class="fa fa-phone fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">No. HP</div>
                                            <div class="fw-semibold">{{ $model->nohp ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-secondary rounded p-2 me-3">
                                            <i class="fa fa-calendar-plus fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Dibuat Pada</div>
                                            <div class="fw-semibold">{{ optional($model->created_at)->format('d M Y H:i') ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-label-info rounded p-2 me-3">
                                            <i class="fa fa-history fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">Diubah Terakhir</div>
                                            <div class="fw-semibold">{{ optional($model->updated_at)->format('d M Y H:i') ?? '–' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- === SECTION: TAMBAH DATA ANAK === --}}
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-light">
                                <h6 class="mb-0" style="color: white !important;">
                                    <i class="fa fa-plus-circle me-2"></i>
                                    Tambah Data Anak
                                </h6>
                            </div>
                            <div class="card-body">
                                {!! Form::open(['route' => 'walisiswa.store', 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) !!}
                                    {!! Form::hidden('wali_id', $model->id) !!}

                                    <div class="mb-3">
                                        {!! Form::label('siswa_id', 'Pilih Siswa', ['class' => 'form-label fw-semibold']) !!}
                                        {!! Form::select('siswa_id', $siswa, null, [
                                            'class' => 'form-select select2' . ($errors->has('siswa_id') ? ' is-invalid' : ''),
                                            'id' => 'siswa_id',
                                            'placeholder' => '-- Pilih Siswa --',
                                            'required'
                                        ]) !!}
                                        @error('siswa_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex gap-2">
                                        {!! Form::button('<i class="fa fa-floppy-disk me-1"></i> Simpan', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-primary'
                                        ]) !!}
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fa fa-rotate me-1"></i> Reset
                                        </button>
                                    </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>

                    {{-- === SECTION: DATA ANAK === --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fa fa-child me-2"></i>
                                    Data Anak
                                </h6>
                                <span class="badge bg-label-info rounded-pill">
                                    {{ $model->siswa->count() }} anak terdaftar
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 5%">#</th>
                                                <th>NISN</th>
                                                <th>Nama Siswa</th>
                                                <th>Kelas</th>
                                                <th class="text-center" style="width: 100px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($model->siswa as $item)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>{{ $item->nisn ?? '–' }}</td>
                                                    <td class="fw-semibold">{{ $item->nama }}</td>
                                                    <td>
                                                        <span class="badge bg-label-{{ $item->kelas == 'VII' ? 'success' : ($item->kelas == 'VIII' ? 'warning' : 'danger') }} rounded-pill px-2 py-1">
                                                            {{ $item->kelas }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        {!! Form::open([
                                                            'route' => ['walisiswa.update', $item->id],
                                                            'method' => 'PUT',
                                                            'onsubmit' => 'return confirm("⚠️ Yakin ingin menghapus relasi dengan siswa: ' . $item->nama . '?")',
                                                            'class' => 'd-inline'
                                                        ]) !!}
                                                            <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus Relasi">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        {!! Form::close() !!}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <i class="fa fa-child fs-1 text-muted mb-2 d-block"></i>
                                                        <p class="text-muted mb-0">Belum ada data anak terdaftar.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
