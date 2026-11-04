# SIMPONI - SMP Muhammadiyah Larangan

Sistem Informasi Pembayaran dan Operasional Sekolah (ESMULA).

## Teknologi dan arsitektur

- Framework: Laravel 9 (MVC)
- Frontend: Blade, Bootstrap 5, and the Sneat admin template
- Database: MySQL/MariaDB
- Authentication: session-based login with `admin`, `operator`, and `wali` roles

## Role pengguna

| Role | Akses |
|---|---|
| `admin` | Akses administrasi/operator |
| `operator` | Manajemen user, wali, siswa, biaya, tagihan, dan pembayaran |
| `wali` | Melihat data dan tagihan siswa yang terhubung ke akunnya |

## Data dan alur bisnis

### Wali dan siswa

Satu wali (record `users` dengan akses `wali`) dapat memiliki beberapa siswa. Setiap siswa menyimpan `wali_id`, sehingga siswa terhubung ke maksimal satu wali.

### Biaya dan tagihan

- `biayas` menyimpan nama dan nominal biaya yang dapat dipilih operator.
- `tagihans` menyimpan tagihan per siswa, periode, tanggal jatuh tempo, keterangan, dan status (`baru`, `angsur`, `lunas`).
- Rincian nominal disimpan pada `tagihan_details` sebagai salinan nama dan jumlah biaya saat tagihan dibuat.
- Operator dapat membuat tagihan untuk siswa berdasarkan filter angkatan/kelas dan biaya.

### Pembayaran

Satu tagihan dapat memiliki beberapa catatan pembayaran. Wali mengirim bukti transfer dan operator mengonfirmasi pembayaran. Alur yang tersedia adalah transfer manual; integrasi payment gateway otomatis belum tersedia.

## Menjalankan secara lokal

1. Install dependensi PHP: `composer install`.
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi database lokal.
3. Isi `ADMIN_DEFAULT_NAME`, `ADMIN_DEFAULT_EMAIL`, dan `ADMIN_DEFAULT_PASSWORD` dengan identitas dan sandi admin lokal yang unik.
4. Isi `DEMO_WALI_PASSWORD` dengan sandi lokal khusus untuk akun wali demo.
5. Buat database MySQL/MariaDB sesuai `DB_DATABASE`.
6. Jalankan `php artisan key:generate`.
7. Jalankan migrasi dan data demo lokal: `php artisan migrate --seed`.
8. Jalankan aplikasi: `php artisan serve`.

Seeder lokal menyiapkan katalog bank, satu rekening sekolah fiktif, rekening pengirim fiktif, serta akun wali, siswa, dan tagihan yang saling terhubung. Nilai sandi dan identitas akun dibaca dari environment, tidak disimpan dalam source. Jangan gunakan data demo atau kredensial lokal untuk produksi.

## Batasan dan kesiapan produksi

- Alur inti untuk data siswa, tagihan, dan verifikasi transfer tersedia.
- Papan informasi wali masih ditandai sebagai fitur dalam pengembangan.
- Sebelum produksi, siapkan kredensial admin unik, HTTPS, konfigurasi email/notifikasi, backup database, kebijakan retensi bukti transfer, dan pengujian operasional.
- Jalankan tes dengan `vendor/bin/phpunit`. Cakupan tes otomatis saat ini masih dasar; alur bisnis utama perlu terus ditambahkan ke suite.
