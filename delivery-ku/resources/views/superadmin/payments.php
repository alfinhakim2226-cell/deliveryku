<h4 class="fw-bold mb-3">Manajemen Pembayaran & Komisi</h4>
<div class="dk-card p-3 mb-4">
    <h6 class="fw-bold mb-3">Verifikasi Pembayaran & Bukti Transfer Manual</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle small">
            <thead class="table-light"><tr><th>Kode Bayar</th><th>Pesanan</th><th>Pelanggan</th><th>Metode</th><th>Nominal</th><th>Bukti Transfer</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['payment_code']) ?></td>
                    <td><?= e($p['order_code']) ?></td>
                    <td><?= e($p['customer_name']) ?></td>
                    <td><?= e(PAYMENT_METHODS[$p['method']] ?? $p['method']) ?></td>
                    <td><?= e(rupiah($p['amount'])) ?></td>
                    <td>
                        <?php if (!empty($p['proof_image_path'])): ?>
                            <a href="<?= e(upload_url($p['proof_image_path'])) ?>" target="_blank" class="btn btn-outline-info btn-sm">Lihat Bukti</a>
                            <div class="text-muted"><?= e($p['sender_bank']) ?> - <?= e($p['sender_name']) ?></div>
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td><span class="badge text-bg-<?= $p['status'] === 'verified' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e($p['status']) ?></span></td>
                    <td>
                        <?php if ($p['status'] !== 'verified'): ?>
                            <form method="POST" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="payment_id" value="<?= (int)$p['id'] ?>">
                                <button name="decision" value="verified" class="btn btn-success btn-sm">Verifikasi</button>
                                <button name="decision" value="rejected" class="btn btn-danger btn-sm">Tolak</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dk-card p-3">
    <h6 class="fw-bold mb-3">Rincian Komisi Platform, Merchant & Kurir</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle small">
            <thead class="table-light"><tr><th>Pesanan</th><th>Merchant</th><th>Subtotal</th><th>Komisi %</th><th>Komisi Platform</th><th>Biaya Layanan</th><th>Bersih Merchant</th><th>Bersih Driver</th></tr></thead>
            <tbody>
            <?php foreach ($commissions as $c): ?>
                <tr>
                    <td><?= e($c['order_code']) ?> <?= (int)$c['is_demo'] === 1 ? '<span class="badge text-bg-warning">Demo</span>' : '' ?></td>
                    <td><?= e($c['store_name']) ?></td>
                    <td><?= e(rupiah($c['gross_subtotal'])) ?></td>
                    <td><?= e((string)$c['commission_rate']) ?>%</td>
                    <td class="text-primary fw-semibold"><?= e(rupiah($c['platform_commission_amount'])) ?></td>
                    <td><?= e(rupiah($c['service_fee_amount'])) ?></td>
                    <td class="text-success fw-semibold"><?= e(rupiah($c['merchant_net_amount'])) ?></td>
                    <td><?= e(rupiah($c['driver_net_amount'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
