# Laporan QA SIMPONI

> **Cakupan:** pemeriksaan alur admin/operator dan wali, keterhubungan data, pembayaran, akses, tampilan lokal, dan regresi atas perbaikan.
> **Lingkungan:** aplikasi Laravel lokal, MySQL lokal, Node.js 20.19.5.
> **Kesimpulan:** alur utama yang diuji berjalan; perlu tindak lanjut sebelum dinyatakan siap produksi.
> **Catatan keamanan:** laporan ini sengaja tidak memuat kata sandi, nomor rekening, atau kredensial.

## Ringkasan eksekutif

| Area | Hasil |
|---|---|
| Tes regresi Laravel | **LULUS** — 8 tes, 94 assertions, exit code 0 |
| Build aset frontend | **LULUS** — `npm run build` dengan Vite 3.2.11 |
| Migrasi database | **LULUS** — seluruh migrasi tercatat sudah diterapkan |
| Data demo tertaut | **LULUS** — 3 wali, 3 siswa, 3 tagihan, 2 pembayaran |
| Referensi rekening pembayaran | **LULUS** — 0 referensi wali/sekolah yang yatim |
| Pemeriksaan browser wali | **LULUS** — beranda, siswa, tagihan, profil memberi HTTP 200 |
| Pemeriksaan browser admin/operator | **LULUS TERBATAS** — login dan halaman sampel sebelumnya berhasil; operasi destruktif tidak dijalankan |
| Audit dependency produksi | **LULUS** — `npm audit --omit=dev` menemukan 0 kerentanan |
| Audit seluruh dependency npm | **PERLU TINDAK LANJUT** — 15 temuan pada dependency pengembangan (4 moderate, 10 high, 1 critical) |

**Penilaian:** layak untuk uji coba lokal/staging dengan data demo. Belum direkomendasikan untuk produksi sebelum temuan dependency pengembangan dan pengujian yang belum dilakukan ditangani.

## 1. Login dan akses

### Admin/operator

- Login admin berhasil pada pemeriksaan browser sebelumnya.
- Dashboard admin serta halaman operator sampel merespons HTTP 200.
- Tes regresi memeriksa batas akses berbasis peran serta alur biaya/tagihan terkait.
- Invoice operator/admin kini dapat dicetak melalui rute yang sesuai.
- **Batas:** seluruh menu tidak diuji dengan operasi tulis; penghapusan massal dan promosi/rollback siswa tidak dieksekusi pada data demo.

### Wali murid

- Login wali berhasil pada pemeriksaan browser sebelumnya.
- Pada pemeriksaan browser terbaru, rute `/wali/beranda`, `/wali/siswa`, `/wali/tagihan`, dan `/wali/profile` masing-masing merespons HTTP 200.
- Form pembayaran menampilkan rekening bank demo, tujuan transfer demo, jumlah tagihan, tanggal bayar, dan kontrol unggah bukti.
- Akses tagihan milik wali lain diharapkan ditolak; pemeriksaan kepemilikan dan manipulasi ID profil memiliki tes regresi.
- **Batas:** pembayaran yang terlihat adalah klaim transfer manual. Tidak ada transfer uang atau gateway pembayaran yang diuji.

## 2. Cakupan fitur

| Fitur | Status | Bukti / catatan |
|---|---|---|
| Autentikasi dan pembatasan peran | **Lulus** | Login admin/wali berhasil pada pemeriksaan browser sebelumnya; tes akses lintas peran tersedia. |
| Dashboard admin/operator | **Lulus terbatas** | Dashboard dan halaman sampel berhasil dimuat; belum semua variasi data/aksi diperiksa. |
| Pengguna dan wali | **Lulus terbatas** | Alur peran diuji. Pendaftaran baru kini diberi peran wali minimum; wali baru masih perlu ditautkan ke siswa oleh admin. |
| Data siswa dan relasi wali | **Lulus** | Data demo saling terhubung; kolom kelas diperlebar agar nilai kelas tidak terpotong. |
| Biaya dan tagihan | **Lulus** | Alur biaya/tagihan serta konsistensi jumlah/tagihan dicakup tes regresi. |
| Pembayaran wali | **Lulus untuk alur uji** | Validasi klaim pembayaran, penanganan duplikat, dan pembersihan bukti unggahan saat transaksi gagal memiliki cakupan tes. |
| Konfirmasi pembayaran operator | **Lulus terbatas** | Logika status konfirmasi diuji; proses transfer eksternal tidak termasuk. |
| Rekening bank sekolah dan rekening wali | **Lulus untuk data tertaut** | Data bank tersedia; foreign key pembayaran kini menunjuk tabel rekening yang benar. |
| Invoice dan ekspor | **Lulus terbatas** | Perbaikan URL invoice/ekspor dan akses cetak invoice diuji. Format setiap ekspor belum diperiksa manual satu per satu. |
| Profil wali | **Lulus** | Wali tidak boleh mengubah profil pengguna lain dengan mengganti ID pada URL. |
| Hapus massal / promosi / rollback siswa | **Belum diuji secara operasional** | Sengaja tidak dijalankan agar data demo tidak berubah/hilang. |
| Informasi/papan pengumuman wali | **Belum tersedia** | Ditandai “Coming Soon” pada aplikasi. |
| CRUD rekening wali mandiri | **Belum tersedia** | Tidak ditemukan alur CRUD rekening wali tersendiri. |
| Reset kata sandi dan email | **Belum diverifikasi** | Pengiriman email memerlukan konfigurasi dan layanan email yang tidak diuji. |

## 3. Data demo dan integritas relasi

Jumlah yang diverifikasi di database lokal setelah tes:

