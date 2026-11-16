<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="fw-bold mb-0">Manajemen Pesanan & Pengiriman</h4>
    <form method="GET" class="d-flex gap-2">
        <input type="hidden" name="route" value="superadmin/orders">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <?php foreach (ORDER_STATUSES as $k => $meta): ?>
                <option value="<?= e($k) ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= e($meta['label']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="dk-card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light"><tr><th>Kode</th><th>Pelanggan</th><th>Merchant</th><th>Driver</th><th>Total</th><th>Status</th><th>Ubah Status / Tugaskan Driver</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="fw-bold"><?= e($o['order_code']) ?><br><small class="text-muted"><?= e(format_indo_date($o['created_at'])) ?></small></td>
                    <td><?= e($o['customer_name']) ?><br><small class="text-muted"><?= e($o['recipient_phone']) ?></small></td>
                    <td><?= e($o['store_name']) ?></td>
                    <td><?= e($o['driver_name'] ?? 'Belum ditugaskan') ?></td>
                    <td><?= e(rupiah($o['total_amount'])) ?><br><small class="text-muted"><?= e($o['payment_method']) ?> (<?= e($o['payment_status'] ?? '-') ?>)</small></td>
                    <td><?= order_status_badge($o['status']) ?></td>
                    <td>
                        <?php $nexts = ORDER_STATUS_TRANSITIONS[$o['status']] ?? []; if (!empty($nexts)): ?>
                            <form method="POST" class="d-flex flex-wrap gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                <select name="to_status" class="form-select form-select-sm" style="width:165px">
                                    <?php foreach ($nexts as $nx): ?>
                                        <option value="<?= e($nx) ?>"><?= e(order_status_label($nx)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($o['driver_id'])): ?>
                                    <select name="driver_id" class="form-select form-select-sm" style="width:140px">
                                        <option value="">- Driver -</option>
                                        <?php foreach ($activeDrivers as $ad): ?>
                                            <option value="<?= (int)$ad['id'] ?>"><?= e($ad['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                                <button class="btn btn-dk-primary btn-sm">Proses</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Final</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
