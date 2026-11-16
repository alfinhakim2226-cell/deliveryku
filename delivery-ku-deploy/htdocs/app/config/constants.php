<?php
declare(strict_types=1);

/**
 * Konstanta Status Pesanan, Transisi Sah, dan Label Bahasa Indonesia
 */
const ORDER_STATUSES = [
    'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'badge' => 'warning'],
    'menunggu_konfirmasi_merchant' => ['label' => 'Menunggu Konfirmasi Merchant', 'badge' => 'info'],
    'pesanan_dikonfirmasi' => ['label' => 'Pesanan Dikonfirmasi', 'badge' => 'primary'],
    'sedang_disiapkan' => ['label' => 'Sedang Disiapkan', 'badge' => 'primary'],
    'menunggu_driver' => ['label' => 'Menunggu Driver', 'badge' => 'secondary'],
    'driver_menuju_merchant' => ['label' => 'Driver Menuju Merchant', 'badge' => 'info'],
    'pesanan_diambil' => ['label' => 'Pesanan Diambil', 'badge' => 'info'],
    'dalam_pengiriman' => ['label' => 'Dalam Pengiriman', 'badge' => 'primary'],
    'selesai' => ['label' => 'Selesai', 'badge' => 'success'],
    'dibatalkan' => ['label' => 'Dibatalkan', 'badge' => 'danger'],
    'pengembalian_dana' => ['label' => 'Pengembalian Dana', 'badge' => 'dark'],
];

const ORDER_STATUS_TRANSITIONS = [
    'menunggu_pembayaran' => ['menunggu_konfirmasi_merchant', 'dibatalkan'],
    'menunggu_konfirmasi_merchant' => ['pesanan_dikonfirmasi', 'dibatalkan'],
    'pesanan_dikonfirmasi' => ['sedang_disiapkan', 'dibatalkan'],
    'sedang_disiapkan' => ['menunggu_driver', 'dibatalkan'],
    'menunggu_driver' => ['driver_menuju_merchant', 'dibatalkan'],
    'driver_menuju_merchant' => ['pesanan_diambil', 'dibatalkan'],
    'pesanan_diambil' => ['dalam_pengiriman'],
    'dalam_pengiriman' => ['selesai', 'pengembalian_dana'],
    'selesai' => ['pengembalian_dana'],
    'dibatalkan' => ['pengembalian_dana'],
    'pengembalian_dana' => [],
];

const PAYMENT_METHODS = [
    'cod' => 'COD (Bayar di Tempat)',
    'bank_transfer' => 'Transfer Bank',
    'manual_proof' => 'Transfer Manual (Upload Bukti)',
];

const ROLE_HOME_ROUTES = [
    'superadmin' => 'superadmin/dashboard',
    'admin' => 'admin/dashboard',
    'merchant' => 'merchant/dashboard',
    'driver' => 'driver/dashboard',
    'user' => 'user/home',
];
