<?php
declare(strict_types=1);

/**
 * ============================================================================
 * KONFIGURASI DATABASE & HOSTING INFINITYFREE - DELIVERY KU
 * ============================================================================
 * Silakan sesuaikan nilai di bawah ini dengan data dari:
 * InfinityFree Client Area / Control Panel (VistaPanel) -> MySQL Databases
 * ============================================================================
 */
return [
    // Pengaturan Aplikasi
    'app_name'    => 'Delivery Ku',
    'app_env'     => 'production',
    'app_debug'   => false, // WAJIB false di lingkungan produksi InfinityFree
    'app_url'     => 'https://domainanda.rf.gd', // Ganti dengan domain InfinityFree Anda
    'timezone'    => 'Asia/Jakarta',
    'force_https' => false, // Ubah ke true setelah sertifikat SSL/HTTPS aktif di domain Anda

    // Kredensial Database MySQL InfinityFree
    // Contoh Hostname: sql305.infinityfree.com (BUKAN localhost pada InfinityFree!)
    'db_host'     => 'sql103.infinityfree.com',
    'db_port'     => '3306',
    'db_name'     => 'if0_43456311_deliveryku',
    'db_user'     => 'if0_43456311',
    'db_pass'     => 'deliveryku',
];
