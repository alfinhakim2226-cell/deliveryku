-- ============================================================================
-- DELIVERY KU - DATABASE LENGKAP (SCHEMA + SEED DATA INDRAMAYU)
-- Siap impor langsung via phpMyAdmin InfinityFree
-- ============================================================================

-- ============================================================================
-- DELIVERY KU - Skema Database MySQL / MariaDB (Kompatibel InfinityFree)
-- Target Operasional: Kabupaten Indramayu, Jawa Barat
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. permissions
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `module` VARCHAR(60) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_role_perm` (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. districts (Kecamatan di Kabupaten Indramayu & sekitarnya)
CREATE TABLE IF NOT EXISTS `districts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `regency_name` VARCHAR(100) NOT NULL DEFAULT 'Kabupaten Indramayu',
    `province_name` VARCHAR(100) NOT NULL DEFAULT 'Jawa Barat',
    `name` VARCHAR(120) NOT NULL,
    `postal_code` VARCHAR(10) NULL,
    `latitude` DECIMAL(10,7) NULL,
    `longitude` DECIMAL(10,7) NULL,
    `base_delivery_fee` DECIMAL(12,2) NOT NULL DEFAULT 6000.00,
    `per_km_fee` DECIMAL(12,2) NOT NULL DEFAULT 2500.00,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_district_regency` (`regency_name`, `name`),
    INDEX `idx_district_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. villages (Desa / Kelurahan)
