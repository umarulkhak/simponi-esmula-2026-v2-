{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Daftar Biaya
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Menampilkan daftar biaya dengan fitur pencarian, aksi massal, pagination.
| Fitur       :
|   - Tombol tambah data
|   - Pencarian berdasarkan nama biaya
|   - Tabel responsif dengan aksi: Edit, Hapus (icon-only)
|   - Hapus massal & hapus semua (opsional)
|   - Format rupiah otomatis
|   - Pagination Bootstrap
|   - Responsif & UX friendly
|
| Variabel dari Controller:
|   - $title
|   - $routePrefix (misal: 'operator.biaya')
|   - $models → paginate collection
|
--}}

@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
                <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm px-3">
                    <i class="fa fa-plus me-1"></i> Tambah Data
                </a>
            </div>
            <div class="card-body">

                {{-- === SECTION: PENCARIAN === --}}
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
                                placeholder="Cari nama biaya..."
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
                                    <i class="fa fa-x me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                    {!! Form::close() !!}
                </div>

                {{-- === SECTION: TOMBOL AKSI MASSAL === --}}
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    {{-- Tidak perlu form tersembunyi lagi --}}
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-selected" disabled>
                        <i class="fa fa-trash me-1"></i> Hapus Terpilih
                    </button>

                    {{-- Opsional: Hapus semua --}}
                    {{-- @if($models->total() > 0 && !request('q'))
                        <button type="button" class="btn btn-outline-dark btn-sm" id="btn-delete-all"
                                onclick="confirmDeleteAll()">
                            <i class="bx bx-ban me-1"></i> Hapus Semua
                        </button>
                    @endif --}}
                </div>

                {{-- === SECTION: TABEL DATA === --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="table-biaya">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th class="text-center" style="width: 5%">#</th>
                                <th>Nama Biaya</th>
                                <th>Jumlah</th>
                                <th>Dibuat Oleh</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($models as $item)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="biaya_ids[]" value="{{ $item->id }}" class="biaya-checkbox">
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $loop->iteration + ($models->firstItem() - 1) }}
                                    </td>
                                    <td class="fw-semibold">{{ $item->nama }}</td>
                                    <td>
                                        <span class="badge bg-label-success rounded-pill px-3 py-1">
                                            {{ $item->formatRupiah('jumlah') }}
                                        </span>
                                    </td>
                                    <td>{{ $item->user->name ?? '–' }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            {{-- Edit --}}
                                            <a href="{{ route($routePrefix . '.edit', $item->id) }}"
                                               class="btn btn-icon btn-outline-warning btn-sm"
                                               title="Edit: {{ $item->nama }}">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            {{-- Hapus --}}
                                            <form action="{{ route($routePrefix . '.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('⚠️ Yakin hapus biaya: {{ $item->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-icon btn-outline-danger btn-sm"
                                                        title="Hapus: {{ $item->nama }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bx bx-empty fs-1 text-muted mb-2 d-block"></i>
                                        <p class="text-muted mb-0">Data tidak ditemukan.</p>
                                        @if(request('q'))
                                            <small class="d-block mt-1">
                                                <a href="{{ route($routePrefix . '.index') }}" class="text-primary">
                                                    Lihat semua data
                                                </a>
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- === SECTION: PAGINATION === --}}
                @if($models->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Menampilkan {{ $models->firstItem() }} - {{ $models->lastItem() }} dari {{ $models->total() }} data
                        </small>
                        <div>
                            {!! $models->links() !!}
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
    .btn-icon i { margin: 0; }
    .btn-icon:hover {
        transform: scale(1.05);
        transition: transform 0.1s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.biaya-checkbox');
        const deleteSelectedBtn = document.getElementById('btn-delete-selected');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateDeleteButton();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateDeleteButton);
        });

        function updateDeleteButton() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            deleteSelectedBtn.disabled = !anyChecked;
        }

        // ✅ PERBAIKAN UTAMA: Kirim sebagai form dengan input array
        deleteSelectedBtn.addEventListener('click', function () {
            const selected = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selected.length === 0) return;

            if (!confirm(`⚠️ Yakin ingin menghapus ${selected.length} biaya yang dipilih?`)) {
                return;
            }

            // Buat form dinamis
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route($routePrefix . '.massDestroy') }}";
            form.style.display = 'none';

            // CSRF Token
            const csrf = document.createElement('input');
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            // Method spoofing (DELETE)
            const method = document.createElement('input');
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            // Kirim setiap ID sebagai input array: ids[]
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]'; // ⬅️ INI KUNCI: Laravel akan parse jadi array
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });

        // ✅ Hapus semua — tetap pakai cara yang sama (lebih konsisten)
        window.confirmDeleteAll = function () {
            if (!confirm('⚠️ YAKIN hapus SEMUA biaya? Tindakan ini tidak bisa dibatalkan!')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route($routePrefix . '.massDestroy') }}";
            form.style.display = 'none';

            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="delete_all" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        };

        const searchInput = document.getElementById('q');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
    });
</script>
@endpush
