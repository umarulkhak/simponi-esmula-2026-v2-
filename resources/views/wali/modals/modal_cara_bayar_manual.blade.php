<!-- resources/views/wali/modals/modal_cara_bayar_manual.blade.php -->

<div class="modal fade" id="modalCaraBayarManual" tabindex="-1" aria-labelledby="modalCaraBayarManualLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-md">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalCaraBayarManualLabel">Bayar Langsung di Sekolah</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body fs-7">
        <p class="mb-3">
          Anda bisa membayar tagihan secara langsung di sekolah dengan cara berikut:
        </p>

        <ol>
          <li>
            Datang ke kantor Tata Usaha (TU) SMP Muhammadiyah Larangan.
          </li>
          <li>
            Sampaikan kepada petugas bahwa Anda ingin membayar tagihan untuk:
            <div class="mt-2 ms-3 fw-medium">
              <strong>{{ $siswa->nama ?? 'Nama Siswa' }}</strong><br>
              <small>Kelas: {{ $siswa->kelas ?? '–' }}</small>
            </div>
          </li>
          <li>
            Lakukan pembayaran sesuai nominal tagihan.
          </li>
          <li>
            Petugas akan mencatat dan memproses pembayaran Anda hingga selesai.
          </li>
        </ol>

        <div class="alert alert-info mt-3 mb-0">
          <i class="bx bx-info-circle me-1"></i>
          Jam layanan: Senin–Jumat (07.30–15.00 WIB), Sabtu (07.30–12.00 WIB)
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
