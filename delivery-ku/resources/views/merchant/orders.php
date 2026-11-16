<h4 class="fw-bold mb-3">Pesanan Masuk & Riwayat Toko</h4>
<?php if (empty($orders)): ?><div class="dk-card p-4 text-center text-muted">Belum ada pesanan masuk untuk toko Anda.</div><?php endif; ?>
<div class="row g-3">
<?php foreach ($orders as $o): ?>
    <div class="col-lg-6">
        <div class="dk-card p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="fw-bold text-primary"><?= e($o['order_code']) ?></div>
                    <small class="text-muted"><?= e($o['customer_name']) ?> • <?= e($o['customer_phone']) ?></small>
                </div>
                <?= order_status_badge($o['status']) ?>
            </div>
            <ul class="list-unstyled small bg-light rounded p-2 mb-2">
                <?php foreach ($o['items'] as $it): ?>
                    <li class="d-flex justify-content-between">
                        <span><?= (int)$it['quantity'] ?>x <?= e($it['product_name']) ?> <?= $it['variant_name'] ? '(' . e($it['variant_name']) . ')' : '' ?></span>
                        <span><?= e(rupiah($it['subtotal'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (!empty($o['customer_notes'])): ?><div class="small text-muted mb-2"><i class="bi bi-chat-dots me-1"></i>Catatan: <?= e($o['customer_notes']) ?></div><?php endif; ?>
            <div class="d-flex justify-content-between align-items-center small mb-2">
                <span>Pendapatan Bersih Toko: <strong class="text-success"><?= e(rupiah($o['merchant_net_income'])) ?></strong></span>
                <span class="text-muted"><?= e(PAYMENT_METHODS[$o['payment_method']] ?? $o['payment_method']) ?></span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <?php if ($o['status'] === 'menunggu_konfirmasi_merchant'): ?>
                    <form method="POST">
                        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="to_status" value="pesanan_dikonfirmasi">
                        <button class="btn btn-dk-green btn-sm"><i class="bi bi-check-lg me-1"></i>Konfirmasi Pesanan</button>
                    </form>
                    <form method="POST" class="d-flex gap-1" data-confirm="Tolak pesanan ini?">
                        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="to_status" value="dibatalkan">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Alasan penolakan..." required style="width:160px">
                        <button class="btn btn-outline-danger btn-sm">Tolak</button>
                    </form>
                <?php elseif ($o['status'] === 'pesanan_dikonfirmasi'): ?>
                    <form method="POST">
                        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="to_status" value="sedang_disiapkan">
                        <button class="btn btn-primary btn-sm">Mulai Siapkan Pesanan</button>
                    </form>
                <?php elseif ($o['status'] === 'sedang_disiapkan'): ?>
                    <form method="POST">
                        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="to_status" value="menunggu_driver">
                        <button class="btn btn-dk-primary btn-sm"><i class="bi bi-box-seam me-1"></i>Pesanan Siap Diambil Kurir</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