CREATE TABLE IF NOT EXISTS `villages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `district_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `postal_code` VARCHAR(10) NULL,
    `latitude` DECIMAL(10,7) NULL,
    `longitude` DECIMAL(10,7) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_village_district` (`district_id`),
    CONSTRAINT `fk_village_district` FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. users
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(25) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `avatar_path` VARCHAR(255) NULL,
    `status` ENUM('active','inactive','suspended','pending') NOT NULL DEFAULT 'active',
    `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
    `reset_token` VARCHAR(120) NULL,
    `reset_expires_at` DATETIME NULL,
    `last_login_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_users_role` (`role_id`),
    INDEX `idx_users_status` (`status`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel tambahan untuk keamanan brute-force login & granular permission per admin
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `is_success` TINYINT(1) NOT NULL DEFAULT 0,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_login_email_ip` (`email`, `ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_user_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_admin_perm` (`user_id`, `permission_id`),
    CONSTRAINT `fk_aup_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_aup_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. user_addresses
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `label` VARCHAR(60) NOT NULL DEFAULT 'Rumah',
    `recipient_name` VARCHAR(150) NOT NULL,
    `recipient_phone` VARCHAR(25) NOT NULL,
    `district_id` INT UNSIGNED NULL,
    `village_id` INT UNSIGNED NULL,
    `full_address` TEXT NOT NULL,
    `postal_code` VARCHAR(10) NULL,
    `latitude` DECIMAL(10,7) NULL,
    `longitude` DECIMAL(10,7) NULL,
    `notes` VARCHAR(255) NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_ua_user` (`user_id`),
    CONSTRAINT `fk_ua_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ua_district` FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ua_village` FOREIGN KEY (`village_id`) REFERENCES `villages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. merchant_categories
CREATE TABLE IF NOT EXISTS `merchant_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `icon` VARCHAR(60) NULL DEFAULT 'bi-shop',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. merchants
CREATE TABLE IF NOT EXISTS `merchants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `merchant_category_id` INT UNSIGNED NULL,
    `district_id` INT UNSIGNED NULL,
    `village_id` INT UNSIGNED NULL,
    `store_name` VARCHAR(160) NOT NULL,
    `slug` VARCHAR(180) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `logo_path` VARCHAR(255) NULL,
    `banner_path` VARCHAR(255) NULL,
    `address` TEXT NOT NULL,
    `postal_code` VARCHAR(10) NULL,
    `latitude` DECIMAL(10,7) NULL,
    `longitude` DECIMAL(10,7) NULL,
    `open_time` TIME NOT NULL DEFAULT '08:00:00',
    `close_time` TIME NOT NULL DEFAULT '21:00:00',
    `is_open` TINYINT(1) NOT NULL DEFAULT 1,
    `verification_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `verification_notes` TEXT NULL,
    `commission_rate` DECIMAL(5,2) NULL COMMENT 'Override komisi persen khusus merchant jika diisi',
    `rating_avg` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    `total_reviews` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_recommended` TINYINT(1) NOT NULL DEFAULT 0,
    `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_merchant_verify` (`verification_status`, `is_open`),
    INDEX `idx_merchant_district` (`district_id`),
    CONSTRAINT `fk_merchant_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_merchant_cat` FOREIGN KEY (`merchant_category_id`) REFERENCES `merchant_categories`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_merchant_dist` FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_merchant_vil` FOREIGN KEY (`village_id`) REFERENCES `villages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. merchant_documents
CREATE TABLE IF NOT EXISTS `merchant_documents` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `doc_type` ENUM('ktp','siup_nib','foto_toko','buku_tabungan','lainnya') NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `notes` VARCHAR(255) NULL,
    `verified_by` BIGINT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mdoc_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. categories (Kategori Produk)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `icon` VARCHAR(60) NOT NULL DEFAULT 'bi-bag',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. products
CREATE TABLE IF NOT EXISTS `products` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NULL,
    `name` VARCHAR(180) NOT NULL,
    `slug` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(12,2) NOT NULL,
    `promo_price` DECIMAL(12,2) NULL,
    `stock` INT NOT NULL DEFAULT 100,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sold_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `rating_avg` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_prod_merchant` (`merchant_id`),
    INDEX `idx_prod_cat` (`category_id`),
    INDEX `idx_prod_avail` (`is_available`),
    CONSTRAINT `fk_prod_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. product_variants
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_name` VARCHAR(120) NOT NULL COMMENT 'Contoh: Level Pedas, Ukuran Jumbo',
    `additional_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pv_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. product_images
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pimg_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. carts
CREATE TABLE IF NOT EXISTS `carts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_cart_user_merchant` (`user_id`, `merchant_id`),
    CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cart_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ci_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ci_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. drivers
CREATE TABLE IF NOT EXISTS `drivers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `district_id` INT UNSIGNED NULL,
    `vehicle_type` ENUM('Motor','Mobil','Sepeda Listrik') NOT NULL DEFAULT 'Motor',
    `vehicle_brand` VARCHAR(100) NULL,
    `license_plate` VARCHAR(30) NOT NULL,
    `is_online` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `verification_notes` TEXT NULL,
    `rating_avg` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    `total_deliveries` INT UNSIGNED NOT NULL DEFAULT 0,
    `current_lat` DECIMAL(10,7) NULL,
    `current_lng` DECIMAL(10,7) NULL,
    `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_driver_online` (`is_online`, `verification_status`),
    CONSTRAINT `fk_driver_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_driver_district` FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. driver_documents
CREATE TABLE IF NOT EXISTS `driver_documents` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `driver_id` BIGINT UNSIGNED NOT NULL,
    `doc_type` ENUM('ktp','sim','stnk','skck','foto_kendaraan') NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `notes` VARCHAR(255) NULL,
    `verified_by` BIGINT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ddoc_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. vouchers
CREATE TABLE IF NOT EXISTS `vouchers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `discount_type` ENUM('percent','fixed') NOT NULL DEFAULT 'fixed',
    `discount_value` DECIMAL(12,2) NOT NULL,
    `max_discount` DECIMAL(12,2) NULL,
    `min_transaction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `quota` INT UNSIGNED NOT NULL DEFAULT 100,
    `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. orders
CREATE TABLE IF NOT EXISTS `orders` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(40) NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `driver_id` BIGINT UNSIGNED NULL,
    `district_id` INT UNSIGNED NULL,
    `voucher_id` BIGINT UNSIGNED NULL,
    `recipient_name` VARCHAR(150) NOT NULL,
    `recipient_phone` VARCHAR(25) NOT NULL,
    `delivery_address` TEXT NOT NULL,
    `delivery_lat` DECIMAL(10,7) NULL,
    `delivery_lng` DECIMAL(10,7) NULL,
    `distance_km` DECIMAL(6,2) NOT NULL DEFAULT 2.00,
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `merchant_discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `voucher_discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `delivery_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `service_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `platform_commission` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `merchant_net_income` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `driver_net_income` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('cod','bank_transfer','manual_proof') NOT NULL DEFAULT 'cod',
    `status` ENUM(
        'menunggu_pembayaran',
        'menunggu_konfirmasi_merchant',
        'pesanan_dikonfirmasi',
        'sedang_disiapkan',
        'menunggu_driver',
        'driver_menuju_merchant',
        'pesanan_diambil',
        'dalam_pengiriman',
        'selesai',
        'dibatalkan',
        'pengembalian_dana'
    ) NOT NULL DEFAULT 'menunggu_konfirmasi_merchant',
    `customer_notes` TEXT NULL,
    `cancel_reason` VARCHAR(255) NULL,
    `customer_confirmed_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_orders_user` (`user_id`),
    INDEX `idx_orders_merchant` (`merchant_id`),
    INDEX `idx_orders_driver` (`driver_id`),
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_demo_created` (`is_demo`, `created_at`),
    CONSTRAINT `fk_ord_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_ord_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`),
    CONSTRAINT `fk_ord_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ord_district` FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ord_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. order_items
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NULL,
    `product_name` VARCHAR(180) NOT NULL,
    `variant_name` VARCHAR(120) NULL,
    `original_price` DECIMAL(12,2) NOT NULL,
    `unit_price` DECIMAL(12,2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. order_status_histories
CREATE TABLE IF NOT EXISTS `order_status_histories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `from_status` VARCHAR(50) NULL,
    `to_status` VARCHAR(50) NOT NULL,
    `changed_by` BIGINT UNSIGNED NULL,
    `actor_role` VARCHAR(30) NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_osh_order` (`order_id`),
    CONSTRAINT `fk_osh_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. payments
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `payment_code` VARCHAR(50) NOT NULL UNIQUE,
    `method` ENUM('cod','bank_transfer','manual_proof') NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `bank_name` VARCHAR(80) NULL,
    `account_number` VARCHAR(60) NULL,
    `status` ENUM('pending','waiting_verification','verified','rejected','refunded') NOT NULL DEFAULT 'pending',
    `paid_at` DATETIME NULL,
    `verified_by` BIGINT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `verification_notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. payment_proofs
CREATE TABLE IF NOT EXISTS `payment_proofs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `payment_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `proof_image_path` VARCHAR(255) NOT NULL,
    `transfer_amount` DECIMAL(12,2) NOT NULL,
    `transfer_time` DATETIME NOT NULL,
    `sender_name` VARCHAR(120) NULL,
    `sender_bank` VARCHAR(80) NULL,
    `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `admin_notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pp_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. deliveries
CREATE TABLE IF NOT EXISTS `deliveries` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `driver_id` BIGINT UNSIGNED NULL,
    `pickup_address` TEXT NOT NULL,
    `pickup_lat` DECIMAL(10,7) NULL,
    `pickup_lng` DECIMAL(10,7) NULL,
    `dropoff_address` TEXT NOT NULL,
    `dropoff_lat` DECIMAL(10,7) NULL,
    `dropoff_lng` DECIMAL(10,7) NULL,
    `delivery_fee` DECIMAL(12,2) NOT NULL,
    `driver_earning` DECIMAL(12,2) NOT NULL,
    `verification_pin` VARCHAR(10) NULL,
    `status` ENUM('waiting_driver','accepted','heading_to_merchant','picked_up','on_the_way','delivered','cancelled') NOT NULL DEFAULT 'waiting_driver',
    `accepted_at` DATETIME NULL,
    `picked_up_at` DATETIME NULL,
    `delivered_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_deliv_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_deliv_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. delivery_status_histories
CREATE TABLE IF NOT EXISTS `delivery_status_histories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `delivery_id` BIGINT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `notes` VARCHAR(255) NULL,
    `latitude` DECIMAL(10,7) NULL,
    `longitude` DECIMAL(10,7) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_dsh_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. commissions
CREATE TABLE IF NOT EXISTS `commissions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `driver_id` BIGINT UNSIGNED NULL,
    `gross_subtotal` DECIMAL(12,2) NOT NULL,
    `commission_rate` DECIMAL(5,2) NOT NULL,
    `platform_commission_amount` DECIMAL(12,2) NOT NULL,
    `service_fee_amount` DECIMAL(12,2) NOT NULL,
    `merchant_net_amount` DECIMAL(12,2) NOT NULL,
    `driver_fee_rate` DECIMAL(5,2) NOT NULL DEFAULT 85.00,
    `driver_net_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `platform_delivery_cut` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comm_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comm_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. transactions
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_code` VARCHAR(50) NOT NULL UNIQUE,
    `order_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('order_payment','merchant_payout','driver_earning','platform_fee','refund') NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `direction` ENUM('credit','debit') NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `status` ENUM('pending','settled','cancelled') NOT NULL DEFAULT 'settled',
    `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_trx_demo_created` (`is_demo`, `created_at`),
    CONSTRAINT `fk_trx_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_trx_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. voucher_usages
CREATE TABLE IF NOT EXISTS `voucher_usages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `voucher_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `discount_amount` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_vu_order` (`order_id`),
    CONSTRAINT `fk_vu_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vu_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vu_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30. reviews
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `driver_id` BIGINT UNSIGNED NULL,
    `merchant_rating` TINYINT UNSIGNED NOT NULL,
    `driver_rating` TINYINT UNSIGNED NULL,
    `comment` TEXT NULL,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_rev_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_rev_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31. favorites
CREATE TABLE IF NOT EXISTS `favorites` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `merchant_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_fav_user_merchant` (`user_id`, `merchant_id`),
    CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fav_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(160) NOT NULL,
    `message` TEXT NOT NULL,
    `link_url` VARCHAR(255) NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user_read` (`user_id`, `is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33. banners
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(160) NOT NULL,
    `subtitle` VARCHAR(255) NULL,
    `image_path` VARCHAR(255) NULL,
    `target_url` VARCHAR(255) NULL,
    `badge_text` VARCHAR(40) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34. application_settings
CREATE TABLE IF NOT EXISTS `application_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 35. complaints
CREATE TABLE IF NOT EXISTS `complaints` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(30) NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NULL,
    `subject` VARCHAR(180) NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    `admin_response` TEXT NULL,
    `handled_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comp_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 36. activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `role_slug` VARCHAR(40) NULL,
    `action` VARCHAR(120) NOT NULL,
    `module` VARCHAR(60) NOT NULL,
    `description` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_al_user` (`user_id`),
    INDEX `idx_al_module` (`module`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

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
