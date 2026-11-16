<h4 class="fw-bold mb-3">Pusat Bantuan & Pengaduan Pelanggan</h4>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Kirim Pengaduan / Bantuan</h6>
            <form method="POST">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label small">Terkait Pesanan (Opsional)</label><select name="order_id" class="form-select form-select-sm"><option value="">- Umum -</option><?php foreach ($orders as $o): ?><option value="<?= (int)$o['id'] ?>"><?= e($o['order_code']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label small">Subjek</label><input type="text" name="subject" class="form-control form-control-sm" required></div>
                <div class="mb-3"><label class="form-label small">Detail Kendala</label><textarea name="description" class="form-control form-control-sm" rows="3" required></textarea></div>
                <button class="btn btn-dk-primary btn-sm w-100">Kirim Tiket Bantuan</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Riwayat Tiket Pengaduan</h6>
            <?php if (empty($complaints)): ?><p class="text-muted small mb-0">Belum ada pengaduan.</p><?php endif; ?>
            <?php foreach ($complaints as $c): ?>
                <div class="border rounded p-2 mb-2 small">
                    <div class="d-flex justify-content-between"><strong><?= e($c['ticket_code']) ?> — <?= e($c['subject']) ?></strong><span class="badge text-bg-info"><?= e($c['status']) ?></span></div>
                    <p class="mb-1"><?= e($c['description']) ?></p>
                    <?php if (!empty($c['admin_response'])): ?><div class="bg-light p-2 rounded text-success"><strong>Balasan Admin:</strong> <?= e($c['admin_response']) ?></div><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
