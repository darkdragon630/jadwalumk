# Jadwal Kuliah UMK — PHP + MySQL

Aplikasi web untuk menyimpan dan menampilkan jadwal kuliah mahasiswa Universitas Muria Kudus (UMK) lintas device.

---

## File & Struktur

```
jadwal-php/
├── index.php          → Halaman utama (tampil jadwal)
├── upload.php         → Upload file HTML jadwal
├── database.sql       → Schema database (jalankan sekali)
├── config/
│   └── database.php   → Koneksi PDO ke MySQL
└── api/
    └── jadwal.php     → Endpoint simpan/cek HTML ke DB
```

---

## Setup Awal (Wasmer)

### 1. Import Database
- Buka **phpMyAdmin** di Wasmer
- Import file `database.sql` → tabel `settings` otomatis terbuat

### 2. Upload Semua File
- Upload seluruh isi folder `jadwal-php/` ke root hosting Wasmer

### 3. Cek Koneksi DB
- Buka `index.php` — kalau muncul halaman "Belum ada jadwal", koneksi DB berhasil

---

## Cara Pakai

### Upload Jadwal Baru
1. Siapkan file HTML jadwal (generate sendiri atau dari tool lain)
2. Buka `upload.php`
3. Seret atau pilih file `.html`
4. Klik **Simpan Jadwal** → otomatis redirect ke `index.php`

### Lihat Jadwal
- Buka `index.php` dari device manapun → jadwal tampil langsung dari database

---

## Konfigurasi Database

Edit file `config/database.php` sesuai kredensial Wasmer kamu:

```php
$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = '10272';
$db   = 'dbXYRA9WDi3SpqcVN5fPm2L2';
$user = 'username_kamu';
$pass = 'password_kamu';
```

---

## Catatan
- Jadwal tersimpan di tabel `settings` dengan key `jadwal_html`
- Upload jadwal baru akan **menggantikan** jadwal lama
- Tidak ada AI, tidak ada API key — upload HTML langsung
