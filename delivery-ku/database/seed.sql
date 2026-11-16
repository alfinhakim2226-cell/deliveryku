-- ============================================================================
-- DELIVERY KU - Seed Data Awal & Data Demo Terpisah (is_demo = 1)
-- Password default seluruh akun demo: DeliveryKu2026!
-- Hash Bcrypt terverifikasi PHP password_verify()
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLES (5 Role Wajib)
INSERT INTO `roles` (`id`, `slug`, `name`, `description`) VALUES
(1, 'superadmin', 'Superadmin', 'Pemilik kendali penuh seluruh konfigurasi dan operasional Delivery Ku'),
(2, 'admin', 'Admin Operasional', 'Pengelola operasional harian sesuai hak akses yang diberikan Superadmin'),
(3, 'merchant', 'Merchant / Mitra', 'Pemilik toko kuliner atau UMKM lokal Kabupaten Indramayu'),
(4, 'driver', 'Driver / Kurir', 'Mitra kurir pengantar pesanan pelanggan'),
(5, 'user', 'Pelanggan', 'Pengguna aplikasi pemesan makanan, minuman, dan produk UMKM')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 2. PERMISSIONS (Granular untuk Admin)
INSERT INTO `permissions` (`id`, `slug`, `name`, `module`) VALUES
(1, 'view_dashboard', 'Lihat Ringkasan Transaksi', 'dashboard'),
(2, 'verify_merchants', 'Verifikasi Dokumen & Toko Merchant', 'merchants'),
(3, 'verify_drivers', 'Verifikasi Dokumen & Kendaraan Driver', 'drivers'),
(4, 'manage_orders', 'Pemantauan & Kelola Pesanan', 'orders'),
(5, 'manage_customers', 'Manajemen Akun Pelanggan', 'customers'),
(6, 'manage_products', 'Manajemen Produk Marketplace', 'products'),
(7, 'manage_complaints', 'Tangani Pengaduan Pelanggan', 'complaints'),
(8, 'manage_vouchers', 'Kelola Voucher Diskon', 'vouchers'),
(9, 'verify_payments', 'Verifikasi Bukti Transfer Manual', 'payments'),
(10, 'view_reports', 'Laporan Transaksi & Operasional', 'reports')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, `id` FROM `permissions`;

