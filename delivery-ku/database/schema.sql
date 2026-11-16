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
