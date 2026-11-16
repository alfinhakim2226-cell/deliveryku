<?php
$user = current_user();
$currentRoute = trim((string)($_GET['route'] ?? 'user/home'), '/');
$flashes = flash_get_all();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Delivery Ku — Pesan Antar Indramayu') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/deliveryku.css')) ?>" rel="stylesheet">
</head>
<body class="user-mobile-pad">
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dk-blue" href="<?= e(route_url('user/home')) ?>">
            <img src="<?= e(asset_url('images/logo.svg')) ?>" alt="Delivery Ku" width="34" height="34" class="rounded-3">
            <span class="brand-font">Delivery Ku</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if (is_logged_in()): ?>
                <a href="<?= e(route_url('user/cart')) ?>" class="btn btn-outline-primary btn-sm position-relative">
                    <i class="bi bi-cart3"></i><span class="d-none d-sm-inline ms-1">Keranjang</span>
                </a>
                <a href="<?= e(route_url('user/profile')) ?>" class="btn btn-light btn-sm border position-relative">
                    <i class="bi bi-bell"></i>
                    <span id="notif-unread-badge" data-api-url="<?= e(route_url('api/notifications')) ?>" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;font-size:.65rem;">0</span>
                </a>
                <?php if (($user['role_slug'] ?? '') !== 'user'): ?>
                    <a href="<?= e(route_url(ROLE_HOME_ROUTES[$user['role_slug']] ?? 'user/home')) ?>" class="btn btn-dk-green btn-sm">
                        Panel <?= e(ucfirst($user['role_slug'])) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= e(route_url('auth/logout')) ?>" class="btn btn-outline-danger btn-sm d-none d-md-inline-flex">Keluar</a>
            <?php else: ?>
                <a href="<?= e(route_url('auth/login')) ?>" class="btn btn-outline-primary btn-sm">Masuk</a>
                <a href="<?= e(route_url('auth/register')) ?>" class="btn btn-dk-primary btn-sm">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-3 py-md-4">
    <?php foreach ($flashes as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show alert-autoclose" role="alert">
            <?= e($f['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<!-- Bottom Navigation Pelanggan (Mobile-First) -->
<nav class="dk-bottom-nav d-lg-none">
    <a href="<?= e(route_url('user/home')) ?>" class="<?= $currentRoute === 'user/home' || $currentRoute === '' ? 'active' : '' ?>">
        <i class="bi bi-house-door-fill fs-5"></i><span>Beranda</span>
    </a>
    <a href="<?= e(route_url('user/search')) ?>" class="<?= $currentRoute === 'user/search' ? 'active' : '' ?>">
        <i class="bi bi-search fs-5"></i><span>Pencarian</span>
    </a>
    <a href="<?= e(route_url('user/orders')) ?>" class="<?= str_starts_with($currentRoute, 'user/order') ? 'active' : '' ?>">
        <i class="bi bi-receipt-cutoff fs-5"></i><span>Pesanan</span>
    </a>
    <a href="<?= e(route_url('user/favorites')) ?>" class="<?= $currentRoute === 'user/favorites' ? 'active' : '' ?>">
        <i class="bi bi-heart-fill fs-5"></i><span>Favorit</span>
    </a>
    <a href="<?= e(is_logged_in() ? route_url('user/profile') : route_url('auth/login')) ?>" class="<?= $currentRoute === 'user/profile' ? 'active' : '' ?>">
        <i class="bi bi-person-circle fs-5"></i><span>Profil</span>
    </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset_url('js/deliveryku.js')) ?>"></script>
</body>
</html>
