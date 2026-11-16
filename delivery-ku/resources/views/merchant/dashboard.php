<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-0"><?= e($merchant['store_name']) ?></h4>
        <small class="text-muted">Status Verifikasi: <span class="badge text-bg-<?= $merchant['verification_status'] === 'approved' ? 'success' : 'warning' ?>"><?= e($merchant['verification_status']) ?></span> • Jam: <?= e(substr($merchant['open_time'], 0, 5)) ?> - <?= e(substr($merchant['close_time'], 0, 5)) ?> WIB</small>
    </div>
    <form method="POST" action="<?= e(route_url('merchant/profile')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="toggle_open">
        <button class="btn btn-<?= $merchant['is_open'] ? 'success' : 'secondary' ?> btn-sm">
            <i class="bi bi-power me-1"></i> Toko <?= $merchant['is_open'] ? 'BUKA (Klik untuk Tutup)' : 'TUTUP (Klik untuk Buka)' ?>
        </button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="dk-card p-3"><small class="text-muted">Pesanan Masuk</small><div class="fs-4 fw-bold text-warning"><?= (int)$stats['incoming'] ?></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="dk-card p-3"><small class="text-muted">Sedang Disiapkan</small><div class="fs-4 fw-bold text-primary"><?= (int)$stats['preparing'] ?></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="dk-card p-3"><small class="text-muted">Selesai</small><div class="fs-4 fw-bold text-success"><?= (int)$stats['completed'] ?></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="dk-card p-3"><small class="text-muted">Total Omzet</small><div class="fs-6 fw-bold"><?= e(rupiah($stats['gross_sales'])) ?></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="dk-card p-3"><small class="text-muted">Komisi Platform</small><div class="fs-6 fw-bold text-danger"><?= e(rupiah($stats['platform_comm'])) ?></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="dk-card p-3"><small class="text-muted">Pendapatan Bersih</small><div class="fs-6 fw-bold text-success"><?= e(rupiah($stats['net_income'])) ?></div></div></div>
</div>

<div class="dk-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Pesanan Terbaru</h6>
        <a href="<?= e(route_url('merchant/orders')) ?>" class="btn btn-dk-primary btn-sm">Kelola Semua Pesanan</a>
    </div>
    <div class="table-responsive"><table class="table table-sm small align-middle mb-0"><thead class="table-light"><tr><th>Kode</th><th>Pelanggan</th><th>Subtotal</th><th>Pendapatan Bersih</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td class="fw-semibold"><?= e($o['order_code']) ?></td>
                <td><?= e($o['customer_name']) ?></td>
                <td><?= e(rupiah($o['subtotal'])) ?></td>
                <td class="text-success fw-semibold"><?= e(rupiah($o['merchant_net_income'])) ?></td>
                <td><?= order_status_badge($o['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
</div>
