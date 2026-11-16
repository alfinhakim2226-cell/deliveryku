# Delivery Ku — Marketplace Pesan Antar Multi-Role (Kabupaten Indramayu)

Delivery Ku adalah platform web marketplace pesan antar makanan, minuman, kebutuhan harian, dan produk UMKM lokal untuk wilayah awal **Kabupaten Indramayu, Jawa Barat** (Kecamatan Jatibarang, Sukagumiwang, Kertasemaya, Widasari, Tukdana, Bangodua, Krangkeng, serta siap diperluas ke seluruh kecamatan lainnya).

Dibangun dengan **PHP 8.4 modular native**, **MySQL / MariaDB (PDO Prepared Statements)**, **HTML5 / CSS3 / Bootstrap 5**, **Vanilla JS + AJAX Polling**, dan **Chart.js** agar 100% kompatibel dengan shared hosting **InfinityFree**.

---

## 1. Struktur Folder Proyek
```
/delivery-ku
  /app
    /config       (app.php, database.php, security.php, constants.php)
    /controllers  (Auth, Superadmin, Admin, Merchant, Driver, User, Api)
    /services     (Auth, Order, Pricing, Upload, Export, Backup)
    /middleware   (Auth, Role, Csrf, RateLimit)
    /helpers      (functions.php, flash.php, validator.php)
  /database
    schema.sql    (36+ tabel relasional InnoDB utf8mb4 + soft delete + index)
    seed.sql      (Data wilayah Indramayu, role, permissions, akun uji terpisah)
  /public/assets  (css, js, images, uploads)
  /resources/views (auth, superadmin, admin, merchant, driver, user, layouts)
  /routes         (web.php)
  /storage/logs
  /includes       (bootstrap.php)
  index.php
  .htaccess
  .env.example
```

---

## 2. Panduan Instalasi Lokal & Upload ke InfinityFree (`htdocs`)

### Langkah 1: Siapkan Database MySQL di InfinityFree
1. Login ke **InfinityFree Control Panel (VistaPanel)**.
2. Buka menu **MySQL Databases** -> buat database baru, misalnya `if0_38901234_deliveryku`.
3. Catat **MySQL Hostname** (contoh: `sql305.infinityfree.com`), **Database Name**, **Username**, dan **Password**.
4. Buka **phpMyAdmin** dari panel InfinityFree pada database tersebut.
5. Masuk ke tab **Import**:
   - Impor `database/schema.sql` terlebih dahulu.
   - Setelah sukses, impor `database/seed.sql`.

### Langkah 2: Upload Source Code ke `htdocs`
1. Buka **File Manager** InfinityFree (atau gunakan **FileZilla FTP** ke `ftpupload.net`).
2. Masuk ke folder `htdocs/` (document root domain Anda).
3. Unggah **seluruh isi folder `/delivery-ku/`** langsung ke dalam `htdocs/` sehingga `htdocs/index.php` dan `htdocs/.htaccess` berada di root `htdocs/`.
4. Salin `.env.example` menjadi `.env` di dalam `htdocs/.env` dan sesuaikan kredensial:
   ```ini
   APP_NAME="Delivery Ku"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL="https://domainanda.rf.gd"
   DB_HOST="sql305.infinityfree.com"
   DB_PORT="3306"
   DB_NAME="if0_38901234_deliveryku"
   DB_USER="if0_38901234"
   DB_PASS="PASSWORD_MYSQL_ANDA"
   ```
5. Pastikan folder `public/uploads/` dan `storage/logs/` memiliki izin tulis (`755`).

---

## 3. Panduan Konfigurasi Domain & HTTPS di InfinityFree
1. Pada Client Area InfinityFree, buka **SSL Certificates** -> **Free SSL Certificate**.
2. Pilih domain/subdomain Anda (misal `deliveryku.rf.gd` atau custom domain) dan gunakan penyedia **Let's Encrypt** atau **ZeroSSL** (atau hubungkan ke **Cloudflare Free SSL Full**).
3. Verifikasi CNAME record melalui menu **CNAME Records** di VistaPanel hingga status menjadi **Ready**.
4. Klik **Install SSL Certificate** pada VistaPanel -> **SSL/TLS**.
5. Setelah HTTPS aktif, ubah `APP_URL="https://domainanda.rf.gd"` di `.env` dan aktifkan redirect HTTPS di `.htaccess` jika diperlukan:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

## 4. Keterbatasan InfinityFree & Solusi Kompatibel
- **Tidak ada WebSocket / Node.js / Cron Daemon terus-menerus**: Digantikan oleh **AJAX Polling berkala** (`public/assets/js/deliveryku.js` memanggil `api/order-status` setiap 15 detik dan `api/notifications` setiap 25 detik) yang sangat ringan dan sesuai kebijakan fair-usage InfinityFree.
- **Tidak ada `mysqldump` CLI / `exec()`**: Fitur Backup Database Superadmin menggunakan **BackupService PDO murni PHP** (`SHOW CREATE TABLE` + `INSERT INTO`) sehingga tetap menghasilkan file `.sql` lengkap.
- **SMTP / Email Reset Password**: Hosting gratis InfinityFree membatasi `mail()` PHP; oleh karena itu mekanisme lupa kata sandi menyimpan token terenkripsi berbatas waktu 30 menit di database dan siap dihubungkan ke layanan SMTP eksternal (Mailgun/Brevo/Gmail SMTP) jika diperlukan.
- **Payment Gateway Otomatis**: Sistem mendukung COD, Transfer Bank, dan Pembayaran Manual Upload Bukti Transfer dengan verifikasi Admin/Superadmin (tanpa mengklaim integrasi payment gateway aktif sebelum kredensial resmi tersedia).

---

## 5. Akun Pengujian & Pemisahan Data Demo
Semua akun uji menggunakan kata sandi default: **`DeliveryKu2026!`** (segera ubah setelah masuk):
- **Superadmin (Produksi)**: `superadmin@deliveryku.id`
- **Admin Demo**: `admin@deliveryku.id`
- **Merchant Demo**: `merchant@deliveryku.id` & `merchant2@deliveryku.id`
- **Driver Demo**: `driver@deliveryku.id`
- **Pelanggan Demo**: `pelanggan@deliveryku.id`

Catatan penting: Transaksi demo memiliki kolom `is_demo = 1` dan **terpisah otomatis** dari Laporan Keuangan Produksi (`is_demo = 0`).

---

## 6. Checklist Pengujian Aplikasi
- [ ] Akses langsung ke `/app/`, `/database/`, `/resources/`, `/.env` mengembalikan **403 Forbidden** oleh `.htaccess`.
- [ ] Login gagal 5x berturut-turut memicu pembatasan brute-force (`login_attempts`).
- [ ] Login Superadmin menampilkan 12 KPI, 3 grafik Chart.js, pengaturan komisi/ongkir/rekening, izin Admin, wilayah Indramayu, ekspor Excel/PDF, dan unduh backup `.sql`.
- [ ] Admin hanya dapat membuka modul sesuai checkbox permission dari Superadmin.
- [ ] Pelanggan dapat mendaftar, memilih kecamatan Indramayu, menambah variasi menu ke keranjang, memakai voucher `INDRAMAYUHEMAT`, checkout COD / Upload Bukti Transfer, dan melacak status pesanan.
- [ ] Merchant mengonfirmasi / menolak pesanan -> Menyiapkan -> Siap Diambil Driver.
- [ ] Driver mengambil order -> Navigasi Google Maps -> Diambil -> Dalam Pengiriman -> Selesai (verifikasi PIN) -> Komisi & pendapatan bersih tercatat di tabel `commissions` dan `transactions`.
