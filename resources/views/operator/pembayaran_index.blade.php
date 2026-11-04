@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Data Pembayaran</h5>
            </div>
            <div class="card-body">

                {{-- ================= CARD UANG MASUK ================= --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar bg-label-success">
                                    <i class="fa fa-calendar-day"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Hari Ini</small>
                                    <h6 class="fw-bold mb-0">
                                        {{ formatRupiah($uangMasukHariIni) }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar bg-label-info">
                                    <i class="fa fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Bulan Ini</small>
                                    <h6 class="fw-bold mb-0">
                                        {{ formatRupiah($uangMasukBulanIni) }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar bg-label-warning">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Tahun Ini</small>
                                    <h6 class="fw-bold mb-0">
                                        {{ formatRupiah($uangMasukTahunIni) }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border border-primary">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar bg-label-primary">
                                    <i class="fa fa-wallet"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Total</small>
                                    <h6 class="fw-bold mb-0">
                                        {{ formatRupiah($totalUangMasuk) }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ================= END CARD ================= --}}

                {{-- === FLASH MESSAGE === --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                        <i class="fa fa-check-circle me-2"></i> {!! session('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @elseif(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- === FILTER === --}}
                <div class="card mb-4 border">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <i class="fa fa-filter text-muted"></i>
                        <h6 class="mb-0 fw-bold">Filter Data</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-12 col-md-2">
                                <label class="form-label small">Bulan</label>
                                <select name="bulan" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                            {{ request('bulan') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small">Tahun</label>
                                <select name="tahun" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    @for ($y = date('Y'); $y >= 2022; $y--)
                                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Dikonfirmasi</option>
                                    <option value="sudah" {{ request('status') == 'sudah' ? 'selected' : '' }}>Sudah Dikonfirmasi</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small">Kelas</label>
                                <select name="kelas" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    <option value="VII" {{ request('kelas') == 'VII' ? 'selected' : '' }}>VII</option>
                                    <option value="VIII" {{ request('kelas') == 'VIII' ? 'selected' : '' }}>VIII</option>
                                    <option value="IX" {{ request('kelas') == 'IX' ? 'selected' : '' }}>IX</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small">Cari Siswa</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="q" class="form-control" placeholder="Nama/NISN..."
                                        value="{{ request('q') }}">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fa fa-filter me-1"></i> Filter
                                    </button>
                                    @if(request()->anyFilled(['q', 'bulan', 'tahun', 'status', 'kelas']))
                                        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary">
                                            <i class="fa fa-times me-1"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- === AKSI MASSAL === --}}
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    <form id="form-mass-delete" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>

                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-selected" disabled>
                        <i class="fa fa-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>

                {{-- === TABEL DATA === --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="table-pembayaran">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th class="text-center" style="width: 5%">#</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Wali</th>
                                <th class="text-center">Kelas</th>
                                <th class="text-center">Tgl. Konfirmasi</th>
                                <th class="text-center">Status Konfirmasi</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $item)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="pembayaran_ids[]" value="{{ $item->id }}" class="pembayaran-checkbox">
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $loop->iteration + ($results->firstItem() - 1) }}
                                    </td>
                                    <td><span class="badge bg-label-dark rounded-pill px-2 py-1">{{ $item->nisn }}</span></td>
                                    <td>
                                        <strong>{{ $item->nama_siswa }}</strong>
                                        <div class="text-muted small">Angkatan: {{ $item->angkatan }}</div>
                                    </td>
                                    <td>
                                        @if($item->nama_wali)
                                            <span class="badge bg-label-dark rounded-pill px-2 py-1">{{ $item->nama_wali }}</span>
                                        @else
                                            <span class="badge bg-label-secondary rounded-pill px-2 py-1">Tidak Ada Wali</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $kelas = $item->kelas ?? '–';
                                            $colorMap = ['VII' => 'primary', 'VIII' => 'success', 'IX' => 'danger'];
                                            $color = $colorMap[$kelas] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-label-{{ $color }} rounded-pill px-2 py-1">{{ $kelas }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->tanggal_konfirmasi)
                                            <span class="badge bg-label-dark rounded-pill px-2 py-1">
                                                {{ \Carbon\Carbon::parse($item->tanggal_konfirmasi)->translatedFormat('d M Y') }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-warning rounded-pill px-2 py-1">Belum Dikonfirmasi</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->status_konfirmasi == 'belum')
                                            <span class="badge bg-label-warning rounded-pill px-2 py-1">
                                                <i class="fa fa-clock me-1"></i> BELUM
                                            </span>
                                        @else
                                            <span class="badge bg-label-success rounded-pill px-2 py-1">
                                                <i class="fa fa-check-circle me-1"></i> SUDAH
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('pembayaran.show', $item->id) }}"
                                               class="btn btn-icon btn-outline-info btn-sm"
                                               title="Lihat detail">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <form action="{{ route('pembayaran.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('⚠️ Yakin hapus pembayaran ini?\n{{ $item->nama_siswa }} - {{ \Carbon\Carbon::parse($item->tanggal_konfirmasi ?? now())->translatedFormat('M Y') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-icon btn-outline-danger btn-sm"
                                                        title="Hapus pembayaran">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
                                            <i class="fa fa-database fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-1">Belum ada data pembayaran.</p>
                                            @if(request()->anyFilled(['q', 'bulan', 'tahun']))
                                                <small class="mt-1">
                                                    <a href="{{ route('pembayaran.index') }}" class="text-primary">
                                                        Lihat semua data
                                                    </a>
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- === PAGINATION === --}}
                @if($results->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Menampilkan {{ $results->firstItem() }} - {{ $results->lastItem() }} dari {{ $results->total() }} data
                        </small>
                        <div>{!! $results->links() !!}</div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.pembayaran-checkbox');
    const deleteSelectedBtn = document.getElementById('btn-delete-selected');
    const formMassDelete = document.getElementById('form-mass-delete');

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

    deleteSelectedBtn.addEventListener('click', function () {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selected.length === 0) return;

        if (!confirm(`⚠️ Yakin ingin menghapus ${selected.length} pembayaran yang dipilih?`)) {
            return;
        }

        formMassDelete.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="DELETE">
        `;

        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            formMassDelete.appendChild(input);
        });

        formMassDelete.action = "{{ route('pembayaran.massDestroy') }}";
        formMassDelete.submit();
    });
});
</script>
@endpush
