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
                        <div class="col-md-4 col-lg-5">
                            <input
                                type="text"
                                name="q"
                                class="form-control form-control-sm"
                                placeholder="Cari nama, email, atau no. HP..."
                                value="{{ request('q') }}"
                                autocomplete="off"
                            >
                        </div>
                        <div class="col-md-8 col-lg-7 d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary btn-sm px-3">Cari</button>
                            @if(request('q'))
                                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
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
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th>Akses</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($models as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($models->firstItem() - 1) }}</td>
                                    <td class="fw-medium">{{ $item->name }}</td>
                                    <td>{{ $item->nohp ?: '–' }}</td>
                                    <td class="text-muted">{{ $item->email }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $item->akses == 'admin' ? 'danger' : ($item->akses == 'operator' ? 'primary' : 'success') }} rounded-pill px-3 py-1">
                                            {{ ucfirst($item->akses) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">

                                            {{-- Edit Icon Only --}}
                                            <a href="{{ route($routePrefix . '.edit', $item->id) }}"
                                               class="btn btn-icon btn-outline-warning btn-sm"
                                               title="Edit {{ $item->name }}"
                                               aria-label="Edit {{ $item->name }}">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            {{-- Delete Icon Only --}}
                                            @if($item->email !== config('app.admin_default_email'))
                                                <form action="{{ route($routePrefix . '.destroy', $item->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus user: {{ $item->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-icon btn-outline-danger btn-sm"
                                                            title="Hapus {{ $item->name }}"
                                                            aria-label="Hapus {{ $item->name }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Tidak ada data user ditemukan.
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
