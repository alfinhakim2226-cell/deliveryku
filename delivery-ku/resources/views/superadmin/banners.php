<h4 class="fw-bold mb-3">Banner Promosi & Moderasi Ulasan</h4>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="dk-card p-3 mb-3">
            <h6 class="fw-bold mb-2">Tambah Banner Promosi</h6>
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_banner">
                <div class="mb-2"><label class="form-label small">Judul Banner</label><input type="text" name="title" class="form-control form-control-sm" required></div>
                <div class="mb-2"><label class="form-label small">Subjudul</label><input type="text" name="subtitle" class="form-control form-control-sm"></div>
                <div class="mb-2"><label class="form-label small">Teks Badge</label><input type="text" name="badge_text" value="PROMO INDRAMAYU" class="form-control form-control-sm"></div>
                <div class="mb-2"><label class="form-label small">Gambar Banner (Opsional)</label><input type="file" name="image" accept="image/*" class="form-control form-control-sm"></div>
                <button class="btn btn-dk-primary btn-sm w-100">Simpan Banner</button>
            </form>
        </div>
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Banner Aktif</h6>
            <?php foreach ($banners as $b): ?>
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center small">
                    <div>
                        <div class="fw-bold"><?= e($b['title']) ?></div>
                        <div class="text-muted"><?= e($b['subtitle']) ?></div>
                    </div>
                    <form method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_banner">
                        <input type="hidden" name="banner_id" value="<?= (int)$b['id'] ?>">
                        <button class="btn btn-outline-<?= $b['is_active'] ? 'success' : 'secondary' ?> btn-sm"><?= $b['is_active'] ? 'Aktif' : 'Nonaktif' ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Ulasan & Rating Pelanggan</h6>
            <div class="table-responsive">
                <table class="table table-sm small align-middle">
                    <thead class="table-light"><tr><th>Order</th><th>Pelanggan</th><th>Merchant</th><th>Rating</th><th>Komentar</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><?= e($r['order_code']) ?></td>
                            <td><?= e($r['customer_name']) ?></td>
                            <td><?= e($r['store_name']) ?></td>
                            <td>★ <?= (int)$r['merchant_rating'] ?> / Kurir ★ <?= (int)$r['driver_rating'] ?></td>
                            <td><?= e($r['comment']) ?></td>
                            <td>
                                <form method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_review">
                                    <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                                    <button class="btn btn-outline-secondary btn-sm"><?= $r['is_visible'] ? 'Sembunyikan' : 'Tampilkan' ?></button>
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
