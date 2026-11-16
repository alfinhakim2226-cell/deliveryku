<h4 class="fw-bold mb-3">Cari Makanan & Toko UMKM</h4>
<div class="dk-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <input type="hidden" name="route" value="user/search">
        <div class="col-md-4"><input type="text" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Nama makanan atau toko..."></div>
        <div class="col-md-3"><select name="category_id" class="form-select form-select-sm"><option value="0">Semua Kategori</option><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select name="district_id" class="form-select form-select-sm"><option value="0">Semua Kecamatan</option><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>" <?= $distId === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-dk-primary btn-sm w-100">Cari</button></div>
    </form>
</div>

<div class="row g-3">
    <?php if (empty($products)): ?><div class="col-12"><div class="dk-card p-4 text-center text-muted">Tidak ditemukan produk yang cocok.</div></div><?php endif; ?>
    <?php foreach ($products as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="dk-card h-100 d-flex flex-column">
                <img src="<?= e(upload_url($p['primary_image'])) ?>" alt="<?= e($p['name']) ?>" class="product-card-img w-100">
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <div class="fw-bold small"><?= e($p['name']) ?></div>
                    <small class="text-muted mb-2"><?= e($p['store_name']) ?> • <?= e($p['district_name'] ?? '') ?></small>
                    <div class="fw-bold text-primary mb-2"><?= e(rupiah($p['promo_price'] ?: $p['price'])) ?></div>
                    <a href="<?= e(route_url('user/merchant', ['id' => $p['merchant_id']])) ?>" class="btn btn-dk-primary btn-sm mt-auto">Lihat Toko</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
