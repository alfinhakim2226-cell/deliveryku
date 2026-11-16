<h4 class="fw-bold mb-3">Laporan Operasional (Produksi)</h4>
<div class="dk-card p-3">
    <div class="table-responsive"><table class="table table-sm small align-middle"><thead class="table-light"><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Merchant</th><th>Total</th><th>Status</th></tr></thead><tbody>
        <?php if (empty($orders)): ?><tr><td colspan="6" class="text-center text-muted py-3">Belum ada transaksi produksi.</td></tr><?php else: foreach ($orders as $o): ?>
            <tr><td><?= e($o['order_code']) ?></td><td><?= e(format_indo_date($o['created_at'])) ?></td><td><?= e($o['customer_name']) ?></td><td><?= e($o['store_name']) ?></td><td><?= e(rupiah($o['total_amount'])) ?></td><td><?= order_status_badge($o['status']) ?></td></tr>
        <?php endforeach; endif; ?>
    </tbody></table></div>
</div>
