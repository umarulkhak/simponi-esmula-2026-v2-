{{--
|--------------------------------------------------------------------------
| VIEW: Wali - Detail Tagihan Siswa (Mobile Optimized) - PROFESIONAL VERSION
|--------------------------------------------------------------------------
| Fitur:
|   - Kolom Status Konfirmasi: '-', 'Menunggu', 'Sudah'
|   - Invoice rapi, mirip contoh kampus
|   - Logo sekolah, nomor invoice, catatan, tanda tangan
--}}

@extends('layouts.app_sneat_wali')

@section('content')

@include('wali.modals.modal_cara_bayar_atm', ['banksekolah' => $banksekolah])
@include('wali.modals.modal_cara_bayar_manual', ['siswa' => $siswa])

<div class="col-12">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

        {{-- Header Info Siswa --}}
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start gap-4 position-relative">

                {{-- Tombol Kembali --}}
                <div class="position-absolute top-0 end-0 me-2 mt-1 me-md-3 mt-md-2">
                    <a href="{{ route('wali.tagihan.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                </div>

                {{-- Foto Siswa --}}
                <div class="flex-shrink-0 text-center mb-3 mb-md-0">
                    @php
                        $fotoPath = $siswa->foto ? \Storage::url($siswa->foto) : asset('images/no-image.png');
                    @endphp
                    <img
                        src="{{ $fotoPath }}"
                        alt="Foto {{ $siswa->nama }}"
                        class="rounded shadow-sm border"
                        style="width: 100px; height: 120px; object-fit: cover;"
                        onerror="this.src='{{ asset('images/no-image.png') }}'">
                </div>

                {{-- Detail Siswa --}}
                <div class="flex-grow-1 w-100">
                    <h6 class="fw-bold mb-2 fs-6 fs-md-5">
                        <i class="bx bx-user-circle me-1"></i> Informasi Siswa
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0 fs-7 fs-md-6">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 35%;">Nama</td>
                                    <td>{{ $siswa->nama ?? '–' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">NISN</td>
                                    <td>{{ $siswa->nisn ?? '–' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Kelas</td>
                                    <td>
                                        <span class="badge bg-label-{{ $siswa->kelas == 'VII' ? 'success' : ($siswa->kelas == 'VIII' ? 'warning' : 'danger') }} rounded-pill px-2 py-1 fs-7">
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

        {{-- Jika tidak ada tagihan --}}
        @if ($tagihanList->isEmpty())
            <div class="text-center py-5 px-3">
                <i class="bx bx-empty fs-1 text-muted mb-3"></i>
                <h5 class="text-muted fw-normal fs-5">Belum ada tagihan tersedia</h5>
                <p class="text-muted small">Silakan cek kembali nanti atau hubungi operator sekolah.</p>
            </div>
        @else
            {{-- Status Global --}}
            <div class="alert alert-{{ $statusGlobal == 'lunas' ? 'success' : ($statusGlobal == 'angsur' ? 'warning' : 'danger') }} d-flex align-items-center mb-3 mx-3 mx-md-4 p-3" role="alert">
                <i class="bx bx-{{ $statusGlobal == 'lunas' ? 'check-circle' : ($statusGlobal == 'angsur' ? 'history' : 'error-circle') }} fs-4 me-2 me-md-3"></i>
                <div class="fs-7 fs-md-6">
                    <strong>Status Pembayaran:</strong>
                    @if ($statusGlobal == 'lunas')
                        <span class="fw-bold">Lunas Semua</span>
                    @elseif ($statusGlobal == 'angsur')
                        <span class="fw-bold">Masih Angsur</span> — Sisa tagihan: <strong class="text-danger">{{ formatRupiah($grandTotal - $totalDibayarDikonfirmasi) }}</strong>
                    @else
                        <span class="fw-bold">Belum Bayar</span> — Total tagihan: <strong class="text-danger">{{ formatRupiah($grandTotal) }}</strong>
                    @endif
                </div>
            </div>

            {{-- Form Pemilihan Tagihan --}}
            <form id="formPembayaran" action="{{ route('wali.pembayaran.create') }}" method="GET" class="mb-4">
                {{-- Tabel Tagihan --}}
                <div class="table-responsive mx-3 mx-md-4 mb-3">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th>Tanggal</th>
                                <th>Rincian</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-center" style="width: 15%;">Status</th>
                                <th class="text-center" style="width: 15%;">Konfirmasi</th>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="select-all" onchange="toggleAll(this)">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tagihanList as $tagihan)
                                @php
                                    $subtotal = $tagihan->tagihanDetails->sum('jumlah_biaya');
                                    // Total yang SUDAH DIKONFIRMASI untuk tagihan ini
                                    $totalBayarDikonfirmasi = $tagihan->pembayaran
                                        ->where('status_konfirmasi', 'sudah')
                                        ->sum('jumlah_dibayar');
                                    $isLunasDikonfirmasi = ($totalBayarDikonfirmasi >= $subtotal);

                                    // Cek apakah ada pembayaran (meski belum dikonfirmasi)
                                    $adaPembayaran = $tagihan->pembayaran->isNotEmpty();
                                @endphp
                                <tr class="border-bottom">
                                    <!-- Nomor Urut -->
                                    <td class="text-center align-middle">
                                        {{ $tagihanList->firstItem() + $loop->index }}
                                    </td>
                                    <td class="align-middle">{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->translatedFormat('d M Y') }}</td>
                                    <td class="align-middle">
                                        <ul class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.3;">
                                            @forelse ($tagihan->tagihanDetails as $detail)
                                                <li>{{ $detail->nama_biaya ?? '–' }}</li>
                                            @empty
                                                <li class="text-muted">Tidak ada rincian</li>
                                            @endforelse
                                        </ul>
                                    </td>
                                    <td class="text-end fw-medium {{ $isLunasDikonfirmasi ? 'text-success' : 'text-danger' }} align-middle">
                                        {{ formatRupiah($subtotal) }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-{{ $isLunasDikonfirmasi ? 'success' : 'danger' }} px-2 py-1 rounded-pill fs-7">
                                            {{ $isLunasDikonfirmasi ? 'Sudah Dibayar' : 'Belum Dibayar' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if (!$adaPembayaran)
                                            <span class="badge bg-secondary px-2 py-1 rounded-pill fs-7">-</span>
                                        @elseif ($tagihan->pembayaran->contains('status_konfirmasi', 'belum'))
                                            <span class="badge bg-warning px-2 py-1 rounded-pill fs-7">Menunggu</span>
                                        @else
                                            <span class="badge bg-success px-2 py-1 rounded-pill fs-7">Sudah</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <input type="checkbox"
                                               name="tagihan_id[]"
                                               value="{{ $tagihan->id }}"
                                               class="tagihan-checkbox"
                                               {{ $isLunasDikonfirmasi ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- pagination links -->
                   <div class="d-flex justify-content-center mt-4">
                        {{ $tagihanList->onEachSide(1)->links() }}
                    </div>
                </div>

                {{-- Rekening Bank & Tombol Konfirmasi --}}
                @if ($statusGlobal != 'lunas')
                    <div class="bg-light border border-info rounded-3 p-3 p-md-4 mt-3 mx-3 mx-md-4 mb-4">
                        <p class="mb-3 text-muted fs-7">
                            Pilih tagihan di atas, lalu pilih rekening bank untuk konfirmasi pembayaran.
                        </p>

                        <div class="row g-3">
                            @foreach ($banksekolah as $itemBank)
                                <div class="col-12">
                                    <div class="bg-white border rounded-3 p-3 shadow-sm">
                                        <div class="mb-2">
                                            <strong class="text-muted fs-7">Bank Tujuan</strong>
                                            <p class="mb-0 fs-6">{{ $itemBank->nama_bank }}</p>
                                        </div>
                                        <div class="mb-2">
                                            <strong class="text-muted fs-7">Nomor Rekening</strong>
                                            <p class="mb-0 fs-6">{{ $itemBank->nomor_rekening }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong class="text-muted fs-7">Atas Nama</strong>
                                            <p class="mb-0 fs-6">{{ $itemBank->nama_rekening }}</p>
                                        </div>

                                        <input type="hidden" name="bank_sekolah_id" value="{{ $itemBank->id }}">
                                        <button type="submit" class="btn btn-dark w-100 fs-6">
                                            Konfirmasi Pembayaran
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <ul class="mb-2 small">
                                <li>
                                    <a href="#" class="text-decoration-none text-primary fs-7" data-bs-toggle="modal" data-bs-target="#modalCaraBayarATM">
                                        Cara Bayar via ATM / m-Banking.
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-decoration-none text-primary fs-7" data-bs-toggle="modal" data-bs-target="#modalCaraBayarManual">
                                        Cara Bayar Langsung di Sekolah
                                    </a>
                                </li>
                            </ul>
                            <p class="text-muted small fs-7">
                                Upload bukti pembayaran setelah transfer.
                            </p>
                        </div>
                    </div>
                @endif
            </form>

            {{-- Jika Lunas & Dikonfirmasi — Tampilkan tombol cetak --}}
            @if ($semuaDikonfirmasi ?? false)
                <div class="bg-success-light text-dark rounded-3 p-3 p-md-4 mt-3 mx-3 mx-md-4 d-flex align-items-start">
                    <i class="bx bx-check-circle fs-4 me-2 me-md-3 text-success flex-shrink-0"></i>
                    <div>
                        <h5 class="mb-2 fw-bold fs-6">🎉 Terima Kasih!</h5>
                        <p class="mb-0 fs-7">
                            Semua tagihan untuk <strong>{{ $siswa->nama }}</strong> telah lunas dan dikonfirmasi.
                            <br>Terima kasih atas kepercayaan Anda.
                        </p>
                    </div>
                </div>

                <div class="text-center my-3 mx-3 mx-md-4">
                    <a href="{{ route('wali.invoice.cetak', $siswa) }}" target="_blank" class="btn btn-dark w-100 w-md-auto px-4 py-2 fs-6">
                        <i class="bx bx-printer me-2"></i> Cetak Invoice (PDF)
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8f9fa;
    }

    .card {
        margin: 0 auto;
    }

    .table thead th {
        font-weight: 500;
        font-size: 0.85rem;
        color: #6c757d;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }

    .bg-success-light {
        background-color: #e8f5e8 !important;
        border: 1px solid #c8e6c9 !important;
        color: #2e7d32 !important;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .btn-dark {
        background-color: #2A363B;
        border-color: #2A363B;
        color: #ffffff;
    }

    .btn-dark:hover {
        background-color: #1E2C2F;
        border-color: #1E2C2F;
    }

    .fs-5 { font-size: 1.1rem; }
    .fs-6 { font-size: 1rem; }
    .fs-7 { font-size: 0.9rem; }

    @media (max-width: 576px) {
        .card { margin: 0 8px; }
        .p-3 { padding: 1rem !important; }
        .mx-3 { margin-left: 1rem !important; margin-right: 1rem !important; }
        .fs-5 { font-size: 1rem; }
        .fs-6 { font-size: 0.95rem; }
        .fs-7 { font-size: 0.875rem; }
        img[style*="width: 100px"] {
            width: 90px !important;
            height: 110px !important;
        }
        .table thead th { font-size: 0.8rem; }
        .badge { font-size: 0.75rem; padding: 0.35em 0.5em; }
    }

    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }
        .no-print { display: none !important; }
        table { page-break-inside: avoid; }
        tr { page-break-inside: avoid; }
    }
</style>
@endpush

@push('scripts')
<script>
    function cetakInvoice() {
        const namaSiswa = "{{ $siswa->nama }}";
        const nisn = "{{ $siswa->nisn ?? '–' }}";
        const kelas = "{{ $siswa->kelas ?? '–' }}";
        const angkatan = "{{ $siswa->angkatan ?? '–' }}";
        const tanggalCetak = new Date().toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        const now = new Date();
        const invNumber = `INV/ESMULA/${now.getFullYear()}/${String(now.getMonth() + 1).padStart(2, '0')}/${String(now.getDate()).padStart(2, '0')}`;

        let detailTagihan = '';
        @foreach ($tagihanList as $tagihan)
            @php $subtotal = $tagihan->tagihanDetails->sum('jumlah_biaya'); @endphp
            detailTagihan += `
            <tr>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #ccc; padding: 6px;">{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->translatedFormat('d F Y') }}</td>
                <td style="border: 1px solid #ccc; padding: 6px;">
                    <ul style="margin: 0; padding-left: 16px; font-size: 12px; line-height: 1.4;">
                        @foreach ($tagihan->tagihanDetails as $detail)
                            <li>{{ $detail->nama_biaya }}</li>
                        @endforeach
                    </ul>
                </td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold;">{{ formatRupiah($subtotal) }}</td>
            </tr>`;
        @endforeach

        const logoSrc = "{{ asset('sneat/assets/img/logo-esmula.png') }}";
        const total = "{{ formatRupiah($grandTotal) }}";
        const isLunas = {{ $semuaDikonfirmasi ? 'true' : 'false' }};

        // ✅ Watermark LOGO saja (tanpa tulisan)
        const watermarkHTML = `
            <div style="
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 0;
                pointer-events: none;
                user-select: none;
            ">
                <img src='${logoSrc}' style="width: 360px; opacity: 0.07;" alt="Watermark Logo">
            </div>
        `;

        const printHTML = `
        <div id="print-area" style="
            font-family: 'Poppins', sans-serif;
            color: #000;
            padding: 30px 40px;
            max-width: 850px;
            height: 1150px;
            margin: 0 auto;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            page-break-after: avoid;
        ">
            ${watermarkHTML}

            <div style="position: relative; z-index: 1; transform: scale(0.95); transform-origin: top;">
                <!-- Header -->
                <table style="width: 100%; border-bottom: 4px solid #1565c0; margin-bottom: 15px;">
                    <tr>
                        <td style="width: 18%; text-align: center;">
                            <img src="${logoSrc}" alt="Logo Sekolah"
                                style="width: 80px; height: auto; background: white; border-radius: 8px; border: 1px solid #ddd; padding: 4px;">
                        </td>
                        <td style="text-align: left; padding-left: 10px;">
                            <h2 style="margin: 0; color: #0d47a1; font-size: 20px; font-weight: 700;">SMP MUHAMMADIYAH LARANGAN</h2>
                            <p style="margin: 4px 0; font-size: 12px; color: #444;">
                                Karangancol, Larangan, Kab. Brebes, Jawa Tengah 52262<br>
                                Telp: (0283) 6183947 | Email: smpmuhammadiyahtarangan@gmail.com
                            </p>
                        </td>
                        <td style="text-align: right;">
                            <div style="background: #1565c0; color: white; padding: 8px 12px; border-radius: 6px; font-size: 16px; font-weight: bold;">
                                INVOICE
                            </div>
                            <div style="margin-top: 6px; font-size: 12px;">
                                <strong>No. Dokumen:</strong><br>
                                <span style="font-family: monospace; color: #0d47a1;">${invNumber}</span>
                            </div>
                            <div style="margin-top: 4px; font-size: 12px; color: #555;">
                                Tanggal: ${tanggalCetak}
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Info Siswa -->
                <div style="background: #e3f2fd; padding: 10px 16px; border-radius: 6px; margin-bottom: 15px;">
                    <h4 style="margin: 0 0 8px 0; color: #0d47a1; font-size: 14px; border-bottom: 2px solid #fbc02d; padding-bottom: 3px;">
                        Informasi Siswa
                    </h4>
                    <table style="width: 100%; font-size: 12px;">
                        <tr><td style="width: 25%; font-weight: 600;">Nama Lengkap</td><td>: ${namaSiswa}</td></tr>
                        <tr><td style="font-weight: 600;">NISN</td><td>: ${nisn}</td></tr>
                        <tr><td style="font-weight: 600;">Kelas</td><td>: ${kelas}</td></tr>
                        <tr><td style="font-weight: 600;">Angkatan</td><td>: ${angkatan}</td></tr>
                    </table>
                </div>

                <!-- Rincian -->
                <h4 style="margin: 0 0 8px 0; color: #0d47a1; font-size: 14px; border-left: 4px solid #fbc02d; padding-left: 8px;">
                    Rincian Tagihan Pembayaran
                </h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px;">
                    <thead>
                        <tr style="background-color: #bbdefb; color: #0d47a1;">
                            <th style="border: 1px solid #ccc; padding: 6px;">No</th>
                            <th style="border: 1px solid #ccc; padding: 6px;">Tanggal</th>
                            <th style="border: 1px solid #ccc; padding: 6px;">Rincian</th>
                            <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detailTagihan}
                        <tr style="font-weight: bold; background-color: #fff9c4;">
                            <td colspan="3" style="border: 1px solid #ccc; padding: 8px; text-align: right;">TOTAL PEMBAYARAN</td>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${total}</td>
                        </tr>
                        ${isLunas ? `
                            <tr>
                                <td colspan="4" style="border: 1px solid #ccc; padding: 8px; background: #e8f5e9; color: #2e7d32; text-align: center; font-weight: bold;">
                                    ✅ Semua tagihan telah LUNAS dan dikonfirmasi oleh bendahara sekolah.
                                </td>
                            </tr>` : ''}
                    </tbody>
                </table>

                <!-- Rekening -->
                <div style="background: #f5f9ff; padding: 10px; border: 1px solid #bbdefb; border-radius: 6px; margin-bottom: 10px; font-size: 12px;">
                    <strong>Rekening Resmi Sekolah:</strong><br>
                    @foreach ($banksekolah as $bank)
                        • {{ $bank->nama_bank }} a/n {{ $bank->nama_rekening }} – {{ $bank->nomor_rekening }}<br>
                    @endforeach
                </div>

                <!-- Catatan -->
                <ol style="font-size: 11px; color: #333; margin-left: 20px; line-height: 1.4;">
                    <li>Pembayaran yang telah dikonfirmasi oleh admin dianggap sah dan tidak dapat dikembalikan.</li>
                    <li>Invoice ini berlaku sebagai bukti pembayaran resmi dari SMP Muhammadiyah Larangan.</li>
                    <li>Untuk pertanyaan lebih lanjut, silakan hubungi bagian keuangan sekolah.</li>
                </ol>

                <!-- Tanda tangan -->
                <div style="width: 100%; margin-top: 20px; text-align: right;">
                    <div style="display: inline-block; text-align: center; min-width: 220px;">
                        <p style="margin: 0; font-size: 12px;">Hormat Kami,</p>
                        <div style="margin-top: 35px; font-weight: bold; font-size: 12px; border-top: 1px solid #000; padding-top: 4px;">
                            Bendahara Sekolah
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div style="margin-top: 20px; border-top: 1px dashed #ccc; text-align: center; font-size: 11px; color: #777; padding-top: 6px;">
                    <p style="margin: 0;">Sistem Pembayaran Digital – SMP Muhammadiyah Larangan</p>
                    <p style="margin: 2px 0 0;">© ${now.getFullYear()} – Semua hak dilindungi</p>
                </div>
            </div>
        </div>`;

        const original = document.body.innerHTML;
        document.body.innerHTML = printHTML;
        window.print();
        document.body.innerHTML = original;
        setTimeout(() => window.location.reload(), 100);
    }


    function toggleAll(source) {
        document.querySelectorAll('.tagihan-checkbox:not(:disabled)').forEach(checkbox => {
            checkbox.checked = source.checked;
        });
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const anyChecked = document.querySelector('.tagihan-checkbox:checked');
        const buttons = document.querySelectorAll('#formPembayaran button[type="submit"]');
        buttons.forEach(btn => {
            btn.disabled = !anyChecked;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tagihan-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSubmitButton);
        });
        updateSubmitButton();
    });
</script>
@endpush
