{{--
|--------------------------------------------------------------------------
| VIEW: Operator - Daftar Siswa
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Menampilkan daftar siswa dengan filter status, pencarian, aksi, pagination.
|
--}}

@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
                <div class="d-flex gap-2">
                    {{-- 🔹 TOMBOL PROMOSI — HANYA UNTUK OPERATOR 🔹 --}}
                    @if(auth()->check() && auth()->user()->akses === 'operator')
                        <a href="{{ route('siswa.promosi.form') }}" class="btn btn-outline-success btn-sm px-3">
                            <i class="bx bx-up-arrow-circle me-1"></i> Promosi & Kelulusan
                        </a>
                    @endif

                    {{-- Tombol Export --}}
                    <a href="{{ route('siswa.export') }}" class="btn btn-outline-secondary btn-sm px-3" target="_blank">
                        <i class="bx bx-file me-1"></i> Export Excel
                    </a>
                    {{-- Tombol Tambah --}}
                    <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="fa fa-plus me-1"></i> Tambah Data
                    </a>
                </div>
            </div>

            {{-- 🔹 DASHBOARD STATISTIK KECIL 🔹 --}}
            <div class="card-body border-bottom">
                <div class="row g-3">

                    {{-- Kelas VII (Aktif) --}}
                    <div class="col-6 col-md-2">
                        <div class="bg-label-info rounded p-3 text-center">
                            <div class="fs-5 fw-bold">{{ $stats['kelas_vii'] }}</div>
                            <div class="small">Kelas VII</div>
                        </div>
                    </div>

                    {{-- Kelas VIII (Aktif) --}}
                    <div class="col-6 col-md-2">
                        <div class="bg-label-warning rounded p-3 text-center">
                            <div class="fs-5 fw-bold">{{ $stats['kelas_viii'] }}</div>
                            <div class="small">Kelas VIII</div>
                        </div>
                    </div>

                    {{-- Kelas IX (Aktif) --}}
                    <div class="col-6 col-md-2">
                        <div class="bg-label-primary rounded p-3 text-center">
                            <div class="fs-5 fw-bold">{{ $stats['kelas_ix'] }}</div>
                            <div class="small">Kelas IX</div>
                        </div>
                    </div>

                    {{-- Lulus --}}
                    <div class="col-6 col-md-2">
                        <div class="bg-label-secondary rounded p-3 text-center">
                            <div class="fs-5 fw-bold">{{ $stats['lulus'] }}</div>
                            <div class="small">Lulus</div>
                        </div>
                    </div>

                    {{-- Tidak Aktif (DO) --}}
                    <div class="col-6 col-md-2">
                        <div class="bg-label-danger rounded p-3 text-center">
                            <div class="fs-5 fw-bold">{{ $stats['tidak_aktif'] }}</div>
                            <div class="small">Tidak Aktif</div>
                        </div>
                    </div>

                    {{-- Total Siswa Aktif --}}
                    <div class="col-6 col-md-2">
                        <div class="bg-label-success rounded p-3 text-center">
                            <div class="fs-5 fw-bold">{{ $stats['total_aktif'] }}</div>
                            <div class="small">Siswa Aktif</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- === ISI UTAMA === --}}
            <div class="card-body">

                {{-- === SECTION: PENCARIAN & FILTER === --}}
                <div class="mb-4">
                    {!! Form::open([
                        'route' => $routePrefix . '.index',
                        'method' => 'GET',
                        'class' => 'row g-2'
                    ]) !!}
                        <div class="col-md-4">
                            <input
                                type="text"
                                name="q"
                                class="form-control form-control-sm"
                                placeholder="Cari nama atau NISN..."
                                value="{{ request('q') }}"
                                autocomplete="off"
                            >
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>
                                    Lulus
                                </option>
                                <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>
                                    Tidak Aktif (DO)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary btn-sm px-3">
                                <i class="fa fa-filter me-1"></i> Terapkan Filter
                            </button>
                            @if(request()->anyFilled(['q', 'status']))
                                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                                    <i class="fa fa-times me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                    {!! Form::close() !!}
                </div>

                {{-- === SECTION: TOMBOL AKSI MASSAL === --}}
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    <form id="form-mass-delete" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="ids" id="selected-ids">
                    </form>

                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-selected" disabled>
                        <i class="fa fa-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>

                {{-- === SECTION: TABEL DATA === --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="table-siswa">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th class="text-center" style="width: 5%">#</th>
                                <th>Wali Murid</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th>Kelas</th>
                                <th>Angkatan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($models as $item)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="siswa_ids[]" value="{{ $item->id }}" class="siswa-checkbox">
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $loop->iteration + ($models->firstItem() - 1) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary rounded-pill px-3 py-1">
                                            {{ $item->wali->name ?? '–' }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $item->nama }}</td>
                                    <td>{{ $item->nisn ?? '–' }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $item->kelas == 'VII' ? 'info' : ($item->kelas == 'VIII' ? 'warning' : 'primary') }} rounded-pill">
                                            {{ $item->kelas }}
                                        </span>
                                    </td>
                                    <td>{{ $item->angkatan }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusMap = [
                                                'aktif' => ['label' => 'Aktif', 'color' => 'success', 'icon' => 'bx-check-circle'],
                                                'lulus' => ['label' => 'LULUS', 'color' => 'secondary', 'icon' => 'bx-graduation'],
                                                'tidak_aktif' => ['label' => 'Tidak Aktif', 'color' => 'danger', 'icon' => 'bx-x-circle'],
                                            ];
                                            $statusData = $statusMap[$item->status] ?? ['label' => 'Tidak Diketahui', 'color' => 'light', 'icon' => 'bx-help-circle'];
                                        @endphp
                                        <span class="badge bg-label-{{ $statusData['color'] }} rounded-pill px-2 py-1">
                                            <i class="bx {{ $statusData['icon'] }} me-1"></i> {{ $statusData['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route($routePrefix . '.show', $item->id) }}"
                                               class="btn btn-icon btn-outline-info btn-sm"
                                               title="Detail: {{ $item->nama }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route($routePrefix . '.edit', $item->id) }}"
                                               class="btn btn-icon btn-outline-warning btn-sm"
                                               title="Edit: {{ $item->nama }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route($routePrefix . '.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('⚠️ Hapus {{ $item->nama }}?')">
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
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bx bx-empty fs-1 text-muted mb-2 d-block"></i>
                                        <p class="text-muted mb-0">Tidak ada data siswa.</p>
                                        @if(request()->anyFilled(['q', 'status']))
                                            <small class="d-block mt-1">
                                                <a href="{{ route($routePrefix . '.index') }}" class="text-primary">
                                                    Lihat semua siswa aktif
                                                </a>
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- === PAGINATION === --}}
                @if($models->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Menampilkan {{ $models->firstItem() }} - {{ $models->lastItem() }} dari {{ $models->total() }} data
                        </small>
                        <div>{!! $models->links() !!}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover { background-color: #f9fafb !important; }
    .btn-icon {
        width: 36px; height: 36px; padding: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
    }
    .btn-icon i { margin: 0; }
    .btn-icon:hover { transform: scale(1.05); transition: transform 0.1s ease; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.siswa-checkbox');
        const deleteSelectedBtn = document.getElementById('btn-delete-selected');

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                deleteSelectedBtn.disabled = !selectAll.checked;
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const anyChecked = Array.from(checkboxes).some(c => c.checked);
                deleteSelectedBtn.disabled = !anyChecked;
            });
        });

        deleteSelectedBtn.addEventListener('click', function () {
            const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (!selected.length) return;

            if (!confirm(`⚠️ Yakin hapus ${selected.length} siswa terpilih?`)) return;

            const form = document.getElementById('form-mass-delete');
            document.getElementById('selected-ids').value = JSON.stringify(selected);
            form.action = "{{ route($routePrefix . '.massDestroy') }}";
            form.submit();
        });

        // Fokus ke input pencarian
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput && !searchInput.value) searchInput.focus();
    });
</script>
@endpush
