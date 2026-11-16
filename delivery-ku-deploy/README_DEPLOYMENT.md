# Panduan Deployment Delivery Ku ke InfinityFree (`dash.infinityfree.com`)

Panduan ini berisi langkah demi langkah untuk memublikasikan aplikasi **Delivery Ku** (PHP 8.4 + MySQL + Bootstrap 5) ke akun hosting **InfinityFree**.

---

## Isi Paket Deployment (`/delivery-ku-deploy/`)
1. **`delivery-ku-infinityfree.zip`** — Seluruh file aplikasi siap ekstrak/unggah ke folder `htdocs/` (di dalamnya langsung terdapat `index.php`, `.htaccess`, `app/`, `includes/`, `public/`, `resources/`, `routes/`, `storage/`).
2. **`htdocs/`** — Folder hasil ekstraksi siap unggah via FTP (FileZilla).
3. **`database.sql`** — File SQL gabungan (38 tabel skema + data awal Kabupaten Indramayu + akun demo terpisah) siap impor ke phpMyAdmin.
4. **`README_DEPLOYMENT.md`** — Panduan instalasi & checklist pengujian ini.

---

## Langkah 1: Buat Akun Hosting & Database MySQL di InfinityFree
1. Login ke **https://dash.infinityfree.com/** dan buat atau buka akun hosting Anda.
2. Masuk ke **Control Panel (VistaPanel)** -> menu **MySQL Databases**.
3. Buat database baru (misalnya `deliveryku`).
4. Catat keempat parameter koneksi yang ditampilkan oleh InfinityFree:
   - **MySQL Host Name**: contoh `sql305.infinityfree.com` *(BUKAN `localhost`)*
   - **MySQL DB Name**: contoh `if0_38901234_deliveryku`
   - **MySQL User Name**: contoh `if0_38901234`
   - **MySQL Password**: password akun hosting / vPanel Anda

---

## Langkah 2: Impor `database.sql` ke phpMyAdmin
1. Masih di **Control Panel -> MySQL Databases**, klik tombol **phpMyAdmin** di sebelah database yang baru dibuat.
2. Pastikan Anda sudah berada di dalam database `if0_XXXXXXX_deliveryku`.
3. Buka tab **Import**.
4. Pilih file **`database.sql`** dari komputer Anda.
5. Biarkan format **SQL** dan klik **Go / Kirim**.
6. Pastikan muncul notifikasi sukses dan seluruh **38 tabel** (`users`, `roles`, `orders`, `merchants`, `drivers`, `districts`, `villages`, dll.) tampil pada panel kiri.

---

## Langkah 3: Konfigurasi Koneksi Database (Mudah Diedit)
Sebelum atau sesudah mengunggah ke `htdocs/`, edit file berikut:

### Opsi A (Paling Mudah di File Manager InfinityFree): `app/config/db_credentials.php`
Buka `htdocs/app/config/db_credentials.php` dan sesuaikan nilainya:
```php
return [
    'app_name'    => 'Delivery Ku',
    'app_env'     => 'production',
    'app_debug'   => false, // WAJIB false di produksi
    'app_url'     => 'https://domainanda.rf.gd',
    'timezone'    => 'Asia/Jakarta',
    'force_https' => false, // Ubah ke true setelah SSL aktif

    'db_host'     => 'sql305.infinityfree.com',
    'db_port'     => '3306',
    'db_name'     => 'if0_38901234_deliveryku',
    'db_user'     => 'if0_38901234',
    'db_pass'     => 'PASSWORD_MYSQL_ANDA',
];
```

### Opsi B (Menggunakan `.env`):
Salin `.env.example` menjadi `.env` di dalam `htdocs/.env` dan isi `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`. Jika `.env` ada, nilainya akan menimpa `db_credentials.php`.

> **Catatan Keamanan:** Folder `app/`, `includes/`, `resources/`, `routes/`, `storage/`, serta file `.env*` sudah diblokir total oleh `.htaccess` (mengembalikan HTTP `403 Forbidden`) sehingga tidak dapat diakses lewat browser publik.

---

## Langkah 4: Upload File Aplikasi ke Folder `htdocs`
- **Cara 1 (FTP - Direkomendasikan oleh InfinityFree karena batas ukuran upload File Manager):**
  1. Gunakan **FileZilla** dan login memakai detail FTP dari Client Area InfinityFree (`ftpupload.net`, Username `if0_XXXXXXX`, Password akun hosting, Port `21`).
  2. Masuk ke folder **`/htdocs/`** di sisi server.
  3. Unggah seluruh isi folder `htdocs/` (atau hasil ekstrak `delivery-ku-infinityfree.zip`) ke dalam `/htdocs/`.
  4. Pastikan `htdocs/index.php` dan `htdocs/.htaccess` berada langsung di dalam `/htdocs/`.
