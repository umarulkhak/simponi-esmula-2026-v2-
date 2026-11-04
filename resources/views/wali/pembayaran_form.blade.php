@extends('layouts.app_sneat_wali')

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 py-3 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bx bx-credit-card me-2"></i>Konfirmasi Pembayaran
                    </h5>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm px-2 rounded-3" title="Kembali">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-3 p-md-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bx bx-check-circle fs-5 me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bx bx-error-circle fs-5 me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {!! Form::open(['route' => 'wali.pembayaran.store', 'method' => 'POST', 'files' => true, 'id' => 'formPembayaran']) !!}

                <!-- REKENING PENGIRIM -->
                <section class="mb-3 mt-0">
                    <div class="divider divider-dark my-2">
                        <div class="divider-text">
                            <h6 class="mb-0"><i class="fa-solid fa-circle-info text-dark me-1"></i>Rekening Pengirim</h6>
                        </div>
                    </div>

                    @if($listWaliBank && count($listWaliBank) > 0)
                        <div class="form-group mb-3" id="formGroupBankTersimpan">
                            <label class="form-label fw-medium" for="wali_bank_id">Bank Tersimpan</label>
                            {!! Form::select('wali_bank_id', $listWaliBank, null, [
                                'class' => 'form-select',
                                'placeholder' => 'Pilih Bank Pengirim',
                                'id' => 'wali_bank_id'
                            ]) !!}
                            @error('wali_bank_id')
                                <div class="text-danger mt-1 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            {!! Form::checkbox('pilihan_bank', 1, false, [
                                'class' => 'form-check-input',
                                'id' => 'checkboxtoggle'
                            ]) !!}
                            <label class="form-check-label" for="checkboxtoggle">
                                Saya punya rekening baru
                            </label>
                        </div>
                    @endif

                    <div id="infoRekeningBaru" class="alert alert-info d-flex align-items-center {{ ($listWaliBank && count($listWaliBank) > 0) ? 'd-none' : '' }} p-2">
                        <i class="bx bx-info-circle me-2"></i>
                        <small>Isi data rekening baru agar operator dapat verifikasi transfer Anda.</small>
                    </div>

                    <div id="formBankBaru" class="mt-3 {{ ($listWaliBank && count($listWaliBank) > 0) ? 'd-none' : '' }}">
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="bank_id" class="form-label fw-medium">Nama Bank Pengirim</label>
                                {!! Form::select('bank_id', $listBank ?? [], null, [
                                    'class' => 'form-select',
                                    'placeholder' => 'Pilih bank pengirim...',
                                    'id' => 'bank_id'
                                ]) !!}
                                @error('bank_id')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium" for="nama_rekening">Nama Pemilik Rekening</label>
                                {!! Form::text('nama_rekening', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Nama sesuai buku tabungan',
                                    'id' => 'nama_rekening'
                                ]) !!}
                                @error('nama_rekening')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium" for="nomor_rekening">Nomor Rekening</label>
                                {!! Form::text('nomor_rekening', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Contoh: 1234567890',
                                    'id' => 'nomor_rekening'
                                ]) !!}
                                @error('nomor_rekening')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    {!! Form::checkbox('simpan_data_rekening', 1, false, [
                                        'class' => 'form-check-input',
                                        'id' => 'defaultCheck3'
                                    ]) !!}
                                    <label for="defaultCheck3" class="form-check-label">
                                        <small>Simpan data ini untuk pembayaran berikutnya</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- REKENING TUJUAN (CARD) -->
                <section class="mb-3">
                    <div class="divider divider-dark my-2">
                        <div class="divider-text">
                            <h6 class="mb-0"><i class="fa-solid fa-circle-info text-dark me-1"></i>Rekening Tujuan</h6>
                        </div>
                    </div>

                    <input type="hidden" name="bank_sekolah_id" id="selected_bank_sekolah_id" value="{{ old('bank_sekolah_id', request('bank_sekolah_id')) }}" required>

                    <div class="row g-3">
                        @forelse($listBankSekolah as $id => $namaBank)
                            @php
                                $bank = $bankSekolahList->firstWhere('id', $id);
                            @endphp
                            <div class="col-12">
                                <div class="card border {{ (old('bank_sekolah_id', request('bank_sekolah_id')) == $id) ? 'border-primary' : 'border-secondary' }} rounded-3 shadow-sm"
                                     style="cursor: pointer; transition: all 0.2s;"
                                     onclick="selectBank({{ $id }})">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $namaBank }}</h6>
                                                <p class="mb-1"><strong>No. Rek:</strong> {{ $bank->nomor_rekening ?? '–' }}</p>
                                                <p class="mb-0"><strong>Atas Nama:</strong> {{ $bank->nama_rekening ?? '–' }}</p>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="bank_radio" id="bank_{{ $id }}"
                                                       {{ (old('bank_sekolah_id', request('bank_sekolah_id')) == $id) ? 'checked' : '' }} disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning small">
                                    Tidak ada rekening bank sekolah tersedia.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @error('bank_sekolah_id')
                        <div class="text-danger mt-2 small">{{ $message }}</div>
                    @enderror
                </section>

                <!-- INFO PEMBAYARAN -->
                <section class="mb-3">
                    <div class="divider divider-dark my-2">
                        <div class="divider-text">
                            <h6 class="mb-0"><i class="fa-solid fa-circle-info text-dark me-1"></i>Info Pembayaran</h6>
                        </div>
                    </div>

                    {{-- Tampilkan rincian tagihan yang dibayar --}}
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">Tagihan yang Dibayar</h6>
                        <div class="bg-light p-2 rounded">
                            @foreach($tagihanSelected as $tagihan)
                                @php $subtotal = $tagihan->tagihanDetails->sum('jumlah_biaya'); @endphp
                                <div class="mb-2 pb-2 border-bottom">
                                    <strong>{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->translatedFormat('d F Y') }}</strong><br>
                                    <ul class="mb-1 ps-3">
                                        @foreach($tagihan->tagihanDetails as $detail)
                                            <li>{{ $detail->nama_biaya }} — {{ formatRupiah($detail->jumlah_biaya) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                            <div class="fw-bold text-end mt-2">
                                <strong>Total Transfer: {{ formatRupiah($totalTagihan) }}</strong>
                            </div>
                        </div>
                        <p class="text-muted small mt-2">
                            <i class="bx bx-info-circle"></i> Anda akan membayar masing-masing tagihan sesuai nilainya.
                            Bukti transfer yang diupload akan digunakan untuk semua tagihan di atas.
                        </p>
                    </div>

                    <div class="row g-2">
                        <div class="col-12">
                            <label for="tanggal_bayar" class="form-label fw-medium">Tanggal Bayar</label>
                            {!! Form::date('tanggal_bayar', date('Y-m-d'), ['class' => 'form-control', 'required' => true]) !!}
                        </div>

                        <div class="col-12">
                            <label for="bukti_bayar" class="form-label fw-medium">
                                <i class="bi bi-upload me-1"></i> Upload Bukti Transfer (JPG/PNG/PDF) <span class="text-danger">*</span>
                            </label>
                            {!! Form::file('bukti_bayar', [
                                'class' => 'form-control',
                                'accept' => 'image/*,.pdf',
                                'id' => 'bukti_bayar',
                                'required' => true
                            ]) !!}
                            <div class="form-text small text-muted">Maks. 2MB</div>
                            <div id="previewContainer" class="mt-2 d-none"></div>
                        </div>
                    </div>
                </section>

                {{-- Kirim semua tagihan_id --}}
                @foreach($tagihanSelected as $tagihan)
                    <input type="hidden" name="tagihan_id[]" value="{{ $tagihan->id }}">
                @endforeach

                <div class="d-grid gap-2 d-sm-flex justify-content-end mt-3">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-3 rounded-3">Batal</a>
                    <button type="submit" class="btn btn-dark px-4 rounded-3" id="btnSimpan">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="loadingSpinner"></span>
                        <span id="btnText">Simpan</span>
                    </button>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card[onclick] {
        user-select: none;
    }
    .card[onclick]:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
function selectBank(bankId) {
    document.getElementById('selected_bank_sekolah_id').value = bankId;
    document.querySelectorAll('input[name="bank_radio"]').forEach(radio => radio.checked = false);
    const selectedRadio = document.querySelector(`#bank_${bankId}`);
    if (selectedRadio) selectedRadio.checked = true;
}

document.addEventListener('DOMContentLoaded', () => {
    // Preview bukti bayar
    const buktiBayarEl = document.getElementById('bukti_bayar');
    const previewContainer = document.getElementById('previewContainer');

    function createCloseButton() {
        const btn = document.createElement('button');
        btn.className = 'btn-close position-absolute top-0 end-0';
        btn.style.transform = 'translate(50%, -50%)';
        btn.onclick = () => {
            buktiBayarEl.value = '';
            previewContainer.classList.add('d-none');
            previewContainer.innerHTML = '';
        };
        return btn;
    }

    buktiBayarEl?.addEventListener('change', function () {
        const file = this.files[0];
        previewContainer.innerHTML = '';
        if (!file) return previewContainer.classList.add('d-none');

        const wrapper = document.createElement('div');
        wrapper.className = 'position-relative d-inline-block me-2';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'border rounded-2';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                wrapper.appendChild(img);
                wrapper.appendChild(createCloseButton());
                previewContainer.appendChild(wrapper);
                previewContainer.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            const div = document.createElement('div');
            div.className = 'bg-light border rounded-2 d-flex align-items-center justify-content-center p-2';
            div.style.width = '100px';
            div.style.height = '100px';
            div.innerHTML = `<i class="bi bi-file-pdf fs-1 text-danger"></i>`;
            wrapper.appendChild(div);
            wrapper.appendChild(createCloseButton());
            previewContainer.appendChild(wrapper);
            previewContainer.classList.remove('d-none');
        }
    });

    // Loading state
    const form = document.getElementById('formPembayaran');
    const btnSimpan = document.getElementById('btnSimpan');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('loadingSpinner');
    form?.addEventListener('submit', () => {
        btnSimpan.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Menyimpan...';
    });

    // Toggle rekening baru
    const hasSavedBank = {{ ($listWaliBank && count($listWaliBank) > 0) ? 'true' : 'false' }};
    const formBankBaru = document.getElementById('formBankBaru');
    const infoRekeningBaru = document.getElementById('infoRekeningBaru');
    const checkboxToggle = document.getElementById('checkboxtoggle');

    if (!hasSavedBank) {
        formBankBaru.classList.remove('d-none');
        infoRekeningBaru.classList.remove('d-none');
    } else if (checkboxToggle) {
        checkboxToggle.addEventListener('change', () => {
            const isChecked = checkboxToggle.checked;
            formBankBaru.classList.toggle('d-none', !isChecked);
            infoRekeningBaru.classList.toggle('d-none', !isChecked);
        });
    }
});
</script>
@endpush