| Data | Jumlah |
|---|---:|
| Akun wali | 3 |
| Siswa | 3 |
| Tagihan | 3 |
| Pembayaran | 2 |
| Rekening wali demo | 3 |
| Rekening sekolah demo | 1 |
| Referensi bank | 151 |
| Referensi rekening wali yang yatim | 0 |
| Referensi rekening sekolah yang yatim | 0 |

Data rekening dan transaksi demo bersifat fiktif. Status dua pembayaran yang tersimpan adalah kondisi database saat pemeriksaan; data tersebut tidak membuktikan transaksi bank nyata.

## 4. Perbaikan yang dilakukan

1. Memperbaiki URL nested untuk invoice/ekspor operator, menghapus rute duplikat hapus massal pembayaran, dan menghubungkan dashboard admin ke controller dashboard yang tersedia.
2. Memperbaiki foreign key pembayaran agar rekening wali mengacu ke `wali_banks` dan rekening sekolah ke `bank_sekolahs`.
3. Memperlebar kolom kelas siswa agar nilai seperti `VIII` tidak terpotong menjadi `VII`.
4. Menjadikan seeder demo wali/siswa/tagihan dapat dijalankan ulang tanpa merusak status pembayaran yang sudah ada.
5. Mengamankan profil wali dari perubahan data pengguna lain dan memastikan akses tagihan terlarang tetap menghasilkan penolakan, bukan tertelan sebagai redirect generik.
6. Membersihkan file bukti pembayaran jika transaksi gagal/duplikat, dan memberi peran wali berhak-minimum pada akun baru.
7. Menghapus kata sandi default tertanam dari seeder; konfigurasi kata sandi admin default kini melalui environment dan dokumentasi.
8. Menambah tes regresi untuk alur admin/operator, pembayaran wali, relasi data, batas akses, unggahan, invoice, dan ekspor.

## 5. Bukti validasi

| Pemeriksaan | Hasil |
|---|---|
| `php -d auto_prepend_file= vendor/bin/phpunit` | Exit code 0 — 8 tes, 94 assertions |
| `npm ci` | Berhasil memulihkan versi sesuai lockfile; tidak mengubah manifest |
| `npm run build` | Exit code 0 — Vite 3.2.11, aset berhasil dibangun |
| `npm ls vite --depth=0` | Vite 3.2.11 sesuai manifest/lockfile |
| `php -d auto_prepend_file= artisan migrate:status` | Seluruh migrasi berstatus `Ran` |
| `curl http://localhost:8000/login` | HTTP 200 |
| Browser wali | HTTP 200 pada `/wali/beranda`, `/wali/siswa`, `/wali/tagihan`, `/wali/profile` |
| `npm audit --omit=dev --audit-level=moderate` | Exit code 0 — 0 temuan produksi |
| `git diff --check` | Lulus |

### Verdict runtime

```text
environment:
  docker: UNAVAILABLE — perintah docker tidak ditemukan
  node: AVAILABLE — v20.19.5
  playwright: AVAILABLE — browser terintegrasi berhasil menjalankan navigasi Playwright
  infra-tier: FALLBACK(local MySQL) — Docker tidak tersedia; tes menggunakan database lokal yang sudah berjalan
  browser-tier: PRIMARY(Playwright) — alur halaman wali dijalankan melalui browser terintegrasi
startup: PASS — server sudah berjalan; GET /login mengembalikan HTTP 200 (server tidak dimulai ulang)
integration: PASS — exit_code: 0; passed: 8 tes; failed: 0; skipped: 0; 94 assertions
e2e: PARTIAL — exit_code: 0; alur login admin/wali dan halaman sampel berhasil; pemeriksaan browser terbaru mencakup 4 rute wali; bukan suite Playwright tersimpan/otomatis
overall: NEEDS_SIGNOFF — alur utama lokal lulus, namun operasi destruktif, email/reset sandi, audit penuh dependency, dan pemeriksaan produksi masih perlu tindak lanjut
```

### Batas lingkungan

- Docker tidak tersedia; tes memakai MySQL lokal, bukan database container terisolasi.
- Perintah PHP mengeluarkan peringatan ekstensi Herd yang tidak ditemukan. PHPUnit, Artisan, dan build tetap berhasil.
- Aplikasi lokal sudah hidup saat validasi; kesiapan deployment bersih dari nol belum dibuktikan.
- Build Sass mengeluarkan peringatan deprecasi `@import`; build tetap berhasil.

## 6. Risiko dan tindak lanjut

### Prioritas tinggi sebelum produksi

- `npm audit` penuh melaporkan **15 temuan di dependency pengembangan**: 4 moderate, 10 high, dan 1 critical. Audit khusus dependency produksi melaporkan nol. Rencanakan pembaruan dependency frontend secara terpisah karena perbaikan yang tersedia dapat memerlukan perubahan versi mayor; jalankan kembali build dan browser tests setelah pembaruan.
- Pastikan `APP_DEBUG=false` dan toolbar/debug tooling tidak aktif pada lingkungan produksi.
- Jalankan pengujian di lingkungan staging terisolasi sebelum migrasi/seed atau operasi massal pada data nyata.

### Pengujian lanjutan

- Uji operasi hapus massal serta promosi dan rollback siswa dengan database staging yang dapat dipulihkan.
- Verifikasi email/reset kata sandi menggunakan layanan email uji.
- Uji unggah file dengan batas ukuran, tipe file, dan kegagalan penyimpanan di staging.
- Tambahkan suite browser otomatis yang tersimpan untuk login admin dan wali serta alur pembayaran sampai konfirmasi operator.
- Periksa tampilan responsif dan format ekspor pada beberapa browser/perangkat.
- Tentukan apakah papan informasi wali dan pengelolaan rekening wali termasuk kebutuhan rilis; saat ini belum tersedia sebagai fitur lengkap.