-- 3. MASTER WILAYAH KABUPATEN INDRAMAYU (7 Kecamatan Awal + Desa/Kelurahan)
INSERT INTO `districts` (`id`, `regency_name`, `province_name`, `name`, `postal_code`, `latitude`, `longitude`, `base_delivery_fee`, `per_km_fee`, `is_active`) VALUES
(1, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Jatibarang', '45273', -6.4712000, 108.3132000, 6000.00, 2500.00, 1),
(2, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Sukagumiwang', '45274', -6.5284000, 108.3341000, 7000.00, 2500.00, 1),
(3, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Kertasemaya', '45274', -6.5041000, 108.3489000, 7000.00, 2500.00, 1),
(4, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Widasari', '45271', -6.4535000, 108.2861000, 6500.00, 2500.00, 1),
(5, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Tukdana', '45276', -6.5579000, 108.2683000, 7500.00, 2500.00, 1),
(6, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Bangodua', '45272', -6.5210000, 108.2485000, 7500.00, 2500.00, 1),
(7, 'Kabupaten Indramayu', 'Jawa Barat', 'Kecamatan Krangkeng', '45284', -6.4932000, 108.4581000, 8000.00, 2500.00, 1)
ON DUPLICATE KEY UPDATE `postal_code` = VALUES(`postal_code`);

INSERT INTO `villages` (`id`, `district_id`, `name`, `postal_code`, `latitude`, `longitude`, `is_active`) VALUES
(1, 1, 'Jatibarang Baru', '45273', -6.4715000, 108.3125000, 1),
(2, 1, 'Bulak', '45273', -6.4658000, 108.3204000, 1),
(3, 1, 'Pawidean', '45273', -6.4801000, 108.3082000, 1),
(4, 2, 'Sukagumiwang', '45274', -6.5284000, 108.3341000, 1),
(5, 2, 'Cadangpinggan', '45274', -6.5356000, 108.3289000, 1),
(6, 3, 'Kertasemaya', '45274', -6.5041000, 108.3489000, 1),
(7, 3, 'Tulungagung', '45274', -6.5122000, 108.3560000, 1),
(8, 4, 'Widasari', '45271', -6.4535000, 108.2861000, 1),
(9, 4, 'Kongsijaya', '45271', -6.4498000, 108.2945000, 1),
(10, 5, 'Tukdana', '45276', -6.5579000, 108.2683000, 1),
(11, 6, 'Bangodua', '45272', -6.5210000, 108.2485000, 1),
(12, 7, 'Krangkeng', '45284', -6.4932000, 108.4581000, 1)
ON DUPLICATE KEY UPDATE `postal_code` = VALUES(`postal_code`);

-- 4. PENGATURAN APLIKASI DEFAULT
INSERT INTO `application_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('app_name', 'Delivery Ku', 'identity', 'Nama aplikasi marketplace'),
('app_tagline', 'Pesan Antar Cepat Makanan & UMKM Kabupaten Indramayu', 'identity', 'Slogan resmi aplikasi'),
('support_phone', '0811-2345-6789', 'identity', 'Nomor WhatsApp / layanan pelanggan'),
('support_email', 'halo@deliveryku.id', 'identity', 'Email pusat bantuan resmi'),
('default_commission_rate', '15', 'commission', 'Persentase komisi platform default dari subtotal produk (%)'),
('driver_share_rate', '85', 'commission', 'Persentase biaya pengiriman yang diterima driver (%)'),
('service_fee_amount', '2000', 'pricing', 'Biaya layanan platform per pesanan (Rp)'),
('min_transaction_amount', '15000', 'pricing', 'Minimum subtotal transaksi (Rp)'),
('base_delivery_fee', '6000', 'delivery', 'Tarif dasar ongkos kirim (Rp)'),
('per_km_delivery_fee', '2500', 'delivery', 'Tarif ongkos kirim per kilometer (Rp)'),
('bank_accounts_json', '[{"bank":"BRI","number":"0029-01-045678-50-1","holder":"PT Delivery Ku Indramayu"},{"bank":"BCA","number":"3050-8891-22","holder":"PT Delivery Ku Indramayu"},{"bank":"Bank BJB","number":"0098-1245-6700","holder":"PT Delivery Ku Indramayu"}]', 'payment', 'Daftar rekening bank resmi pembayaran manual'),
('include_demo_in_reports', '0', 'system', '0 = Pisahkan data demo dari laporan keuangan produksi, 1 = Tampilkan semua')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 5. KATEGORI USAHA MERCHANT & KATEGORI PRODUK
INSERT INTO `merchant_categories` (`id`, `name`, `slug`, `icon`, `is_active`) VALUES
(1, 'Kuliner & Restoran', 'kuliner-restoran', 'bi-egg-fried', 1),
(2, 'Kedai Kopi & Minuman', 'kedai-kopi-minuman', 'bi-cup-hot', 1),
(3, 'Sembako & Kebutuhan Harian', 'sembako-harian', 'bi-basket2', 1),
(4, 'Oleh-Oleh & UMKM Indramayu', 'oleh-oleh-umkm', 'bi-gift', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`) VALUES
(1, 'Nasi & Lauk Khas', 'nasi-lauk-khas', 'bi-bowl-rice', 1, 1),
(2, 'Seafood & Ikan Bakar', 'seafood-ikan-bakar', 'bi-water', 2, 1),
(3, 'Minuman & Es Mangga', 'minuman-es-mangga', 'bi-cup-straw', 3, 1),
(4, 'Jajanan & Kue', 'jajanan-kue', 'bi-cake2', 4, 1),
(5, 'Kebutuhan Harian', 'kebutuhan-harian', 'bi-bag-check', 5, 1),
(6, 'Produk UMKM Lokal', 'produk-umkm-lokal', 'bi-shop-window', 6, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 6. AKUN PRODUKSI AWAL (Superadmin Utama) + AKUN DEMO TERPISAH (is_demo = 1)
-- Password seluruh akun awal/demo: DeliveryKu2026!
INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone`, `password_hash`, `status`, `is_demo`) VALUES
(1, 1, 'Superadmin Delivery Ku', 'superadmin@deliveryku.id', '081100000001', '$2b$12$vgJ5dEk8WeSltJhRQbUDZukYXMxjZMJqTh7PI6A6dXqsgLXK2rJ9u', 'active', 0),
(2, 2, 'Rizky Pratama (Admin Demo)', 'admin@deliveryku.id', '081100000002', '$2b$12$vgJ5dEk8WeSltJhRQbUDZukYXMxjZMJqTh7PI6A6dXqsgLXK2rJ9u', 'active', 1),
(3, 3, 'Ibu Sari (Warung Lengko Demo)', 'merchant@deliveryku.id', '081224455661', '$2b$12$vgJ5dEk8WeSltJhRQbUDZukYXMxjZMJqTh7PI6A6dXqsgLXK2rJ9u', 'active', 1),
(4, 3, 'Mang Dedi (Seafood Karangsong Demo)', 'merchant2@deliveryku.id', '081224455662', '$2b$12$vgJ5dEk8WeSltJhRQbUDZukYXMxjZMJqTh7PI6A6dXqsgLXK2rJ9u', 'active', 1),
(5, 4, 'Ahmad Fauzi (Driver Demo)', 'driver@deliveryku.id', '081399887701', '$2b$12$vgJ5dEk8WeSltJhRQbUDZukYXMxjZMJqTh7PI6A6dXqsgLXK2rJ9u', 'active', 1),
(6, 5, 'Nadia Putri (Pelanggan Demo)', 'pelanggan@deliveryku.id', '085211223344', '$2b$12$vgJ5dEk8WeSltJhRQbUDZukYXMxjZMJqTh7PI6A6dXqsgLXK2rJ9u', 'active', 1)
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

INSERT IGNORE INTO `admin_user_permissions` (`user_id`, `permission_id`)
SELECT 2, `id` FROM `permissions`;

-- 7. ALAMAT PELANGGAN DEMO
INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `recipient_name`, `recipient_phone`, `district_id`, `village_id`, `full_address`, `postal_code`, `latitude`, `longitude`, `notes`, `is_default`) VALUES
(1, 6, 'Rumah Jatibarang', 'Nadia Putri', '085211223344', 1, 1, 'Jl. Mayor Dasuki No. 48, Jatibarang Baru, Kec. Jatibarang, Kab. Indramayu', '45273', -6.4721000, 108.3145000, 'Gerbang warna biru dekat Masjid Agung', 1),
(2, 6, 'Kantor Sukagumiwang', 'Nadia Putri', '085211223344', 2, 4, 'Jl. Raya By Pass Sukagumiwang Km 14, Kec. Sukagumiwang, Kab. Indramayu', '45274', -6.5290000, 108.3350000, 'Titip ke resepsionis depan', 0)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`);

-- 8. MERCHANT DEMO INDRAMAYU (is_demo = 1)
INSERT INTO `merchants` (`id`, `user_id`, `merchant_category_id`, `district_id`, `village_id`, `store_name`, `slug`, `description`, `address`, `postal_code`, `latitude`, `longitude`, `open_time`, `close_time`, `is_open`, `verification_status`, `rating_avg`, `total_reviews`, `is_recommended`, `is_demo`) VALUES
(1, 3, 1, 1, 1, 'Warung Nasi Lengko Ibu Sari', 'warung-nasi-lengko-ibu-sari', 'Spesialis Nasi Lengko khas Indramayu, Empal Gentong, dan Es Mangga Gedong Gincu segar.', 'Jl. Jend. Sudirman No. 19, Jatibarang, Kab. Indramayu', '45273', -6.4709000, 108.3128000, '07:00:00', '21:30:00', 1, 'approved', 4.90, 42, 1, 1),
(2, 4, 1, 3, 6, 'Ikan Bakar & Kerupuk Udang Mang Dedi', 'ikan-bakar-mang-dedi', 'Sajian ikan bakar segar pesisir Indramayu dan oleh-oleh kerupuk udang UMKM asli.', 'Jl. Raya Kertasemaya No. 102, Kec. Kertasemaya, Kab. Indramayu', '45274', -6.5038000, 108.3492000, '09:00:00', '22:00:00', 1, 'approved', 4.80, 29, 1, 1)
ON DUPLICATE KEY UPDATE `store_name` = VALUES(`store_name`);

-- 9. PRODUK & VARIASI MENU DEMO
INSERT INTO `products` (`id`, `merchant_id`, `category_id`, `name`, `slug`, `description`, `price`, `promo_price`, `stock`, `is_available`, `is_featured`, `sold_count`, `rating_avg`) VALUES
(1, 1, 1, 'Nasi Lengko Komplit Ibu Sari', 'nasi-lengko-komplit', 'Nasi hangat dengan tahu, tempe goreng, tauge rebus, kucai, bumbu kacang gurih, dan kecap manis khas Indramayu.', 18000.00, 15000.00, 80, 1, 1, 145, 4.90),
(2, 1, 1, 'Pindang Gombyang Kepala Ikan', 'pindang-gombyang-indramayu', 'Kuah kuning asam segar khas Indramayu dengan rempah melimpah.', 38000.00, NULL, 30, 1, 1, 64, 4.85),
(3, 1, 3, 'Es Mangga Gedong Gincu Asli', 'es-mangga-gedong-gincu', 'Jus dan potongan mangga gedong gincu manis khas Indramayu.', 14000.00, 12000.00, 100, 1, 1, 210, 4.95),
(4, 2, 2, 'Ikan Etong Bakar Sambal Kecap', 'ikan-etong-bakar', 'Ikan etong segar dibakar arang lengkap dengan lalapan dan sambal terasi.', 42000.00, 39000.00, 25, 1, 1, 88, 4.85),
(5, 2, 6, 'Kerupuk Udang Asli Indramayu (500g)', 'kerupuk-udang-500g', 'Oleh-oleh UMKM kerupuk udang renyah siap goreng maupun matang.', 35000.00, NULL, 50, 1, 1, 112, 4.90),
(6, 2, 5, 'Paket Sembako Hemat Indramayu', 'paket-sembako-hemat', 'Beras 2.5kg, minyak goreng 1L, gula pasir 1kg untuk kebutuhan harian.', 68000.00, 65000.00, 40, 1, 0, 37, 4.80)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `additional_price`, `is_available`) VALUES
(1, 1, 'Original (Tidak Pedas)', 0.00, 1),
(2, 1, 'Pedas + Telur Dadar', 5000.00, 1),
(3, 3, 'Ukuran Reguler', 0.00, 1),
(4, 3, 'Ukuran Jumbo + Topping Mangga', 4000.00, 1),
(5, 4, 'Sambal Kecap', 0.00, 1),
(6, 4, 'Sambal Dadak Pedas + Nasi', 6000.00, 1)
ON DUPLICATE KEY UPDATE `variant_name` = VALUES(`variant_name`);

-- 10. DRIVER DEMO (is_demo = 1)
INSERT INTO `drivers` (`id`, `user_id`, `district_id`, `vehicle_type`, `vehicle_brand`, `license_plate`, `is_online`, `verification_status`, `rating_avg`, `total_deliveries`, `current_lat`, `current_lng`, `is_demo`) VALUES
(1, 5, 1, 'Motor', 'Honda Beat Street', 'E 4521 DK', 1, 'approved', 4.95, 58, -6.4718000, 108.3135000, 1)
ON DUPLICATE KEY UPDATE `license_plate` = VALUES(`license_plate`);

-- 11. BANNER PROMOSI & VOUCHER AKTIF
INSERT INTO `banners` (`id`, `title`, `subtitle`, `badge_text`, `target_url`, `sort_order`, `is_active`) VALUES
(1, 'Festival Kuliner Indramayu', 'Diskon hingga Rp 10.000 untuk Nasi Lengko, Pindang Gombyang & Es Mangga', 'PROMO INDRAMAYU', '?route=user/search', 1, 1),
(2, 'Gratis Ongkir Kecamatan Jatibarang & Kertasemaya', 'Gunakan kode voucher INDRAMAYUHEMAT saat checkout', 'HEMAT ONGKIR', '?route=user/search', 2, 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `vouchers` (`id`, `code`, `title`, `description`, `discount_type`, `discount_value`, `max_discount`, `min_transaction`, `quota`, `used_count`, `start_date`, `end_date`, `is_active`) VALUES
(1, 'INDRAMAYUHEMAT', 'Potongan Ongkir & Belanja Rp 5.000', 'Berlaku untuk seluruh merchant di Kabupaten Indramayu minimal belanja Rp 25.000', 'fixed', 5000.00, 5000.00, 25000.00, 500, 1, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1),
(2, 'UMKM15', 'Diskon UMKM Lokal 15%', 'Dukung produk UMKM Indramayu maksimal diskon Rp 12.000', 'percent', 15.00, 12000.00, 30000.00, 300, 0, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 12. SAMPEL TRANSAKSI DEMO TERPISAH (is_demo = 1, tidak mencemari laporan produksi)
INSERT INTO `orders` (`id`, `order_code`, `user_id`, `merchant_id`, `driver_id`, `district_id`, `voucher_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `delivery_lat`, `delivery_lng`, `distance_km`, `subtotal`, `merchant_discount`, `voucher_discount`, `delivery_fee`, `service_fee`, `platform_commission`, `merchant_net_income`, `driver_net_income`, `total_amount`, `payment_method`, `status`, `customer_notes`, `completed_at`, `is_demo`) VALUES
(1, 'DK-DEMO-20261116-001', 6, 1, 1, 1, 1, 'Nadia Putri', '085211223344', 'Jl. Mayor Dasuki No. 48, Jatibarang Baru, Kab. Indramayu', -6.4721000, 108.3145000, 1.80, 30000.00, 5000.00, 5000.00, 6000.00, 2000.00, 4500.00, 25500.00, 5100.00, 33000.00, 'cod', 'selesai', 'Sambal pisah ya Bu', NOW(), 1)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `variant_name`, `original_price`, `unit_price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 1, 'Nasi Lengko Komplit Ibu Sari', 'Original (Tidak Pedas)', 18000.00, 15000.00, 1, 15000.00),
(2, 1, 3, 3, 'Es Mangga Gedong Gincu Asli', 'Ukuran Reguler', 14000.00, 12000.00, 1, 12000.00)
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`);

INSERT INTO `payments` (`id`, `order_id`, `payment_code`, `method`, `amount`, `status`, `paid_at`) VALUES
(1, 1, 'PAY-DEMO-001', 'cod', 33000.00, 'verified', NOW())
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `deliveries` (`id`, `order_id`, `driver_id`, `pickup_address`, `pickup_lat`, `pickup_lng`, `dropoff_address`, `dropoff_lat`, `dropoff_lng`, `delivery_fee`, `driver_earning`, `verification_pin`, `status`, `accepted_at`, `picked_up_at`, `delivered_at`) VALUES
(1, 1, 1, 'Jl. Jend. Sudirman No. 19, Jatibarang', -6.4709000, 108.3128000, 'Jl. Mayor Dasuki No. 48, Jatibarang Baru', -6.4721000, 108.3145000, 6000.00, 5100.00, '4821', 'delivered', NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `commissions` (`id`, `order_id`, `merchant_id`, `driver_id`, `gross_subtotal`, `commission_rate`, `platform_commission_amount`, `service_fee_amount`, `merchant_net_amount`, `driver_fee_rate`, `driver_net_amount`, `platform_delivery_cut`, `is_demo`) VALUES
(1, 1, 1, 1, 30000.00, 15.00, 4500.00, 2000.00, 25500.00, 85.00, 5100.00, 900.00, 1)
ON DUPLICATE KEY UPDATE `gross_subtotal` = VALUES(`gross_subtotal`);

INSERT INTO `transactions` (`id`, `transaction_code`, `order_id`, `user_id`, `type`, `amount`, `direction`, `description`, `status`, `is_demo`) VALUES
(1, 'TRX-DEMO-001', 1, 6, 'order_payment', 33000.00, 'credit', 'Pembayaran pesanan demo DK-DEMO-20261116-001 (COD)', 'settled', 1),
(2, 'TRX-DEMO-002', 1, 3, 'merchant_payout', 25500.00, 'credit', 'Pendapatan bersih merchant untuk pesanan DK-DEMO-20261116-001', 'settled', 1),
(3, 'TRX-DEMO-003', 1, 5, 'driver_earning', 5100.00, 'credit', 'Upah pengantaran kurir pesanan DK-DEMO-20261116-001', 'settled', 1)
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);

INSERT INTO `reviews` (`id`, `order_id`, `user_id`, `merchant_id`, `driver_id`, `merchant_rating`, `driver_rating`, `comment`, `is_visible`) VALUES
(1, 1, 6, 1, 1, 5, 5, 'Nasi lengkonya masih hangat, es mangganya segar banget, kurir ramah dan tepat waktu!', 1)
ON DUPLICATE KEY UPDATE `merchant_rating` = VALUES(`merchant_rating`);

SET FOREIGN_KEY_CHECKS = 1;
