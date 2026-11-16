<h4 class="fw-bold mb-3">Keranjang Belanja</h4>
<?php if (empty($carts)): ?>
    <div class="dk-card p-4 text-center">
        <i class="bi bi-cart-x fs-1 text-muted"></i>
        <p class="text-muted mt-2 mb-3">Keranjang belanja Anda masih kosong.</p>
        <a href="<?= e(route_url('user/home')) ?>" class="btn btn-dk-primary btn-sm">Mulai Belanja di Indramayu</a>
    </div>
<?php else: foreach ($carts as $cart): $sub = 0; ?>
    <div class="dk-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <div><h6 class="fw-bold mb-0"><i class="bi bi-shop text-primary me-1"></i><?= e($cart['store_name']) ?></h6><small class="text-muted"><?= e($cart['merchant_address']) ?></small></div>
            <a href="<?= e(route_url('user/merchant', ['id' => $cart['merchant_id']])) ?>" class="btn btn-outline-primary btn-sm">+ Tambah Menu</a>
        </div>
        <?php foreach ($cart['items'] as $it):
            $unit = ($it['promo_price'] ?: $it['price']) + $it['additional_price'];
            $line = $unit * $it['quantity'];
            $sub += $line;
        ?>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                <div>
                    <div class="fw-semibold"><?= e($it['name']) ?></div>
                    <?php if (!empty($it['variant_name'])): ?><div class="text-muted">Variasi: <?= e($it['variant_name']) ?></div><?php endif; ?>
                    <?php if (!empty($it['notes'])): ?><div class="text-muted">Catatan: <?= e($it['notes']) ?></div><?php endif; ?>
                    <div class="text-primary fw-semibold"><?= e(rupiah($unit)) ?></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="POST" class="d-flex align-items-center gap-1">
                        <?= csrf_field() ?><input type="hidden" name="action" value="update_qty"><input type="hidden" name="cart_item_id" value="<?= (int)$it['id'] ?>">
                        <input type="number" name="quantity" value="<?= (int)$it['quantity'] ?>" min="0" class="form-control form-control-sm" style="width:62px">
                        <button class="btn btn-outline-secondary btn-sm">Ubah</button>
                    </form>
                    <form method="POST">
                        <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="cart_item_id" value="<?= (int)$it['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between align-items-center pt-3">
            <div>Subtotal Produk: <strong class="fs-5 text-primary"><?= e(rupiah($sub)) ?></strong></div>
            <a href="<?= e(route_url('user/checkout', ['merchant_id' => $cart['merchant_id']])) ?>" class="btn btn-dk-green fw-semibold">Lanjut ke Checkout <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
<?php endforeach; endif; ?>
