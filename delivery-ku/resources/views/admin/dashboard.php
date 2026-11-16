<h4 class="fw-bold mb-3">Dashboard Admin Operasional</h4>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg"><div class="dk-card p-3"><small class="text-muted">Merchant Menunggu</small><div class="fs-4 fw-bold text-warning"><?= (int)$summary['pending_merchants'] ?></div></div></div>
    <div class="col-6 col-md-4 col-lg"><div class="dk-card p-3"><small class="text-muted">Driver Menunggu</small><div class="fs-4 fw-bold text-info"><?= (int)$summary['pending_drivers'] ?></div></div></div>
    <div class="col-6 col-md-4 col-lg"><div class="dk-card p-3"><small class="text-muted">Bukti Bayar Pending</small><div class="fs-4 fw-bold text-primary"><?= (int)$summary['pending_payments'] ?></div></div></div>
    <div class="col-6 col-md-6 col-lg"><div class="dk-card p-3"><small class="text-muted">Pengaduan Aktif</small><div class="fs-4 fw-bold text-danger"><?= (int)$summary['open_complaints'] ?></div></div></div>
    <div class="col-12 col-md-6 col-lg"><div class="dk-card p-3"><small class="text-muted">Pesanan Produksi Aktif</small><div class="fs-4 fw-bold text-success"><?= (int)$summary['active_orders'] ?></div></div></div>
</div>

<div class="dk-card p-3">
    <h6 class="fw-bold mb-2">Pemantauan Pesanan Terbaru</h6>
    <div class="table-responsive">
        <table class="table table-sm small align-middle mb-0">
            <thead class="table-light"><tr><th>Kode</th><th>Pelanggan</th><th>Merchant</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td class="fw-semibold"><?= e($o['order_code']) ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= e($o['store_name']) ?></td>
                    <td><?= e(rupiah($o['total_amount'])) ?></td>
                    <td><?= order_status_badge($o['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
