<h4 class="fw-bold mb-3">Pengaduan & Bantuan Pelanggan</h4>
<div class="dk-card p-3">
    <?php if (empty($complaints)): ?><p class="text-muted mb-0">Belum ada pengaduan masuk.</p><?php endif; ?>
    <?php foreach ($complaints as $c): ?>
        <div class="border rounded p-3 mb-2 small">
            <div class="d-flex justify-content-between">
                <strong><?= e($c['ticket_code']) ?> — <?= e($c['subject']) ?></strong>
                <span class="badge text-bg-primary"><?= e($c['status']) ?></span>
            </div>
            <div class="text-muted mb-1">Dari: <?= e($c['full_name']) ?> (<?= e($c['phone']) ?>) • Order: <?= e($c['order_code'] ?? '-') ?></div>
            <p class="mb-2"><?= e($c['description']) ?></p>
            <form method="POST" class="row g-2">
                <?= csrf_field() ?>
                <input type="hidden" name="complaint_id" value="<?= (int)$c['id'] ?>">
                <div class="col-md-3"><select name="status" class="form-select form-select-sm"><option value="in_progress">Diproses</option><option value="resolved">Selesai</option><option value="closed">Ditutup</option></select></div>
                <div class="col-md-7"><input type="text" name="admin_response" value="<?= e($c['admin_response'] ?? '') ?>" class="form-control form-control-sm" placeholder="Tulis tanggapan resmi Admin..."></div>
                <div class="col-md-2"><button class="btn btn-dk-primary btn-sm w-100">Kirim</button></div>
            </form>
        </div>
    <?php endforeach; ?>
</div>
