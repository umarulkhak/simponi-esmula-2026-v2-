<!-- resources/views/wali/tagihan/modal_cara_bayar_atm.blade.php -->

<div class="modal fade" id="modalCaraBayarATM" tabindex="-1" aria-labelledby="modalCaraBayarATMLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalCaraBayarATMLabel">Cara Pembayaran via Transfer Bank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body fs-7">
        <p class="mb-3">Silakan transfer sesuai tagihan ke salah satu rekening sekolah berikut:</p>

        @forelse ($banksekolah as $itemBank)
          <div class="border rounded-2 p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <strong>{{ $itemBank->nama_bank }}</strong><br>
                <span class="text-muted fs-7">No. Rek: {{ $itemBank->nomor_rekening }}</span><br>
                <span class="text-muted fs-7">a.n. {{ $itemBank->nama_rekening }}</span>
              </div>
              <span class="badge bg-primary">Rek Resmi</span>
            </div>
          </div>
        @empty
          <div class="alert alert-warning">
            Belum ada rekening sekolah yang tersedia. Hubungi operator.
          </div>
        @endforelse

        <h6 class="fw-bold mt-4 mb-2">1. Pembayaran via ATM (Semua Bank)</h6>
        <ol>
          <li>Masukkan kartu ATM Anda dan pilih bahasa.</li>
          <li>Masukkan PIN ATM Anda.</li>
          <li>Pilih menu <strong>“Transfer”</strong>.</li>
          <li>Pilih <strong>“Ke Rekening Bank Lain”</strong> (jika bank berbeda).</li>
          <li>Masukkan <strong>kode bank</strong> tujuan (lihat di bawah) + <strong>nomor rekening</strong> sekolah.</li>
          <li>Masukkan <strong>jumlah nominal sesuai tagihan</strong> (jangan kurang/lebih).</li>
          <li>Periksa nama penerima: pastikan sesuai dengan rekening sekolah.</li>
          <li>Konfirmasi transaksi, lalu simpan bukti transfer.</li>
        </ol>

        <h6 class="fw-bold mt-3 mb-2">2. Pembayaran via Mobile Banking (m-Banking)</h6>
        <ol>
          <li>Buka aplikasi mobile banking (BCA, BNI, Mandiri, dll).</li>
          <li>Login dan pilih menu <strong>“Transfer” → “Antar Bank”</strong>.</li>
          <li>Cari bank tujuan atau masukkan <strong>kode bank</strong> secara manual.</li>
          <li>Masukkan nomor rekening sekolah dan nominal tagihan.</li>
          <li>Verifikasi nama penerima, lalu konfirmasi dengan PIN/OTP.</li>
          <li>Simpan screenshot sebagai bukti pembayaran.</li>
        </ol>

        <div class="alert alert-info mt-3">
          <strong>Kode Bank Umum:</strong><br>
          BCA: 014 | BNI: 009 | BRI: 002 | Mandiri: 008 | BTN: 200<br>
          (Jika transfer dari bank yang sama, tidak perlu kode bank)
        </div>

        <div class="alert alert-warning mt-3">
          <strong>Penting!</strong><br>
          - Transfer <strong>tepat sesuai nominal tagihan</strong>.<br>
          - Setelah bayar, unggah bukti di menu <strong>“Pembayaran”</strong>.<br>
          - Verifikasi dilakukan oleh petugas dalam 1x24 jam.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
