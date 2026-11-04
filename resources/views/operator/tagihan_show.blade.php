{{-- resources/views/operator/tagihan_show.blade.php --}}
@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">

        {{-- === SECTION: INFO SISWA === --}}
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column flex-md-row align-items-start gap-4 position-relative">

                    {{-- Tombol Kembali --}}
                    <div class="position-absolute top-0 end-0 me-3 mt-3">
                        <a href="{{ route('tagihan.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    {{-- Foto Siswa (Kiri) --}}
                    <div class="flex-shrink-0 text-center">
                        @php
                            $fotoPath = $siswa->foto
                                ? \Storage::url($siswa->foto)
                                : asset('images/no-image.png');
                        @endphp
                        <img
                            src="{{ $fotoPath }}"
                            alt="Foto {{ $siswa->nama }}"
                            class="rounded shadow-sm border"
                            style="width: 150px; height: 180px; object-fit: cover; object-position: center;">
                    </div>

                    {{-- Detail Siswa (Kanan) --}}
                    <div class="flex-grow-1 w-100">
                        <h6 class="fw-bold mb-3">
                            <i class="fa fa-user me-2"></i>
                            Informasi Siswa
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-muted" style="width: 30%;">Nama</td>
                                        <td>{{ $siswa->nama ?? '–' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">NISN</td>
                                        <td>{{ $siswa->nisn ?? '–' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Kelas</td>
                                        <td>
                                            <span class="badge bg-label-{{ $siswa->kelas == 'VII' ? 'primary' : ($siswa->kelas == 'VIII' ? 'success' : 'danger') }} rounded-pill px-2 py-1">
                                                {{ $siswa->kelas }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Angkatan</td>
                                        <td>{{ $siswa->angkatan ?? '–' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- === SECTION: TABEL TAGIHAN === --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Daftar Tagihan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tagihan</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Tagihan</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tagihan as $item)
                                    @php
                                        $periode = $item->tanggal_tagihan
                                            ? \Carbon\Carbon::parse($item->tanggal_tagihan)->translatedFormat('M Y')
                                            : 'Tanpa Periode';
                                    @endphp

                                    @foreach($item->tagihanDetails as $detail)
                                        <tr>
                                            <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                            <td>{{ $detail->nama_biaya ?? '–' }}</td>
                                            <td>{{ 'Rp ' . number_format($detail->jumlah_biaya, 0, ',', '.') }}</td>
                                            <td>{{ $item->tanggal_tagihan ? $item->tanggal_tagihan->format('d M Y') : '–' }}</td>
                                            <td>
                                                @if($item->status == 'lunas')
                                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill">
                                                        <i class="fa fa-check-circle me-1"></i> LUNAS
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                                        <i class="fa fa-clock me-1"></i> BELUM BAYAR
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($loop->first) {{-- Hanya tampilkan aksi di baris pertama detail --}}
                                                    <div class="d-flex justify-content-center gap-1">
                                                        {{-- Jika BELUM BAYAR --}}
                                                        @if($item->status != 'lunas')
                                                            <button
                                                                type="button"
                                                                class="btn btn-icon btn-outline-success btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#bayarModal{{ $item->id }}"
                                                                title="Bayar Tagihan">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        @else
                                                        {{-- Jika SUDAH LUNAS --}}
                                                            <button
                                                                type="button"
                                                                class="btn btn-icon btn-outline-warning btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#batalBayarModal{{ $item->id }}"
                                                                title="Batalkan Pembayaran">
                                                                <i class="fa fa-undo"></i>
                                                            </button>
                                                        @endif

                                                        <form action="{{ route('tagihan.destroySingle', $item->id) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('⚠️ Yakin hapus tagihan ini?\n{{ $siswa->nama }} - {{ $periode }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-icon btn-outline-danger btn-sm"
                                                                    title="Hapus tagihan">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Modal Bayar --}}
                                    @if($item->status != 'lunas')
                                        <div class="modal fade" id="bayarModal{{ $item->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('tagihan.bayar', $item->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Yakin ingin menandai tagihan ini sebagai <strong>LUNAS</strong>?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                                                                <input type="date" name="tanggal_bayar" class="form-control" value="{{ now()->toDateString() }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fa fa-check me-1"></i> Konfirmasi Bayar
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- Modal Batalkan Pembayaran --}}
                                    @if($item->status == 'lunas')
                                    <div class="modal fade" id="batalBayarModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title text-warning">
                                                    <i class="fa fa-exclamation-triangle me-1"></i>
                                                    Batalkan Pembayaran
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <form action="{{ route('tagihan.batal', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-body">
                                                    <p class="mb-0">
                                                        Yakin ingin <strong>membatalkan pembayaran</strong> tagihan ini?
                                                    </p>
                                                    <small class="text-muted">
                                                        Status akan kembali menjadi <b>BELUM BAYAR</b>.
                                                    </small>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-warning btn-sm">
                                                        <i class="fa fa-undo me-1"></i> Ya, Batalkan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fa fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">Tidak ada tagihan untuk siswa ini.</p>
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
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.85rem;
    }
    .btn-icon {
        width: 34px;
        height: 34px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    .btn-icon:hover {
        transform: scale(1.05);
        transition: transform 0.1s ease;
    }
</style>
@endpush
