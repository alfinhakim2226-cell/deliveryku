<h4 class="fw-bold mb-3">Toko Favorit Saya</h4>
<?php if (empty($favorites)): ?><div class="dk-card p-4 text-center text-muted">Belum ada toko favorit.</div><?php endif; ?>
<div class="row g-3">
<?php foreach ($favorites as $m): ?>
    <div class="col-md-6 col-lg-4">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-1"><?= e($m['store_name']) ?></h6>
            <small class="text-muted d-block mb-2"><?= e($m['district_name'] ?? 'Indramayu') ?> • ★ <?= e((string)$m['rating_avg']) ?></small>
            <div class="d-flex gap-2">
                <a href="<?= e(route_url('user/merchant', ['id' => $m['id']])) ?>" class="btn btn-dk-primary btn-sm flex-grow-1">Buka Toko</a>
                <form method="POST"><?= csrf_field() ?><input type="hidden" name="merchant_id" value="<?= (int)$m['id'] ?>"><button class="btn btn-outline-danger btn-sm">Hapus</button></form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
