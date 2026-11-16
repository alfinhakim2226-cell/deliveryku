<h4 class="fw-bold mb-3">Riwayat & Status Pesanan Saya</h4>
<?php if (empty($orders)): ?><div class="dk-card p-4 text-center text-muted">Belum ada pesanan.</div><?php endif; ?>
<div class="row g-3">
<?php foreach ($orders as $o): ?>
    <div class="col-md-6">
        <div class="dk-card p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div><strong class="text-primary"><?= e($o['order_code']) ?></strong><div class="small text-muted"><?= e($o['store_name']) ?></div></div>
                <?= order_status_badge($o['status']) ?>
            </div>
            <div class="small text-muted mb-2"><?= e(format_indo_date($o['created_at'])) ?> • Total: <strong class="text-dark"><?= e(rupiah($o['total_amount'])) ?></strong></div>
            <a href="<?= e(route_url('user/order-detail', ['id' => $o['id']])) ?>" class="btn btn-outline-primary btn-sm">Lacak & Detail Pesanan</a>
        </div>
    </div>
<?php endforeach; ?>
</div>
