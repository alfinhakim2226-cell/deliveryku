<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Kelola Menu & Produk</h4>
    <button class="btn btn-dk-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProd"><i class="bi bi-plus-lg me-1"></i>Tambah Produk</button>
</div>

<div class="row g-3">
    <?php foreach ($products as $p): ?>
        <div class="col-md-6 col-lg-4">
            <div class="dk-card h-100 d-flex flex-column">
                <img src="<?= e(upload_url($p['primary_image'])) ?>" alt="<?= e($p['name']) ?>" class="product-card-img w-100">
                <div class="p-3 flex-grow-1 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="fw-bold mb-1"><?= e($p['name']) ?></h6>
                        <span class="badge text-bg-<?= $p['is_available'] ? 'success' : 'secondary' ?>"><?= $p['is_available'] ? 'Tersedia' : 'Habis' ?></span>
                    </div>
                    <small class="text-muted mb-2"><?= e($p['category_name'] ?? '-') ?> • Stok: <?= (int)$p['stock'] ?></small>
                    <div class="mb-3">
                        <?php if (!empty($p['promo_price'])): ?>
                            <span class="fw-bold text-primary"><?= e(rupiah($p['promo_price'])) ?></span>
                            <small class="text-decoration-line-through text-muted ms-1"><?= e(rupiah($p['price'])) ?></small>
                        <?php else: ?>
                            <span class="fw-bold text-primary"><?= e(rupiah($p['price'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-auto d-flex gap-1">
                        <form method="POST" class="flex-grow-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <button class="btn btn-outline-secondary btn-sm w-100"><?= $p['is_available'] ? 'Tandai Habis' : 'Aktifkan' ?></button>
                        </form>
                        <form method="POST" data-confirm="Hapus produk ini?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="modalProd" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <div class="modal-header"><h6 class="modal-title fw-bold">Tambah Produk Baru</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2"><label class="form-label small">Nama Produk</label><input type="text" name="name" class="form-control form-control-sm" required></div>
                <div class="mb-2"><label class="form-label small">Kategori Menu</label><select name="category_id" class="form-select form-select-sm"><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label small">Harga Normal (Rp)</label><input type="number" name="price" class="form-control form-control-sm" required></div>
                    <div class="col"><label class="form-label small">Harga Promo (Opsional)</label><input type="number" name="promo_price" class="form-control form-control-sm"></div>
                    <div class="col"><label class="form-label small">Stok</label><input type="number" name="stock" value="50" class="form-control form-control-sm"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-7"><label class="form-label small">Variasi Awal (Opsional)</label><input type="text" name="variant_name" class="form-control form-control-sm" placeholder="Contoh: Level Pedas"></div>
                    <div class="col-5"><label class="form-label small">Tambahan Harga</label><input type="number" name="variant_price" value="0" class="form-control form-control-sm"></div>
                </div>
                <div class="mb-2"><label class="form-label small">Deskripsi</label><textarea name="description" class="form-control form-control-sm" rows="2"></textarea></div>
                <div class="mb-2"><label class="form-label small">Foto Produk</label><input type="file" name="image" accept="image/*" class="form-control form-control-sm"></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_available" checked id="p_avail"><label class="form-check-label small" for="p_avail">Produk langsung tersedia</label></div>
            </div>
            <div class="modal-footer"><button class="btn btn-dk-primary">Simpan Produk</button></div>
        </form>
    </div></div>
</div>
