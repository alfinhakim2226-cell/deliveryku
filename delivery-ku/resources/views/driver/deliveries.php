<h4 class="fw-bold mb-3">Riwayat Pengantaran & Penghasilan Kurir</h4>
<div class="dk-card p-3 mb-3">
    <div class="row g-3">
        <div class="col-md-4"><small class="text-muted">Total Penghasilan Selesai</small><div class="fs-4 fw-bold text-success"><?= e(rupiah($totalEarnings)) ?></div></div>
        <div class="col-md-4"><small class="text-muted">Total Pengantaran</small><div class="fs-4 fw-bold"><?= (int)$driver['total_deliveries'] ?>x</div></div>
        <div class="col-md-4"><small class="text-muted">Rating Rata-rata</small><div class="fs-4 fw-bold text-warning">★ <?= e((string)$driver['rating_avg']) ?></div></div>
    </div>
</div>
<div class="dk-card p-3">
    <div class="table-responsive"><table class="table table-sm small align-middle"><thead class="table-light"><tr><th>Kode</th><th>Toko</th><th>Pelanggan</th><th>Upah Kurir</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>
        <?php foreach ($history as $h): ?>
            <tr><td><?= e($h['order_code']) ?></td><td><?= e($h['store_name']) ?></td><td><?= e($h['customer_name']) ?></td><td class="text-success fw-semibold"><?= e(rupiah($h['driver_net_income'])) ?></td><td><?= order_status_badge($h['status']) ?></td><td><?= e(format_indo_date($h['created_at'])) ?></td></tr>
        <?php endforeach; ?>
    </tbody></table></div>
</div>
