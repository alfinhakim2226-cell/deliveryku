<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Masuk - Delivery Ku') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/deliveryku.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex align-items-center min-vh-100 py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-3">
                <img src="<?= e(asset_url('images/logo.svg')) ?>" alt="Delivery Ku" width="56" height="56" class="mb-2 rounded-4 shadow-sm">
                <h3 class="fw-bold text-dk-blue mb-0">Delivery Ku</h3>
                <p class="text-muted small">Marketplace Pesan Antar Kabupaten Indramayu</p>
            </div>

            <?php foreach (flash_get_all() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
            <?php endforeach; ?>

            <div class="dk-card p-4">
                <h5 class="fw-bold mb-3">Masuk ke Akun Anda</h5>
                <form method="POST" action="<?= e(route_url('auth/login')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email" id="login_email" class="form-control" required placeholder="nama@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Kata Sandi</label>
                        <input type="password" name="password" id="login_password" class="form-control" required placeholder="Masukkan kata sandi">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="<?= e(route_url('auth/forgot-password')) ?>" class="small text-decoration-none">Lupa kata sandi?</a>
                        <a href="<?= e(route_url('user/home')) ?>" class="small text-decoration-none">Jelajahi Toko</a>
                    </div>
                    <button type="submit" class="btn btn-dk-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </form>

                <hr class="my-4">
                <div class="text-center small">
                    Belum punya akun? <a href="<?= e(route_url('auth/register')) ?>" class="fw-semibold text-decoration-none">Daftar Pelanggan / Mitra / Kurir</a>
                </div>
            </div>

            <div class="dk-card p-3 mt-3 small">
                <div class="fw-bold text-dk-navy mb-2"><i class="bi bi-key me-1"></i> Akun Uji Pengujian (Password: <code>DeliveryKu2026!</code>)</div>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm quick-login" data-email="superadmin@deliveryku.id">Superadmin</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-login" data-email="admin@deliveryku.id">Admin Demo</button>
                    <button type="button" class="btn btn-outline-success btn-sm quick-login" data-email="merchant@deliveryku.id">Merchant Demo</button>
                    <button type="button" class="btn btn-outline-info btn-sm quick-login" data-email="driver@deliveryku.id">Driver Demo</button>
                    <button type="button" class="btn btn-outline-dark btn-sm quick-login" data-email="pelanggan@deliveryku.id">Pelanggan Demo</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.quick-login').forEach(b => {
    b.addEventListener('click', () => {
        document.getElementById('login_email').value = b.dataset.email;
        document.getElementById('login_password').value = 'DeliveryKu2026!';
    });
});
</script>
</body>
</html>