- **Cara 2 (File Manager Browser):**
  1. Buka **File Manager** -> masuk ke `htdocs/`.
  2. Upload `delivery-ku-infinityfree.zip` lalu klik **Extract** (jika ukuran ZIP diterima oleh server web File Manager).

### Izin Direktori (`CHMOD`)
Pastikan folder berikut memiliki izin **`755`** (standar InfinityFree agar PHP dapat menulis file upload & log):
- `htdocs/public/uploads/` beserta subfolder (`products`, `merchants`, `proofs`, `documents`, `avatars`)
- `htdocs/storage/logs/`

---

## Langkah 5: Aktifkan SSL / HTTPS di Domain InfinityFree
1. Buka **Client Area InfinityFree -> Free SSL Certificates**.
2. Pilih domain/subdomain Anda (`domainanda.rf.gd` atau custom domain), pilih penyedia **Let's Encrypt** atau **ZeroSSL** (atau hubungkan ke **Cloudflare**).
3. Tambahkan CNAME record sesuai instruksi di VistaPanel -> **CNAME Records**, tunggu status berubah menjadi **Ready**.
4. Klik **Install SSL Certificate** pada VistaPanel -> **SSL/TLS**.
5. Setelah `https://domainanda.rf.gd` dapat dibuka tanpa peringatan sertifikat:
   - Ubah `'force_https' => true` di `htdocs/app/config/db_credentials.php`, **atau**
   - Hapus tanda `#` pada blok redirect HTTPS di `htdocs/.htaccess`.

---

## Langkah 6: Akun Pengujian Awal & Pengamanan Produksi
Seluruh akun awal menggunakan kata sandi default: **`DeliveryKu2026!`**
- **Superadmin**: `superadmin@deliveryku.id` *(segera ganti password melalui menu Profil Superadmin setelah login pertama!)*
- **Admin Demo**: `admin@deliveryku.id`
- **Merchant Demo**: `merchant@deliveryku.id` & `merchant2@deliveryku.id`
- **Driver Demo**: `driver@deliveryku.id`
- **Pelanggan Demo**: `pelanggan@deliveryku.id`

---

## Checklist Pengujian Wajib Setelah Upload di Domain Live
Lakukan pengujian berikut secara langsung pada domain hosting Anda sebelum dibuka ke publik:
- [ ] **Halaman Utama**: `https://domainanda.rf.gd/` terbuka tanpa error 404/500, menampilkan wilayah Indramayu, kategori, merchant, dan menu promo.
- [ ] **Proteksi Direktori**: Mengakses `https://domainanda.rf.gd/app/config/db_credentials.php` dan `https://domainanda.rf.gd/.env.example` menghasilkan **403 Forbidden**.
- [ ] **Debugging Nonaktif**: Error PHP tidak tampil di layar browser (`app_debug => false`), melainkan masuk ke `storage/logs/app.log`.
- [ ] **Login & RBAC 5 Role**: Login Superadmin, Admin, Merchant, Driver, dan Pelanggan mengarah ke dashboard masing-masing; mencoba membuka route role lain ditolak.
- [ ] **Pendaftaran Baru**: Daftar akun baru Pelanggan, Merchant, dan Driver dari halaman `/index.php?route=auth/register` dan pastikan tersimpan di MySQL.
- [ ] **Alur Transaksi Penuh**:
  1. Pelanggan memilih menu -> tambah ke keranjang -> gunakan voucher `INDRAMAYUHEMAT` -> checkout (uji COD dan Upload Bukti Transfer).
  2. Merchant konfirmasi -> siapkan -> tandai siap diambil driver.
  3. Driver menerima tugas -> menuju merchant -> ambil pesanan -> dalam pengiriman -> selesai (PIN verifikasi).
  4. Cek perubahan saldo/komisi di Dashboard Superadmin -> **Laporan Keuangan & Transaksi** (pastikan filter *Produksi Nyata* memisahkan data `is_demo = 1`).
- [ ] **Ekspor & Backup**: Uji unduh laporan Excel (CSV), Cetak PDF, dan **Backup Database (.sql)** dari dashboard Superadmin.
