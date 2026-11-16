<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-0">Laporan Keuangan & Transaksi</h4>
        <small class="text-muted">Data transaksi demo (<code>is_demo = 1</code>) dipisahkan otomatis dari laporan produksi (<code>is_demo = 0</code>).</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <div class="btn-group btn-group-sm">
            <a href="<?= e(route_url('superadmin/reports', ['scope' => 'production'])) ?>" class="btn btn-<?= $scope === 'production' ? 'primary' : 'outline-primary' ?>">Produksi Nyata</a>
            <a href="<?= e(route_url('superadmin/reports', ['scope' => 'demo'])) ?>" class="btn btn-<?= $scope === 'demo' ? 'warning' : 'outline-warning' ?>">Data Demo Uji</a>
            <a href="<?= e(route_url('superadmin/reports', ['scope' => 'all'])) ?>" class="btn btn-<?= $scope === 'all' ? 'dark' : 'outline-dark' ?>">Semua</a>
        </div>
        <a href="<?= e(route_url('superadmin/reports', ['scope' => $scope, 'export' => 'csv'])) ?>" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (CSV)</a>
        <a href="<?= e(route_url('superadmin/reports', ['scope' => $scope, 'export' => 'pdf'])) ?>" target="_blank" class="btn btn-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Cetak / PDF</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="dk-card p-3"><small class="text-muted">Total Pesanan</small><div class="fs-5 fw-bold"><?= (int)$summary['total_orders'] ?></div></div></div>
    <div class="col-md-3"><div class="dk-card p-3"><small class="text-muted">GMV Selesai</small><div class="fs-5 fw-bold text-primary"><?= e(rupiah($summary['gross_gmv'])) ?></div></div></div>
    <div class="col-md-3"><div class="dk-card p-3"><small class="text-muted">Pendapatan Platform</small><div class="fs-5 fw-bold text-success"><?= e(rupiah($summary['platform_income'])) ?></div></div></div>
    <div class="col-md-3"><div class="dk-card p-3"><small class="text-muted">Hak Merchant / Kurir</small><div class="fs-6 fw-bold"><?= e(rupiah($summary['merchant_income'])) ?> / <?= e(rupiah($summary['driver_income'])) ?></div></div></div>
</div>

<div class="dk-card p-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle small">
            <thead class="table-light"><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Merchant</th><th>Subtotal</th><th>Ongkir</th><th>Layanan</th><th>Komisi</th><th>Total</th><th>Status</th><th>Tipe</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="11" class="text-center text-muted py-4">Belum ada transaksi pada cakupan "<?= e($scope) ?>".</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td class="fw-semibold"><?= e($o['order_code']) ?></td>
                    <td><?= e(format_indo_date($o['created_at'])) ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= e($o['store_name']) ?></td>
                    <td><?= e(rupiah($o['subtotal'])) ?></td>
                    <td><?= e(rupiah($o['delivery_fee'])) ?></td>
                    <td><?= e(rupiah($o['service_fee'])) ?></td>
                    <td><?= e(rupiah($o['platform_commission'])) ?></td>
                    <td class="fw-bold"><?= e(rupiah($o['total_amount'])) ?></td>
                    <td><?= order_status_badge($o['status']) ?></td>
                    <td><?= (int)$o['is_demo'] === 1 ? '<span class="badge text-bg-warning">Demo</span>' : '<span class="badge text-bg-success">Produksi</span>' ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
