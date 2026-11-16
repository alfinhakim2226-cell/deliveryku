<div class="dk-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
            <span class="badge text-bg-<?= $merchant['is_open'] ? 'success' : 'secondary' ?> mb-1"><?= $merchant['is_open'] ? 'Sedang Buka' : 'Tutup' ?></span>
            <h3 class="fw-bold mb-1"><?= e($merchant['store_name']) ?></h3>
            <p class="text-muted small mb-1"><?= e($merchant['description']) ?></p>
            <div class="small"><i class="bi bi-geo-alt text-primary"></i> <?= e($merchant['address']) ?> (<?= e($merchant['district_name'] ?? 'Indramayu') ?>) • ★ <?= e((string)$merchant['rating_avg']) ?></div>
        </div>
        <?php if (is_logged_in() && user_role() === 'user'): ?>
            <form method="POST" action="<?= e(route_url('user/favorites')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="merchant_id" value="<?= (int)$merchant['id'] ?>">
                <input type="hidden" name="redirect" value="user/merchant">
                <button class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-heart<?= $isFavorited ? '-fill' : '' ?> me-1"></i><?= $isFavorited ? 'Disimpan di Favorit' : 'Simpan Favorit' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<h5 class="fw-bold mb-3">Daftar Menu & Produk</h5>
<div class="row g-3 mb-4">
    <?php foreach ($products as $p): ?>
        <div class="col-md-6 col-lg-4">
            <div class="dk-card p-3 h-100 d-flex flex-column">
                <div class="d-flex gap-3 mb-2">
                    <img src="<?= e(upload_url($p['primary_image'])) ?>" alt="<?= e($p['name']) ?>" class="rounded-3" style="width:86px;height:86px;object-fit:cover;">
                    <div>
                        <h6 class="fw-bold mb-1"><?= e($p['name']) ?></h6>
                        <div class="small text-muted mb-1"><?= e($p['description']) ?></div>
                        <div class="fw-bold text-primary">
                            <?= e(rupiah($p['promo_price'] ?: $p['price'])) ?>
                            <?php if (!empty($p['promo_price'])): ?><small class="text-muted text-decoration-line-through ms-1"><?= e(rupiah($p['price'])) ?></small><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if (is_logged_in() && user_role() === 'user' && $merchant['is_open'] && $p['is_available']): ?>
                    <form method="POST" action="<?= e(route_url('user/cart')) ?>" class="mt-auto pt-2 border-top">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="merchant_id" value="<?= (int)$merchant['id'] ?>">
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <?php if (!empty($p['variants'])): ?>
                            <select name="variant_id" class="form-select form-select-sm mb-2">
                                <?php foreach ($p['variants'] as $v): ?>
                                    <option value="<?= (int)$v['id'] ?>"><?= e($v['variant_name']) ?> (+<?= e(rupiah($v['additional_price'])) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div class="input-group input-group-sm mb-2">
                            <input type="number" name="quantity" value="1" min="1" max="<?= (int)$p['stock'] ?>" class="form-control" style="max-width:70px;">
                            <input type="text" name="notes" class="form-control" placeholder="Catatan (opsional)">
                        </div>
                        <button type="submit" class="btn btn-dk-primary btn-sm w-100"><i class="bi bi-cart-plus me-1"></i>Tambah ke Keranjang</button>
                    </form>
                <?php elseif (!is_logged_in()): ?>
                    <a href="<?= e(route_url('auth/login')) ?>" class="btn btn-outline-primary btn-sm mt-auto">Masuk untuk Memesan</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="dk-card p-3">
    <h6 class="fw-bold mb-2">Ulasan Pelanggan</h6>
    <?php if (empty($reviews)): ?><p class="text-muted small mb-0">Belum ada ulasan.</p><?php endif; ?>
    <?php foreach ($reviews as $r): ?>
        <div class="border-bottom pb-2 mb-2 small"><strong><?= e($r['full_name']) ?></strong> • <span class="text-warning">★ <?= (int)$r['merchant_rating'] ?></span><div><?= e($r['comment']) ?></div></div>
    <?php endforeach; ?>
</div>
