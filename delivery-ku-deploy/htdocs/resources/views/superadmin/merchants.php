<h4 class="fw-bold mb-3">Manajemen & Verifikasi Merchant</h4>
<div class="dk-card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light"><tr><th>Toko</th><th>Pemilik</th><th>Wilayah</th><th>Kategori</th><th>Komisi Khusus</th><th>Verifikasi</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($merchants as $m): ?>
                <tr>
                    <td class="fw-semibold"><?= e($m['store_name']) ?><br><small class="text-muted"><?= e($m['address']) ?></small></td>
                    <td><?= e($m['owner_name']) ?><br><small><?= e($m['phone']) ?></small></td>
                    <td><?= e($m['district_name'] ?? '-') ?></td>
                    <td><?= e($m['category_name'] ?? '-') ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-1 align-items-center">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="commission">
                            <input type="hidden" name="merchant_id" value="<?= (int)$m['id'] ?>">
                            <input type="number" step="0.5" name="commission_rate" value="<?= e((string)($m['commission_rate'] ?? '')) ?>" placeholder="Default" class="form-control form-control-sm" style="width:80px">
                            <div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="is_recommended" <?= $m['is_recommended'] ? 'checked' : '' ?> title="Rekomendasi"></div>
                            <button class="btn btn-outline-primary btn-sm">Simpan</button>
                        </form>
                    </td>
                    <td><span class="badge text-bg-<?= $m['verification_status'] === 'approved' ? 'success' : ($m['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e($m['verification_status']) ?></span></td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="verify">
                            <input type="hidden" name="merchant_id" value="<?= (int)$m['id'] ?>">
                            <button name="status" value="approved" class="btn btn-success btn-sm">Setujui</button>
                            <button name="status" value="rejected" class="btn btn-danger btn-sm">Tolak</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
