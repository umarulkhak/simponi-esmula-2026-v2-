# Arsitektur Sistem SIMPONI

> **Sistem Informasi Pembayaran dan Operasional Sekolah**  
> Untuk SMP Muhammadiyah Larangan (ESMULA)

---

## 🧱 1. Pola Arsitektur

- **Framework**: Laravel 10 (MVC)
- **Frontend**: Blade + Bootstrap 5 (Sneat Admin Template)
- **Database**: MySQL / MariaDB
- **Auth**: Role-Based Access Control (RBAC)

---

## 👥 2. Role Pengguna

| Role       | Akses                                                                 |
|------------|-----------------------------------------------------------------------|
| `admin`    | Dashboard admin (minimal, bisa dikembangkan)                          |
| `operator` | Manajemen penuh: user, wali, siswa, biaya, tagihan, pembayaran       |
| `wali`     | Hanya lihat & bayar tagihan anak yang terhubung                       |

---

## 🗃️ 3. Entitas Inti & Relasi

### Biaya (`biayas`)
- **Fungsi**: Master data template nominal (misal: "SPP Oktober 2025")
- **Kolom**: `nama`, `jumlah`, `user_id`
- **Catatan**: **Tidak terhubung ke tagihan**. Hanya sebagai referensi saat input manual.

### Tagihan (`tagihans`)
- **Fungsi**: Tagihan transaksional per siswa
- **Kolom**: `siswa_id`, `jumlah`, `keterangan`, `lunas`
- **Alur**: Operator buat tagihan → isi jumlah manual → wali bayar

### Pembayaran (`pembayarans`)
- **Relasi**: Many-to-one ke `tagihans`
- **Fitur**: Bayar sebagian, update status lunas

### Wali & Siswa
- **Relasi**: Many-to-many via `wali_siswas`
- **Validasi**: Operator hanya bisa hubungkan siswa ke wali yang dikelolanya

---

## 🔒 4. Keamanan

- Semua akses dilindungi middleware:
  - `auth.operator`
  - `auth.wali`
- Form Request Validation untuk semua input
- CSRF protection aktif
- Implicit model binding (otomatis validasi ID)

---

## 🎨 5. UX/UI

- Template: **Sneat Bootstrap 5**
- Konsistensi:
  - Pencarian → Aksi Massal → Tabel → Pagination
  - Icon-only button (Boxicons)
  - Flash message informatif
- Responsif di mobile

---

## 🚀 6. Deployment

- Environment: `local` (dev), `production` (live)
- Tidak ada dependensi eksternal selain Laravel + Sneat
- Siap deploy ke shared hosting / VPS

---
