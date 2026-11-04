{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Daftar Bank Sekolah
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team (Diperbarui)
| Tujuan      : Menampilkan daftar rekening bank sekolah dengan tampilan profesional.
| Fitur       :
|   - Tombol tambah data (ikon + teks)
|   - Pencarian berdasarkan nama bank / nomor rekening
|   - Tabel responsif dengan aksi ikon-only (Edit/Hapus)
|   - Format data jelas: kode, nama bank, atas nama, nomor rekening
|   - Responsif & UX-friendly
|
| Variabel dari Controller:
|   - $title, $routePrefix, $models
|
--}}

@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
                    <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="fa fa-plus me-1"></i> Tambah Data
                    </a>
                </div>
            </div>
            <div class="card-body">

                {{-- Pencarian --}}
                <div class="mb-4">
                    {!! Form::open([
                        'route' => $routePrefix . '.index',
                        'method' => 'GET',
                        'class' => 'row g-2'
                    ]) !!}
                        <div class="col-md-5">
                            <input
                                type="text"
                                name="q"
                                class="form-control form-control-sm"
                                placeholder="Cari nama bank atau nomor rekening..."
                                value="{{ request('q') }}"
                                autocomplete="off"
                            >
                        </div>
                        <div class="col-md-7 d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary btn-sm px-3">
                                <i class="fa fa-search me-1"></i> Cari
                            </button>
                            @if(request('q'))
                                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                                    <i class="fa fa-times me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                    {!! Form::close() !!}
                </div>

                {{-- Tabel Data --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>Kode</th>
                                <th>Nama Bank</th>
                                <th>Atas Nama</th>
                                <th>Nomor Rekening</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($models as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($models->firstItem() - 1) }}</td>
                                    <td>
                                        <span class="badge bg-light text-secondary border border-secondary rounded-pill px-2 py-1 fs-6">
                                            {{ $item->kode ?? '–' }}
                                        </span>
                                    </td>
                                    <td class="fw-medium">{{ $item->nama_bank }}</td>
                                    <td>{{ $item->nama_rekening }}</td>
                                    <td class="font-monospace">{{ $item->nomor_rekening }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            {{-- Edit --}}
                                            <a href="{{ route($routePrefix . '.edit', $item->id) }}"
                                               class="btn btn-icon btn-outline-warning btn-sm"
                                               title="Edit {{ $item->nama_bank }}"
                                               aria-label="Edit {{ $item->nama_bank }}">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            {{-- Hapus --}}
                                            <form action="{{ route($routePrefix . '.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus bank: {{ $item->nama_bank }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-icon btn-outline-danger btn-sm"
                                                        title="Hapus {{ $item->nama_bank }}"
                                                        aria-label="Hapus {{ $item->nama_bank }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Tidak ada data bank ditemukan.
                                        @if(request('q'))
                                            <br><a href="{{ route($routePrefix . '.index') }}" class="text-primary mt-1 d-inline">Lihat semua data</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($models->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Menampilkan {{ $models->firstItem() }}–{{ $models->lastItem() }} dari {{ $models->total() }} data
                        </small>
                        <div>
                            {{ $models->links() }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: #f9fafb !important;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .btn-icon i {
        margin: 0;
    }

    .btn-icon:hover {
        transform: scale(1.05);
        transition: transform 0.1s ease;
    }

    .font-monospace {
        font-family: var(--bs-font-monospace);
        font-size: 0.95rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.querySelector('input[name="q"]');
        if (input && !input.value) input.focus();
    });
</script>
@endpush
