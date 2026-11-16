<?php
$user = current_user();
$role = $user['role_slug'] ?? 'user';
$currentRoute = trim((string)($_GET['route'] ?? ''), '/');
$flashes = flash_get_all();

$menus = match ($role) {
    'superadmin' => [
        ['route' => 'superadmin/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['route' => 'superadmin/users', 'icon' => 'bi-people', 'label' => 'Manajemen Pengguna & Admin'],
        ['route' => 'superadmin/merchants', 'icon' => 'bi-shop', 'label' => 'Manajemen Merchant'],
        ['route' => 'superadmin/drivers', 'icon' => 'bi-scooter', 'label' => 'Manajemen Driver'],
        ['route' => 'superadmin/products', 'icon' => 'bi-box-seam', 'label' => 'Kategori & Produk'],
        ['route' => 'superadmin/orders', 'icon' => 'bi-receipt', 'label' => 'Pesanan & Pengiriman'],
        ['route' => 'superadmin/payments', 'icon' => 'bi-credit-card', 'label' => 'Pembayaran & Komisi'],
        ['route' => 'superadmin/vouchers', 'icon' => 'bi-ticket-perforated', 'label' => 'Manajemen Voucher'],
        ['route' => 'superadmin/regions', 'icon' => 'bi-geo-alt', 'label' => 'Wilayah Operasional'],
        ['route' => 'superadmin/banners', 'icon' => 'bi-megaphone', 'label' => 'Banner & Ulasan'],
        ['route' => 'superadmin/reports', 'icon' => 'bi-graph-up-arrow', 'label' => 'Laporan Keuangan & Transaksi'],
        ['route' => 'superadmin/settings', 'icon' => 'bi-sliders', 'label' => 'Pengaturan Aplikasi & Tarif'],
        ['route' => 'superadmin/logs', 'icon' => 'bi-journal-text', 'label' => 'Log Aktivitas'],
        ['route' => 'superadmin/backup', 'icon' => 'bi-database-down', 'label' => 'Backup Database (SQL)'],
        ['route' => 'superadmin/profile', 'icon' => 'bi-person-badge', 'label' => 'Profil Superadmin'],
    ],
    'admin' => array_values(array_filter([
        has_permission('view_dashboard') ? ['route' => 'admin/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Ringkasan Transaksi'] : null,
        (has_permission('verify_merchants') || has_permission('verify_drivers') || has_permission('verify_payments'))
            ? ['route' => 'admin/verifications', 'icon' => 'bi-shield-check', 'label' => 'Verifikasi Mitra & Bayar'] : null,
        has_permission('manage_orders') ? ['route' => 'admin/orders', 'icon' => 'bi-receipt', 'label' => 'Pemantauan Pesanan'] : null,
        has_permission('manage_complaints') ? ['route' => 'admin/complaints', 'icon' => 'bi-chat-left-dots', 'label' => 'Pengaduan Pelanggan'] : null,
        has_permission('view_reports') ? ['route' => 'admin/reports', 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Laporan Operasional'] : null,
    ])),
    'merchant' => [
        ['route' => 'merchant/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard Toko'],
        ['route' => 'merchant/orders', 'icon' => 'bi-bell', 'label' => 'Pesanan Masuk'],
        ['route' => 'merchant/products', 'icon' => 'bi-bag-plus', 'label' => 'Menu & Produk'],
        ['route' => 'merchant/finance', 'icon' => 'bi-wallet2', 'label' => 'Omzet & Pendapatan'],
        ['route' => 'merchant/reviews', 'icon' => 'bi-star', 'label' => 'Penilaian Pelanggan'],
        ['route' => 'merchant/profile', 'icon' => 'bi-shop-window', 'label' => 'Profil & Jam Toko'],
    ],
    'driver' => [
        ['route' => 'driver/dashboard', 'icon' => 'bi-scooter', 'label' => 'Order & Pengantaran Aktif'],
        ['route' => 'driver/deliveries', 'icon' => 'bi-clock-history', 'label' => 'Riwayat & Penghasilan'],
        ['route' => 'driver/profile', 'icon' => 'bi-person-vcard', 'label' => 'Profil & Dokumen Kurir'],
    ],
    default => [],
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Dashboard - Delivery Ku') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/deliveryku.css')) ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="dk-shell">
    <!-- Sidebar Desktop -->
    <aside class="dk-sidebar p-3 d-none d-lg-flex flex-column">
        <a href="<?= e(route_url(ROLE_HOME_ROUTES[$role] ?? 'user/home')) ?>" class="d-flex align-items-center gap-2 text-white text-decoration-none mb-4 px-2">
            <img src="<?= e(asset_url('images/logo.svg')) ?>" alt="Delivery Ku" width="38" height="38" class="rounded-3">
            <div>
                <div class="fw-bold brand-font lh-1">Delivery Ku</div>
                <small class="text-white-50" style="font-size:.72rem;">Panel <?= e(strtoupper($role)) ?></small>
            </div>
        </a>
        <nav class="nav flex-column flex-grow-1">
            <?php foreach ($menus as $m): ?>
                <a class="nav-link <?= $currentRoute === $m['route'] ? 'active' : '' ?>" href="<?= e(route_url($m['route'])) ?>">
                    <i class="bi <?= e($m['icon']) ?>"></i>
                    <span><?= e($m['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="border-top border-white border-opacity-10 pt-3 mt-3">
            <a class="nav-link text-warning" href="<?= e(route_url('user/home')) ?>">
                <i class="bi bi-globe2"></i><span>Lihat Beranda Pelanggan</span>
            </a>
            <a class="nav-link text-danger-emphasis" href="<?= e(route_url('auth/logout')) ?>" data-confirm="Keluar dari akun Delivery Ku?">
                <i class="bi bi-box-arrow-right"></i><span>Keluar (Logout)</span>
            </a>
        </div>
    </aside>

    <!-- Main Area -->
    <div class="dk-main">
        <header class="dk-topbar d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-primary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><?= e($pageTitle ?? 'Delivery Ku') ?></h6>
                    <small class="text-muted"><i class="bi bi-geo-alt-fill text-primary"></i> Operasional Kabupaten Indramayu, Jawa Barat</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill text-bg-light border">
                    <i class="bi bi-person-circle text-primary me-1"></i><?= e($user['full_name'] ?? '') ?>
                </span>
                <a href="<?= e(route_url('auth/logout')) ?>" class="btn btn-outline-danger btn-sm d-none d-sm-inline-flex align-items-center gap-1" data-confirm="Keluar dari sesi sekarang?">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </header>

        <main class="p-3 p-md-4 flex-grow-1">
            <?php foreach ($flashes as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show alert-autoclose" role="alert">
                    <?= e($f['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<!-- Offcanvas Mobile Sidebar -->
<div class="offcanvas offcanvas-start bg-dk-navy text-white" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold">Delivery Ku (<?= e(strtoupper($role)) ?>)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="nav flex-column">
            <?php foreach ($menus as $m): ?>
                <a class="nav-link text-white <?= $currentRoute === $m['route'] ? 'bg-primary rounded' : '' ?>" href="<?= e(route_url($m['route'])) ?>">
                    <i class="bi <?= e($m['icon']) ?> me-2"></i><?= e($m['label']) ?>
                </a>
            <?php endforeach; ?>
            <hr class="border-white border-opacity-25">
            <a class="nav-link text-warning" href="<?= e(route_url('auth/logout')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
        </nav>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset_url('js/deliveryku.js')) ?>"></script>
</body>
</html>
