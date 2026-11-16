<h4 class="fw-bold mb-3">Manajemen Kategori & Produk</h4>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="dk-card p-3">
            <h6 class="fw-bold">Tambah Kategori Produk</h6>
            <form method="POST" class="mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_category">
                <div class="mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nama kategori" required></div>
                <div class="mb-2"><input type="text" name="icon" class="form-control form-control-sm" value="bi-bag" placeholder="Ikon Bootstrap (bi-cup-hot)"></div>
                <button class="btn btn-dk-primary btn-sm w-100">Simpan Kategori</button>
            </form>
            <ul class="list-group list-group-flush small">
                <?php foreach ($categories as $c): ?>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span><i class="bi <?= e($c['icon']) ?> text-primary me-2"></i><?= e($c['name']) ?></span>
                        <span class="badge text-bg-light border"><?= e($c['slug']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="dk-card p-3">
            <h6 class="fw-bold">Daftar Produk Marketplace</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle small">
                    <thead class="table-light"><tr><th>Produk</th><th>Merchant</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($p['name']) ?><br><small class="text-muted"><?= e($p['category_name'] ?? '-') ?></small></td>
                            <td><?= e($p['store_name']) ?></td>
                            <td><?= e(rupiah($p['promo_price'] ?: $p['price'])) ?></td>
                            <td><?= (int)$p['stock'] ?></td>
                            <td><span class="badge text-bg-<?= $p['is_available'] ? 'success' : 'secondary' ?>"><?= $p['is_available'] ? 'Tersedia' : 'Nonaktif' ?></span></td>
                            <td>
                                <form method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_product">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="is_available" value="<?= $p['is_available'] ? 0 : 1 ?>">
                                    <button class="btn btn-outline-secondary btn-sm"><?= $p['is_available'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
