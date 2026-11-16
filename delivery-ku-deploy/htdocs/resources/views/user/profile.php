<h4 class="fw-bold mb-3">Profil & Notifikasi Pelanggan</h4>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="dk-card p-4 mb-3">
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label small">Nama Lengkap</label><input type="text" name="full_name" value="<?= e($user['full_name']) ?>" class="form-control form-control-sm" required></div>
                <div class="mb-3"><label class="form-label small">Email</label><input type="email" value="<?= e($user['email']) ?>" class="form-control form-control-sm" disabled></div>
                <div class="mb-3"><label class="form-label small">Nomor HP / WhatsApp</label><input type="text" name="phone" value="<?= e($user['phone']) ?>" class="form-control form-control-sm" required></div>
                <div class="mb-3"><label class="form-label small">Foto Profil</label><input type="file" name="avatar" accept="image/*" class="form-control form-control-sm"></div>
                <button class="btn btn-dk-primary btn-sm">Simpan Profil</button>
            </form>
            <hr>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= e(route_url('user/addresses')) ?>" class="btn btn-outline-primary btn-sm">Kelola Buku Alamat</a>
                <a href="<?= e(route_url('user/help')) ?>" class="btn btn-outline-secondary btn-sm">Pusat Bantuan & Pengaduan</a>
                <a href="<?= e(route_url('auth/logout')) ?>" class="btn btn-outline-danger btn-sm">Keluar (Logout)</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Notifikasi Terbaru</h6>
            <?php if (empty($notifications)): ?><p class="text-muted small mb-0">Belum ada notifikasi.</p><?php endif; ?>
            <?php foreach ($notifications as $n): ?>
                <div class="border-bottom pb-2 mb-2 small">
                    <div class="fw-semibold"><?= e($n['title']) ?></div>
                    <div class="text-muted"><?= e($n['message']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
