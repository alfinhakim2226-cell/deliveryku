<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Kata Sandi - Delivery Ku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/deliveryku.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex align-items-center min-vh-100">
<div class="container">
    <div class="dk-card p-4 mx-auto" style="max-width:460px">
        <h5 class="fw-bold text-dk-blue mb-2">Atur Ulang Kata Sandi</h5>
        <?php foreach (flash_get_all() as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
        <form method="POST" action="<?= e(route_url('auth/forgot-password')) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email Terdaftar</label>
                <input type="email" name="email" value="<?= e($_GET['email'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kode Reset (opsional jika sudah punya)</label>
                <input type="text" name="reset_token" class="form-control" placeholder="Kosongkan untuk minta kode">
            </div>
            <div class="mb-3">
                <label class="form-label">Kata Sandi Baru (jika sudah punya kode)</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-dk-primary w-100">Proses Reset Kata Sandi</button>
        </form>
        <div class="text-center mt-3"><a href="<?= e(route_url('auth/login')) ?>" class="small">Kembali ke Login</a></div>
    </div>
</div>
</body>
</html>
