@extends('layouts.app_sneat')

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 position-relative">

                {{-- Tombol Kembali --}}
                <div class="position-absolute top-0 end-0 me-3 mt-3">
                    <a href="{{ route('tagihan.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                {{-- Header Modul --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar avatar-md bg-label-danger p-2 rounded shadow-sm">
                        <i class="bx bx-trash fs-2 text-danger"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-danger">Hapus Tagihan Massal</h5>
                        <p class="text-muted mb-0 small">Gunakan fitur ini untuk menghapus item tagihan tertentu yang belum dibayar.</p>
                    </div>
                </div>

                <div class="alert alert-dark border-0 shadow-none d-flex align-items-center mb-4" role="alert">
                    <i class="bx bx-info-circle me-2 fs-4"></i>
                    <div>
                        Sistem hanya akan menghapus tagihan dengan status <span class="badge bg-warning text-dark rounded-pill">BELUM BAYAR</span>.
                    </div>
                </div>

                <hr class="my-4">

                <form action="{{ route('tagihan.hapus.massal.proses') }}" method="POST" id="formHapus">
                    @csrf
                    <div class="row g-4">

                        {{-- Kiri: Preview Hitung Otomatis --}}
                        <div class="col-md-4">
                            <div class="bg-light p-4 rounded text-center border h-100 d-flex flex-column justify-content-center shadow-sm">
                                <span class="text-muted d-block mb-1 fw-semibold small text-uppercase">Data Akan Dihapus</span>
                                <h1 class="display-3 fw-bold text-danger mb-0" id="jumlah_data">0</h1>
                                <div class="mt-2">
                                    <span class="badge bg-label-danger px-3 py-2">RECORD TAGIHAN</span>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: Form Filter --}}
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Nama Item Tagihan <span class="text-danger">*</span></label>
                                    <select name="nama_biaya" id="nama_biaya" class="form-select filter-input select2" required>
                                        <option value="">-- Pilih Jenis Tagihan --</option>
                                        @foreach($listBiaya as $nama)
                                            <option value="{{ $nama }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Filter Angkatan</label>
                                    <select name="angkatan" id="angkatan" class="form-select filter-input">
                                        <option value="">Semua Angkatan</option>
                                        @foreach($listAngkatan as $a)
                                            <option value="{{ $a }}">{{ $a }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Filter Kelas</label>
                                    <select name="kelas" id="kelas" class="form-select filter-input">
                                        <option value="">Semua Kelas</option>
                                        @foreach($listKelas as $k)
                                            <option value="{{ $k }}">Kelas {{ $k }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-danger btn-lg w-100 shadow-sm py-3 fw-bold" id="btnSubmit" disabled>
                            <i class="bx bx-trash-alt me-2"></i> JALANKAN PROSES HAPUS MASSAL
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Script AJAX & SweetAlert --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        function updateCount() {
            let namaBiaya = $('#nama_biaya').val();
            let angkatan = $('#angkatan').val();
            let kelas = $('#kelas').val();

            if (namaBiaya) {
                $.get("{{ route('tagihan.hapus.massal.preview') }}", {
                    nama_biaya: namaBiaya,
                    angkatan: angkatan,
                    kelas: kelas
                }, function(data) {
                    $('#jumlah_data').text(data.count);
                    $('#btnSubmit').prop('disabled', data.count === 0);
                });
            } else {
                $('#jumlah_data').text(0);
                $('#btnSubmit').prop('disabled', true);
            }
        }

        // Jalankan updateCount setiap filter berubah
        $('.filter-input').on('change', updateCount);

        // SweetAlert Konfirmasi
        $('#formHapus').on('submit', function(e) {
            e.preventDefault();
            let count = $('#jumlah_data').text();

            Swal.fire({
                title: 'Konfirmasi Penghapusan',
                text: "Anda akan menghapus " + count + " data tagihan. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ee3939',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
