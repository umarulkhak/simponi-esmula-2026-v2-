{{--
    MODAL KONFIRMASI PEMBAYARAN — VERSI PRODUKSI + PREVIEW GAMBAR (MODIFIKASI)

    Deskripsi:
    Komponen modal Bootstrap untuk input konfirmasi pembayaran tagihan SPP oleh wali murid.
    Dilengkapi fitur preview gambar/PDF sebelum upload — dengan tampilan rapi dan tombol hapus.

    Fitur Teknis:
    - [Image Preview] Menampilkan preview gambar/PDF sebelum submit — ukuran thumbnail kecil.
    - [UX Optimization] Auto-focus ke field "Bank Pengirim" saat modal muncul.
    - [Loading State] Tombol "Simpan" menampilkan spinner dan disable saat submit — mencegah double-click.
    - [Input Masking] Field "Jumlah Dibayar" otomatis format rupiah saat diketik, lalu dikonversi ke angka murni saat submit.
    - [Error Handling] Menampilkan pesan validasi Laravel di bawah field jika gagal.
    - [Divider Grouping] Gunakan divider horizontal dengan judul + icon info.
    - [Iconography] Setiap label dilengkapi icon Bootstrap untuk meningkatkan affordance visual.
    - [Responsive] Menggunakan ukuran default Bootstrap — optimal untuk desktop & mobile.
    - [Preview UX] Preview gambar berukuran 120x120px dengan tombol hapus di pojok kanan atas.

    Dependencies:
    - Bootstrap 5 CSS/JS
    - Bootstrap Icons
    - Font Awesome 6 (fa-regular fa-circle-info)
    - jQuery 3.7+
    - Select2 4.1+
    - Laravel Collective Form

    @author   SIMPONI Team
    @version  1.9 — MODIFIKASI LOGIKA FIELD BANK BERDASARKAN DATA TERSIMPAN
    @updated  20 September 2025
--}}
<div class="modal fade" id="pembayaranModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow">
            {{-- HEADER MODAL --}}
            <div class="modal-header bg-light border-bottom-0 pb-3">
                <h5 class="modal-title fw-bold">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY MODAL --}}
            <div class="modal-body p-4">
                {!! Form::model($model, [
                    'route' => $route,
                    'method' => $method,
                    'files' => true,
                    'id' => 'formPembayaran'
                ]) !!}
                <input type="hidden" name="tagihan_id" id="modal_tagihan_id">
                <input type="hidden" name="bank_id" id="modal_bank_id">

                {{-- === SECTION: INFORMASI REKENING PENGIRIM === --}}
                <div class="mb-4">
                    <div class="divider divider-dark">
                        <div class="divider-text">
                            <i class="fa-solid fa-circle-info me-1"></i> Rekening Pengirim
                        </div>
                    </div>

                    {{-- ✅ FIELD BARU: PILIH DATA BANK TERSIMPAN (JIKA ADA) --}}
                    @if(isset($waliBanks) && $waliBanks->isNotEmpty())
                        <div class="form-group mb-3">
                            <label for="bank_tersimpan_id" class="form-label fw-medium">
                                <i class="bi bi-collection me-1"></i> Gunakan Data Bank Tersimpan
                            </label>
                            <select name="bank_tersimpan_id" id="bank_tersimpan_id" class="form-control select2" data-placeholder="Pilih data bank tersimpan...">
                                <option value=""></option>
                                @foreach($waliBanks as $bank)
                                    <option value="{{ $bank->id }}"
                                            data-nama-bank="{{ $bank->nama_bank_full }}"
                                            data-nama-pemilik="{{ $bank->nama_pemilik }}"
                                            data-no-rekening="{{ $bank->no_rekening }}">
                                        {{ $bank->nama_bank_full }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Pilih untuk mengisi otomatis form di bawah.
                            </div>
                        </div>

                        {{-- 🔹 BARU: Checkbox "Gunakan bank baru?" — awalnya HIDDEN --}}
                        <div class="form-check mb-3" id="chkGunakanBankBaru" style="display: none;">
                            <input class="form-check-input" type="checkbox" id="chkBaru" name="gunakan_bank_baru">
                            <label class="form-check-label" for="chkBaru">
                                Gunakan bank baru? (abaikan data tersimpan)
                            </label>
                        </div>
                    @endif

                    {{-- 🔹 WRAPPER: Field-field bank baru — awalnya bisa hidden/tampil tergantung kondisi --}}
                    <div id="formBankBaru">
                        {{-- FIELD ASLI: NAMA BANK PENGIRIM --}}
                        <div class="row mb-3">
                            @include('wali.partials.form_field', [
                                'label' => 'Nama Bank Pengirim',
                                'name' => 'bank_pengirim_id',
                                'type' => 'select',
                                'options' => $listbank ?? [],
                                'icon' => 'bi-bank2',
                                'placeholder' => 'Pilih bank pengirim...',
                                'required' => true,
                                'class' => 'select2 col-md-12'
                            ])
                        </div>

                        {{-- NAMA PEMILIK REKENING --}}
                        <div class="row mb-3">
                            @include('wali.partials.form_field', [
                                'label' => 'Nama Pemilik Rekening',
                                'name' => 'nama_pengirim',
                                'type' => 'text',
                                'icon' => 'bi-person',
                                'placeholder' => 'Nama sesuai buku tabungan',
                                'required' => true,
                                'class' => 'col-md-12'
                            ])
                        </div>

                        {{-- NOMOR REKENING --}}
                        <div class="row mb-3">
                            @include('wali.partials.form_field', [
                                'label' => 'Nomor Rekening',
                                'name' => 'no_rekening_pengirim',
                                'type' => 'text',
                                'icon' => 'bi-credit-card',
                                'placeholder' => 'Contoh: 1234567890',
                                'required' => true,
                                'class' => 'col-md-12'
                            ])
                        </div>
                    </div>

                    {{-- Checkbox Simpan Data --}}
                    <div class="form-check mt-3">
                        {!! Form::checkbox('simpan_data_rekening', 1, true, ['class' => 'form-check-input', 'id' => 'defaultCheck3']) !!}
                        <label class="form-check-label" for="defaultCheck3">
                           <small>Simpan data ini untuk pembayaran selanjutnya.</small>
                        </label>
                    </div>
                </div>

                {{-- === SECTION: BANK TUJUAN === --}}
                <div class="mb-4">
                    <div class="divider divider-dark">
                        <div class="divider-text">
                            <i class="fa-solid fa-circle-info me-1"></i> Rekening Tujuan
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            <i class="bi bi-building me-1"></i> Bank Tujuan
                        </label>
                        <div class="bg-light p-3 rounded border">
                            <div id="bankInfo" class="fw-medium">
                                <small class="text-muted">Akan otomatis terisi saat modal dibuka</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === SECTION: INFO PEMBAYARAN === --}}
                <div class="mb-4">
                    <div class="divider divider-dark">
                        <div class="divider-text">
                            <i class="fa-solid fa-circle-info me-1"></i> Info Pembayaran
                        </div>
                    </div>

                    {{-- TANGGAL BAYAR --}}
                    @include('wali.partials.form_field', [
                        'label' => 'Tanggal Bayar',
                        'name' => 'tanggal_bayar',
                        'type' => 'date',
                        'icon' => 'bi-calendar',
                        'value' => $model->tanggal_bayar ?? date('Y-m-d'),
                        'required' => true
                    ])

                    {{-- JUMLAH DIBAYAR --}}
                    @include('wali.partials.form_field', [
                        'label' => 'Jumlah Dibayar',
                        'name' => 'jumlah_dibayar',
                        'type' => 'text',
                        'icon' => 'bi-cash',
                        'placeholder' => 'Contoh: 500.000',
                        'required' => true,
                        'class' => 'rupiah'
                    ])

                    {{-- UPLOAD BUKTI --}}
                    <div class="mb-3">
                        <label for="bukti_bayar" class="form-label fw-medium">
                            <i class="bi bi-upload me-1"></i> Upload Bukti (JPG/PNG/PDF) <span class="text-danger">*</span>
                        </label>
                        {!! Form::file('bukti_bayar', [
                            'class' => 'form-control' . ($errors->has('bukti_bayar') ? ' is-invalid' : ''),
                            'accept' => 'image/*,.pdf',
                            'required',
                            'id' => 'bukti_bayar'
                        ]) !!}
                        @error('bukti_bayar')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted small">Maks. 2MB</div>

                        {{-- Preview Container --}}
                        <div id="previewContainer" class="mt-2 d-none"></div>
                    </div>
                </div>

                {{-- FOOTER MODAL --}}
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-2" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-dark px-4 rounded-2" id="btnSimpan">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" id="loadingSpinner"></span>
                        <span id="btnText">Simpan</span>
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Divider Style */
    .divider {
        position: relative;
        height: 1px;
        background-color: #adb5bd;
        margin: 1.5rem 0;
    }

    .divider-text {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #fff;
        padding: 0 1rem;
        font-size: 0.875rem;
        color: #495057;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 0.25rem;
        z-index: 1;
        box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .divider-dark {
        background-color: #adb5bd;
    }

    /* Container preview: kecil, rata tengah, tidak terlalu besar */
    #previewContainer {
        margin-top: 0.75rem;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px dashed #dee2e6;
        border-radius: 0.5rem;
        background: #f8f9fa;
        padding: 0.5rem;
        display: none;
        max-width: 100%;
        overflow: hidden;
        position: relative; /* Untuk posisi tombol close */
    }

    /* Gambar dipaksa kecil (thumbnail) */
    #previewContainer img {
        width: 120px; /* Ukuran fix */
        height: 120px;
        object-fit: cover; /* Crop supaya isi pas kotak */
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        display: block;
    }

    /* PDF preview tetap rapi */
    #previewContainer .embed {
        text-align: center;
        padding: 0.75rem;
        font-size: 0.875rem;
        border: 1px dashed #adb5bd;
        border-radius: 0.375rem;
        background: #f8f9fa;
        max-width: 100%;
        word-break: break-all;
    }

    /* Tombol hapus preview */
    #previewContainer .btn-close {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #fff;
        border: 1px solid #dee2e6;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 0.75rem;
        cursor: pointer;
        z-index: 10;
        padding: 0;
        line-height: 1;
    }

    /* Animasi smooth toggle */
    #formBankBaru {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Select2 untuk SEMUA select2 termasuk yang baru
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder') || 'Pilih...';
            },
            allowClear: true,
            dropdownParent: document.body,
        });

        // Tampilkan icon clear hanya saat ada pilihan
        $('.select2').on('select2:select', function() {
            const $selection = $(this).siblings('.select2-selection');
            $selection.find('.select2-selection__clear').show();
        });

        $('.select2').on('select2:unselect', function() {
            const $selection = $(this).siblings('.select2-selection');
            $selection.find('.select2-selection__clear').hide();
        });

        // Elemen DOM
        const modal              = document.getElementById('pembayaranModal');
        const bankInfo           = document.getElementById('bankInfo');
        const bankPengirimEl     = document.getElementById('bank_pengirim_id');
        const form               = document.getElementById('formPembayaran');
        const btnSimpan          = document.getElementById('btnSimpan');
        const btnText            = document.getElementById('btnText');
        const loadingSpinner     = document.getElementById('loadingSpinner');
        const buktiBayarEl       = document.getElementById('bukti_bayar');
        const previewContainer   = document.getElementById('previewContainer');

        // 🔹 BARU: Elemen untuk kontrol tampil/sembunyi field bank
        const bankTersimpanEl    = document.getElementById('bank_tersimpan_id');
        const namaPengirimEl     = document.querySelector('input[name="nama_pengirim"]');
        const noRekeningEl       = document.querySelector('input[name="no_rekening_pengirim"]');
        const chkGunakanBankBaru = document.getElementById('chkGunakanBankBaru');
        const chkBaru            = document.getElementById('chkBaru');
        const formBankBaru       = document.getElementById('formBankBaru');

        // 🔹 BARU: Fungsi untuk mengatur tampil/sembunyi field bank
        function updateBankFormVisibility() {
            const hasSavedBank = bankTersimpanEl && bankTersimpanEl.value;

            if (hasSavedBank) {
                // Ada data tersimpan → sembunyikan form bank baru
                formBankBaru.style.display = 'none';
                // Tampilkan checkbox "Gunakan bank baru?"
                if (chkGunakanBankBaru) chkGunakanBankBaru.style.display = 'block';
            } else {
                // Tidak ada data tersimpan → langsung tampilkan form bank baru
                formBankBaru.style.display = 'block';
                // Sembunyikan checkbox
                if (chkGunakanBankBaru) chkGunakanBankBaru.style.display = 'none';
            }
        }

        // 🔹 Event: Saat pilih data bank tersimpan, isi otomatis field lainnya
        if (bankTersimpanEl) {
            bankTersimpanEl.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (!selectedOption.value) {
                    updateBankFormVisibility();
                    return;
                }

                const namaBank = selectedOption.getAttribute('data-nama-bank');
                const namaPemilik = selectedOption.getAttribute('data-nama-pemilik');
                const noRekening = selectedOption.getAttribute('data-no-rekening');

                // Cari select2 bank_pengirim_id dan set valuenya
                const bankSelect2 = $('#bank_pengirim_id');
                if (bankSelect2.length) {
                    let bankId = null;
                    bankSelect2.find('option').each(function() {
                        if ($(this).text().trim() === namaBank) {
                            bankId = $(this).val();
                            return false;
                        }
                    });

                    if (bankId !== null) {
                        bankSelect2.val(bankId).trigger('change');
                    } else {
                        bankSelect2.val('').trigger('change');
                    }
                }

                // Isi field nama dan no rekening
                if (namaPengirimEl) namaPengirimEl.value = namaPemilik;
                if (noRekeningEl) noRekeningEl.value = noRekening;

                // Update tampilan setelah pilih bank tersimpan
                updateBankFormVisibility();
            });
        }

        // 🔹 Event: Saat checkbox "Gunakan bank baru?" diubah
        if (chkBaru) {
            chkBaru.addEventListener('change', function() {
                formBankBaru.style.display = this.checked ? 'block' : 'none';
                if (this.checked) {
                    // Reset field jika ingin mulai fresh
                    $('#bank_pengirim_id').val('').trigger('change');
                    if (namaPengirimEl) namaPengirimEl.value = '';
                    if (noRekeningEl) noRekeningEl.value = '';
                }
            });
        }

        // Event: Saat modal dibuka
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const tagihanId = button.getAttribute('data-tagihan-id');
            const bankId = button.getAttribute('data-bank-id');

            // Set hidden field
            document.getElementById('modal_tagihan_id').value = tagihanId;
            document.getElementById('modal_bank_id').value = bankId;

            // Ambil info bank tujuan dari card
            const bankElements = button.closest('.bg-white').querySelectorAll('p.mb-0.fs-6');
            const [bankNameElem, bankAccountElem, bankOwnerElem] = bankElements;

            if (bankNameElem && bankAccountElem) {
                const ownerHtml = bankOwnerElem ?
                    `<div class="small text-muted">a.n. ${bankOwnerElem.textContent}</div>` : '';
                bankInfo.innerHTML = `
                    <div class="fw-medium">${bankNameElem.textContent}</div>
                    <div class="small">${bankAccountElem.textContent}</div>
                    ${ownerHtml}
                `;
            } else {
                bankInfo.innerHTML = '<small class="text-muted">Informasi bank tidak tersedia</small>';
            }

            // Auto-focus ke field bank pengirim (atau ke bank tersimpan jika ada)
            setTimeout(() => {
                updateBankFormVisibility(); // 🔹 PENTING: panggil di sini!

                if (bankTersimpanEl && bankTersimpanEl.options.length > 1) {
                    bankTersimpanEl.focus();
                } else {
                    bankPengirimEl?.focus();
                }
            }, 300);
        });

        // 🔹 PREVIEW GAMBAR/FILE — DENGAN TOMBOL HAPUS
        buktiBayarEl.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                previewContainer.classList.add('d-none');
                previewContainer.innerHTML = '';
                return;
            }

            const fileType = file.type;

            // Clear existing content
            previewContainer.innerHTML = '';

            if (fileType.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Preview Bukti Bayar';
                    img.style.width = '120px';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #dee2e6';
                    img.style.borderRadius = '0.375rem';

                    // Tombol close
                    const closeBtn = document.createElement('button');
                    closeBtn.className = 'btn-close';
                    closeBtn.setAttribute('type', 'button');
                    closeBtn.setAttribute('aria-label', 'Hapus preview');
                    closeBtn.onclick = function() {
                        buktiBayarEl.value = '';
                        previewContainer.classList.add('d-none');
                        previewContainer.innerHTML = '';
                    };

                    previewContainer.appendChild(img);
                    previewContainer.appendChild(closeBtn);
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else if (fileType === 'application/pdf') {
                const pdfDiv = document.createElement('div');
                pdfDiv.className = 'embed';
                pdfDiv.innerHTML = `
                    <i class="bi bi-file-pdf me-1"></i> File PDF terpilih: ${file.name}
                `;

                // Tombol close
                const closeBtn = document.createElement('button');
                closeBtn.className = 'btn-close';
                closeBtn.setAttribute('type', 'button');
                closeBtn.setAttribute('aria-label', 'Hapus preview');
                closeBtn.onclick = function() {
                    buktiBayarEl.value = '';
                    previewContainer.classList.add('d-none');
                    previewContainer.innerHTML = '';
                };

                previewContainer.appendChild(pdfDiv);
                previewContainer.appendChild(closeBtn);
                previewContainer.classList.remove('d-none');
            } else {
                previewContainer.classList.add('d-none');
            }
        });

        // Event: Saat form disubmit
        form.addEventListener('submit', function() {
            btnSimpan.disabled = true;
            loadingSpinner.classList.remove('d-none');
            btnText.textContent = 'Menyimpan...';
        });

        // Format Rupiah
        document.querySelectorAll('.rupiah').forEach(input => {
            input.addEventListener('keyup', function(e) {
                if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight'].includes(e.key)) return;
                let value = this.value.replace(/\D/g, '');
                if (value) this.value = new Intl.NumberFormat('id-ID').format(value);
            });

            input.addEventListener('blur', function() {
                let value = this.value.replace(/\D/g, '');
                if (value) this.value = new Intl.NumberFormat('id-ID').format(value);
            });

            input.form.addEventListener('submit', function() {
                let rawValue = input.value.replace(/\D/g, '');
                input.value = rawValue;
            });
        });

        // 🔹 Panggil saat halaman pertama kali dimuat
        updateBankFormVisibility();
    });
</script>
@endpush
