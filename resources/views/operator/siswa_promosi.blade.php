@extends('layouts.app_sneat')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 rounded-3">
            {{-- 🔹 HEADER CARD --}}
            <div class="card-header text-center bg-light border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bx bx-trending-up me-2 text-primary"></i>
                    PROMOSI & KELULUSAN SISWA
                </h5>
                <small class="text-muted d-block mt-1">
                    Tahun Ajaran {{ $tahunSekarang }}/{{ $tahunSekarang + 1 }}
                </small>
            </div>
            <div class="card-body">

                {{-- 🔹 CARD INFO: APA YANG AKAN DILAKUKAN 🔹 --}}
                <div class="card bg-label-info mb-4 mt-2">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start">
                            <i class="bx bx-info-circle me-2 mt-1 fs-5"></i>
                            <div>
                                <strong>Fitur ini akan:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    <li>Naikkan siswa kelas <strong>VII → VIII</strong></li>
                                    <li>Naikkan siswa kelas <strong>VIII → IX</strong></li>
                                    <li>Tandai siswa kelas <strong>IX sebagai LULUS</strong> (status diubah, data tetap tersimpan)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔹 STATISTIK JUMLAH SISWA 🔹 --}}
                <div class="row text-center g-3 mb-4">
                    <div class="col-4">
                        <div class="bg-label-success rounded p-3">
                            <div class="fs-5 fw-bold">{{ $jumlahVII }}</div>
                            <div class="small">Kelas VII</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-label-warning rounded p-3">
                            <div class="fs-5 fw-bold">{{ $jumlahVIII }}</div>
                            <div class="small">Kelas VIII</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-label-info rounded p-3">
                            <div class="fs-5 fw-bold">{{ $jumlahIX }}</div>
                            <div class="small">Kelas IX</div>
                        </div>
                    </div>
                </div>

                {{-- 🔹 CARD WARNING: PERINGATAN 🔹 --}}
                <div class="card bg-label-warning mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start">
                            <i class="bx bx-error me-2 mt-1 fs-5"></i>
                            <div>
                                <strong>Peringatan!</strong> Tindakan ini <strong>tidak bisa dibatalkan</strong>.
                                Pastikan semua data sudah benar sebelum melanjutkan.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔹 FORM DENGAN KONFIRMASI SATU LANGKAH 🔹 --}}
                {!! Form::open(['route' => 'siswa.promosi.proses', 'method' => 'POST']) !!}
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm" id="confirm" required>
                        <label class="form-check-label" for="confirm">
                            Saya yakin ingin memproses promosi & kelulusan tahun {{ $tahunSekarang }}.
                            Data siswa kelas IX akan diubah statusnya menjadi <strong>LULUS</strong>.
                        </label>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="submit" class="btn btn-danger px-4"
                                onclick="return confirm('⚠️ Yakin ingin menjalankan promosi tahun {{ $tahunSekarang }}? Tindakan ini tidak bisa dibatalkan!')">
                            <i class="bx bx-check-circle me-1"></i> Jalankan Promosi
                        </button>
                        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Batal
                        </a>
                    </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>
@endsection
