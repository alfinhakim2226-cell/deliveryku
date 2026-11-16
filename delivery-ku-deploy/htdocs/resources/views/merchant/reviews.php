<h4 class="fw-bold mb-3">Penilaian & Ulasan Pelanggan (★ <?= e((string)$merchant['rating_avg']) ?>)</h4>
<div class="dk-card p-3">
    <?php if (empty($reviews)): ?><p class="text-muted mb-0">Belum ada ulasan pelanggan.</p><?php endif; ?>
    <?php foreach ($reviews as $r): ?>
        <div class="border-bottom pb-2 mb-2 small">
            <div class="d-flex justify-content-between"><strong><?= e($r['customer_name']) ?></strong><span class="text-warning fw-bold">★ <?= (int)$r['merchant_rating'] ?> / 5</span></div>
            <div class="text-muted">Order: <?= e($r['order_code']) ?> • <?= e(format_indo_date($r['created_at'])) ?></div>
            <p class="mb-0 mt-1"><?= e($r['comment']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
