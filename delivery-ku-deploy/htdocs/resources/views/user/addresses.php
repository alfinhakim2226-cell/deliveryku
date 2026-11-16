<h4 class="fw-bold mb-3">Buku Alamat Pengiriman</h4>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Tambah Alamat Baru</h6>
            <form method="POST">
                <?= csrf_field() ?><input type="hidden" name="action" value="create">
                <div class="mb-2"><label class="form-label small">Label (Rumah / Kantor)</label><input type="text" name="label" class="form-control form-control-sm" required></div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label small">Penerima</label><input type="text" name="recipient_name" value="<?= e(current_user()['full_name']) ?>" class="form-control form-control-sm" required></div>
                    <div class="col"><label class="form-label small">No. HP</label><input type="text" name="recipient_phone" value="<?= e(current_user()['phone']) ?>" class="form-control form-control-sm" required></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-8"><label class="form-label small">Kecamatan (Indramayu)</label><select name="district_id" class="form-select form-select-sm"><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-4"><label class="form-label small">Kode Pos</label><input type="text" name="postal_code" class="form-control form-control-sm"></div>
                </div>
                <div class="mb-2"><label class="form-label small">Alamat Lengkap</label><textarea name="full_address" class="form-control form-control-sm" rows="2" required></textarea></div>
                <div class="mb-2"><label class="form-label small">Patokan</label><input type="text" name="notes" class="form-control form-control-sm"></div>
                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_default" id="addr_def"><label class="form-check-label small" for="addr_def">Jadikan alamat utama</label></div>
                <button class="btn btn-dk-primary btn-sm w-100">Simpan Alamat</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <?php foreach ($addresses as $a): ?>
            <div class="dk-card p-3 mb-2 small">
                <div class="d-flex justify-content-between">
                    <strong><?= e($a['label']) ?> <?= $a['is_default'] ? '<span class="badge text-bg-primary">Utama</span>' : '' ?></strong>
                    <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="address_id" value="<?= (int)$a['id'] ?>"><button class="btn btn-outline-danger btn-sm">Hapus</button></form>
                </div>
                <div><?= e($a['recipient_name']) ?> (<?= e($a['recipient_phone']) ?>)</div>
                <div class="text-muted"><?= e($a['full_address']) ?> — <?= e($a['district_name'] ?? '') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
