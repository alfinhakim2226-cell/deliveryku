<div id="live-order-tracker" data-api-url="<?= e(route_url('api/order-status', ['id' => $order['id']])) ?>"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-0">Pesanan #<?= e($order['order_code']) ?></h4>
        <small class="text-muted"><?= e($order['store_name']) ?> • <?= e(format_indo_date($order['created_at'])) ?></small>
    </div>
    <span id="live-order-status-label" class="badge text-bg-primary fs-6"><?= e(order_status_label($order['status'])) ?></span>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="dk-card p-3 mb-3">
            <h6 class="fw-bold mb-2">Informasi Pengiriman & Kurir</h6>
            <div class="small">
                <div><strong>Alamat Tujuan:</strong> <?= e($order['delivery_address']) ?></div>
                <div><strong>Kurir Bertugas:</strong> <span id="live-driver-info"><?= $order['driver_name'] ? e($order['driver_name']) . ' (' . e($order['license_plate']) . ') • ' . e($order['driver_phone']) : 'Menunggu penugasan kurir' ?></span></div>
                <?php if (!empty($order['verification_pin'])): ?>
                    <div class="mt-2 alert alert-info py-2 mb-0 small">PIN Verifikasi Penerimaan untuk Kurir: <strong class="fs-6"><?= e($order['verification_pin']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dk-card p-3 mb-3">
            <h6 class="fw-bold mb-2">Rincian Item & Biaya</h6>
            <?php foreach ($items as $it): ?>
                <div class="d-flex justify-content-between small mb-1">
                    <span><?= (int)$it['quantity'] ?>x <?= e($it['product_name']) ?> <?= $it['variant_name'] ? '(' . e($it['variant_name']) . ')' : '' ?></span>
                    <span><?= e(rupiah($it['subtotal'])) ?></span>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between small"><span>Subtotal</span><span><?= e(rupiah($order['subtotal'])) ?></span></div>
            <div class="d-flex justify-content-between small"><span>Ongkos Kirim</span><span><?= e(rupiah($order['delivery_fee'])) ?></span></div>
            <div class="d-flex justify-content-between small"><span>Biaya Layanan</span><span><?= e(rupiah($order['service_fee'])) ?></span></div>
            <?php if ((float)$order['voucher_discount'] > 0): ?><div class="d-flex justify-content-between small text-success"><span>Diskon Voucher</span><span>-<?= e(rupiah($order['voucher_discount'])) ?></span></div><?php endif; ?>
            <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 mt-2"><span>Total Pembayaran</span><span class="text-primary"><?= e(rupiah($order['total_amount'])) ?></span></div>
        </div>

        <?php if ($order['status'] === 'dalam_pengiriman'): ?>
            <form method="POST" class="mb-3" data-confirm="Konfirmasi bahwa pesanan sudah diterima dengan baik?">
                <?= csrf_field() ?><input type="hidden" name="action" value="confirm_received">
                <button class="btn btn-dk-green w-100 py-2 fw-bold">Konfirmasi Pesanan Sudah Diterima</button>
            </form>
        <?php endif; ?>

        <?php if ($order['status'] === 'selesai' && !$hasReview): ?>
            <div class="dk-card p-3 mb-3">
                <h6 class="fw-bold mb-2">Beri Rating & Ulasan</h6>
                <form method="POST">
                    <?= csrf_field() ?><input type="hidden" name="action" value="submit_review">
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small">Rating Toko (1-5)</label><select name="merchant_rating" class="form-select form-select-sm"><option value="5">★★★★★ (5)</option><option value="4">★★★★ (4)</option><option value="3">★★★ (3)</option><option value="2">★★ (2)</option><option value="1">★ (1)</option></select></div>
                        <div class="col-6"><label class="form-label small">Rating Kurir (1-5)</label><select name="driver_rating" class="form-select form-select-sm"><option value="5">★★★★★ (5)</option><option value="4">★★★★ (4)</option><option value="3">★★★ (3)</option><option value="2">★★ (2)</option><option value="1">★ (1)</option></select></div>
                    </div>
                    <textarea name="comment" class="form-control form-control-sm mb-2" rows="2" placeholder="Tulis pengalaman Anda..." required></textarea>
                    <button class="btn btn-dk-primary btn-sm">Kirim Penilaian</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <?php if ($order['payment_method'] !== 'cod' && $order['payment_status'] !== 'verified'): ?>
            <div class="dk-card p-3 mb-3 border-warning">
                <h6 class="fw-bold text-warning-emphasis">Upload Bukti Pembayaran Manual</h6>
                <div class="small mb-2">Transfer tepat sebesar <strong><?= e(rupiah($order['total_amount'])) ?></strong> ke salah satu rekening resmi:</div>
                <ul class="small mb-3">
                    <?php foreach ($bankAccounts as $b): ?><li><?= e($b['bank']) ?>: <strong><?= e($b['number']) ?></strong> (<?= e($b['holder']) ?>)</li><?php endforeach; ?>
                </ul>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?><input type="hidden" name="action" value="upload_proof">
                    <div class="mb-2"><label class="form-label small">Nama Pengirim</label><input type="text" name="sender_name" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="form-label small">Bank Pengirim</label><input type="text" name="sender_bank" class="form-control form-control-sm" placeholder="BRI / BCA / DANA" required></div>
                    <div class="mb-2"><label class="form-label small">Nominal Transfer</label><input type="number" name="transfer_amount" value="<?= (int)$order['total_amount'] ?>" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="form-label small">Waktu Transfer</label><input type="datetime-local" name="transfer_time" value="<?= date('Y-m-d\TH:i') ?>" class="form-control form-control-sm" required></div>
                    <div class="mb-3"><label class="form-label small">Foto Struk / Bukti Transfer</label><input type="file" name="proof_image" accept="image/*" class="form-control form-control-sm" required></div>
                    <button class="btn btn-dk-primary btn-sm w-100">Kirim Bukti Transfer</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Riwayat Status Pesanan</h6>
            <ul class="list-group list-group-flush small">
                <?php foreach ($histories as $h): ?>
                    <li class="list-group-item px-0">
                        <div class="fw-semibold text-primary"><?= e(order_status_label($h['to_status'])) ?></div>
                        <div class="text-muted"><?= e($h['notes']) ?> • <?= e(format_indo_date($h['created_at'])) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
