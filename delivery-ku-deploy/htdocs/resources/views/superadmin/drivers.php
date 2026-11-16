<h4 class="fw-bold mb-3">Manajemen & Verifikasi Driver</h4>
<div class="dk-card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light"><tr><th>Kurir</th><th>Kendaraan</th><th>Wilayah</th><th>Online</th><th>Pengantaran</th><th>Verifikasi</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($drivers as $d): ?>
                <tr>
                    <td class="fw-semibold"><?= e($d['full_name']) ?><br><small class="text-muted"><?= e($d['phone']) ?></small></td>
                    <td><?= e($d['vehicle_type']) ?> - <?= e($d['vehicle_brand']) ?><br><strong><?= e($d['license_plate']) ?></strong></td>
                    <td><?= e($d['district_name'] ?? '-') ?></td>
                    <td><span class="badge text-bg-<?= $d['is_online'] ? 'success' : 'secondary' ?>"><?= $d['is_online'] ? 'Online' : 'Offline' ?></span></td>
                    <td><?= (int)$d['total_deliveries'] ?>x • ★ <?= e((string)$d['rating_avg']) ?></td>
                    <td><span class="badge text-bg-<?= $d['verification_status'] === 'approved' ? 'success' : ($d['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e($d['verification_status']) ?></span></td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="driver_id" value="<?= (int)$d['id'] ?>">
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
