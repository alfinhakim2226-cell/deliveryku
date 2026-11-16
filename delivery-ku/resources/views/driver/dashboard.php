<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-0">Dashboard Kurir — <?= e($driver['full_name']) ?></h4>
        <small class="text-muted"><?= e($driver['vehicle_type']) ?> (<?= e($driver['license_plate']) ?>) • Rating ★ <?= e((string)$driver['rating_avg']) ?> • Total Penghasilan: <strong class="text-success"><?= e(rupiah($totalEarnings)) ?></strong></small>
    </div>
    <form method="POST" action="<?= e(route_url('driver/action')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="toggle_online">
        <button class="btn btn-<?= $driver['is_online'] ? 'success' : 'secondary' ?> btn-sm">
            <i class="bi bi-broadcast me-1"></i> Status: <?= $driver['is_online'] ? 'ONLINE (Siap Terima Order)' : 'OFFLINE' ?>
        </button>
    </form>
</div>

<?php if ($driver['verification_status'] !== 'approved'): ?>
    <div class="alert alert-warning">Akun kurir Anda berstatus <strong><?= e($driver['verification_status']) ?></strong>. Unggah dokumen di halaman Profil agar dapat diverifikasi Admin.</div>
<?php endif; ?>

<h6 class="fw-bold text-primary mb-2">Pengantaran Aktif Anda</h6>
<div class="row g-3 mb-4">
    <?php if (empty($activeDeliveries)): ?>
        <div class="col-12"><div class="dk-card p-3 text-muted small">Belum ada pengantaran aktif saat ini.</div></div>
    <?php else: foreach ($activeDeliveries as $o): ?>
        <div class="col-lg-6">
            <div class="dk-card p-3 border-primary">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-primary"><?= e($o['order_code']) ?></span>
                    <?= order_status_badge($o['status']) ?>
                </div>
                <div class="small mb-2">
                    <div><strong>Ambil di Merchant:</strong> <?= e($o['store_name']) ?> — <?= e($o['merchant_address']) ?></div>
                    <div><strong>Antar ke Pelanggan:</strong> <?= e($o['customer_name']) ?> (<?= e($o['customer_phone']) ?>) — <?= e($o['delivery_address']) ?></div>
                    <div><strong>Upah Kurir:</strong> <span class="text-success fw-bold"><?= e(rupiah($o['driver_net_income'])) ?></span> • <strong>Metode Bayar:</strong> <?= e(PAYMENT_METHODS[$o['payment_method']] ?? $o['payment_method']) ?> (<?= e(rupiah($o['total_amount'])) ?>)</div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <a target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($o['m_lat'] ? $o['m_lat'] . ',' . $o['m_lng'] : $o['merchant_address']) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-geo-alt me-1"></i>Maps ke Toko</a>
                    <a target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($o['delivery_lat'] ? $o['delivery_lat'] . ',' . $o['delivery_lng'] : $o['delivery_address']) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-map me-1"></i>Maps ke Pelanggan</a>
                </div>
                <form method="POST" action="<?= e(route_url('driver/action')) ?>" class="d-flex flex-wrap gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                    <?php if ($o['status'] === 'driver_menuju_merchant'): ?>
                        <button name="action" value="pickup" class="btn btn-dk-primary btn-sm">Konfirmasi Pesanan Diambil</button>
                    <?php elseif ($o['status'] === 'pesanan_diambil'): ?>
                        <button name="action" value="delivering" class="btn btn-primary btn-sm">Mulai Antar ke Pelanggan</button>
                    <?php elseif ($o['status'] === 'dalam_pengiriman'): ?>
                        <input type="text" name="verification_pin" class="form-control form-control-sm" placeholder="PIN Pelanggan (opsional/4 digit)" style="width:185px">
                        <button name="action" value="complete" class="btn btn-dk-green btn-sm">Konfirmasi Sudah Diterima</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<h6 class="fw-bold mb-2">Pesanan Siap Diambil (Tersedia)</h6>
<div class="row g-3">
    <?php if (empty($availableOrders)): ?>
        <div class="col-12"><div class="dk-card p-3 text-muted small">Belum ada pesanan baru yang menunggu kurir.</div></div>
    <?php else: foreach ($availableOrders as $ao): ?>
        <div class="col-md-6">
            <div class="dk-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong><?= e($ao['order_code']) ?></strong>
                    <span class="badge text-bg-success">Upah <?= e(rupiah($ao['driver_net_income'])) ?></span>
                </div>
                <div class="small text-muted mb-2">
                    <div>Toko: <?= e($ao['store_name']) ?> (<?= e($ao['merchant_address']) ?>)</div>
                    <div>Tujuan: <?= e($ao['delivery_address']) ?> • Jarak ~<?= e((string)$ao['distance_km']) ?> km</div>
                </div>
                <form method="POST" action="<?= e(route_url('driver/action')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int)$ao['id'] ?>">
                    <button name="action" value="accept" class="btn btn-dk-primary btn-sm w-100">Terima Tugas Pengantaran</button>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
