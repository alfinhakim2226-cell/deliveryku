<h4 class="fw-bold mb-3">Verifikasi Merchant, Driver & Bukti Pembayaran</h4>
<?php if (has_permission('verify_merchants')): ?>
<div class="dk-card p-3 mb-3">
    <h6 class="fw-bold mb-2">Verifikasi Merchant</h6>
    <div class="table-responsive"><table class="table table-sm small align-middle"><thead class="table-light"><tr><th>Toko</th><th>Pemilik</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($merchants as $m): ?>
            <tr>
                <td><strong><?= e($m['store_name']) ?></strong><br><?= e($m['address']) ?></td>
                <td><?= e($m['full_name']) ?> (<?= e($m['phone']) ?>)</td>
                <td><span class="badge text-bg-<?= $m['verification_status'] === 'approved' ? 'success' : 'warning' ?>"><?= e($m['verification_status']) ?></span></td>
                <td>
                    <form method="POST" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="type" value="merchant"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button name="status" value="approved" class="btn btn-success btn-sm">Setujui</button>
                        <button name="status" value="rejected" class="btn btn-danger btn-sm">Tolak</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
</div>
<?php endif; ?>

<?php if (has_permission('verify_drivers')): ?>
<div class="dk-card p-3 mb-3">
    <h6 class="fw-bold mb-2">Verifikasi Driver / Kurir</h6>
    <div class="table-responsive"><table class="table table-sm small align-middle"><thead class="table-light"><tr><th>Kurir</th><th>Kendaraan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($drivers as $d): ?>
            <tr>
                <td><?= e($d['full_name']) ?> (<?= e($d['phone']) ?>)</td>
                <td><?= e($d['vehicle_type']) ?> - <?= e($d['license_plate']) ?></td>
                <td><span class="badge text-bg-<?= $d['verification_status'] === 'approved' ? 'success' : 'warning' ?>"><?= e($d['verification_status']) ?></span></td>
                <td>
                    <form method="POST" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="type" value="driver"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                        <button name="status" value="approved" class="btn btn-success btn-sm">Setujui</button>
                        <button name="status" value="rejected" class="btn btn-danger btn-sm">Tolak</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
</div>
<?php endif; ?>

<?php if (has_permission('verify_payments')): ?>
<div class="dk-card p-3">
    <h6 class="fw-bold mb-2">Verifikasi Bukti Transfer Manual</h6>
    <div class="table-responsive"><table class="table table-sm small align-middle"><thead class="table-light"><tr><th>Order</th><th>Nominal</th><th>Bukti</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= e($p['order_code']) ?></td>
                <td><?= e(rupiah($p['amount'])) ?></td>
                <td><?= !empty($p['proof_image_path']) ? '<a href="' . e(upload_url($p['proof_image_path'])) . '" target="_blank" class="btn btn-outline-info btn-sm">Lihat</a>' : '-' ?></td>
                <td><span class="badge text-bg-<?= $p['status'] === 'verified' ? 'success' : 'warning' ?>"><?= e($p['status']) ?></span></td>
                <td><?php if ($p['status'] !== 'verified'): ?>
                    <form method="POST" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="type" value="payment"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button name="status" value="approved" class="btn btn-success btn-sm">Verifikasi</button>
                        <button name="status" value="rejected" class="btn btn-danger btn-sm">Tolak</button>
                    </form>
                <?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
</div>
<?php endif; ?>
