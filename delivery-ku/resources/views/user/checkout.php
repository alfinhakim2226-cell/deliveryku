<h4 class="fw-bold mb-3">Checkout Pesanan — <?= e($merchant['store_name']) ?></h4>
<form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="merchant_id" value="<?= (int)$merchant['id'] ?>">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="dk-card p-3 mb-3">
                <h6 class="fw-bold mb-2">1. Alamat Pengiriman (Kabupaten Indramayu)</h6>
                <?php if (!empty($addresses)): ?>
                    <div class="mb-2">
                        <label class="form-label small">Pilih Alamat Tersimpan</label>
                        <select name="address_id" class="form-select form-select-sm">
                            <?php foreach ($addresses as $a): ?>
                                <option value="<?= (int)$a['id'] ?>"><?= e($a['label']) ?> — <?= e($a['full_address']) ?> (<?= e($a['district_name'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                            <option value="0">+ Gunakan Alamat Baru di Bawah</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="row g-2 small">
                    <div class="col-md-6"><label class="form-label">Nama Penerima (jika alamat baru)</label><input type="text" name="recipient_name" value="<?= e(current_user()['full_name']) ?>" class="form-control form-control-sm"></div>
                    <div class="col-md-6"><label class="form-label">No. HP Penerima</label><input type="text" name="recipient_phone" value="<?= e(current_user()['phone']) ?>" class="form-control form-control-sm"></div>
                    <div class="col-md-5"><label class="form-label">Kecamatan</label><select name="district_id" class="form-select form-select-sm"><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-7"><label class="form-label">Alamat Lengkap</label><input type="text" name="full_address" class="form-control form-control-sm" placeholder="Jalan, RT/RW, Desa, Patokan"></div>
                </div>
            </div>

            <div class="dk-card p-3 mb-3">
                <h6 class="fw-bold mb-2">2. Metode Pembayaran</h6>
                <?php foreach (PAYMENT_METHODS as $k => $label): ?>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="payment_method" id="pm_<?= e($k) ?>" value="<?= e($k) ?>" <?= $k === 'cod' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-medium" for="pm_<?= e($k) ?>"><?= e($label) ?></label>
                    </div>
                <?php endforeach; ?>
                <div class="bg-light rounded p-2 mt-2 small">
                    <div class="fw-bold mb-1">Rekening Resmi Transfer Manual Delivery Ku:</div>
                    <?php foreach ($bankAccounts as $b): ?>
                        <div>• <?= e($b['bank']) ?>: <strong><?= e($b['number']) ?></strong> a.n. <?= e($b['holder']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dk-card p-3">
                <h6 class="fw-bold mb-2">3. Voucher & Catatan Pesanan</h6>
                <div class="row g-2">
                    <div class="col-md-5"><input type="text" name="voucher_code" class="form-control form-control-sm text-uppercase" placeholder="Kode Voucher (mis: INDRAMAYUHEMAT)"></div>
                    <div class="col-md-7"><input type="text" name="customer_notes" class="form-control form-control-sm" placeholder="Catatan untuk merchant / kurir..."></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="dk-card p-3">
                <h6 class="fw-bold mb-3">Ringkasan Pembayaran</h6>
                <ul class="list-unstyled small mb-3">
                    <?php foreach ($items as $it): ?>
                        <li class="d-flex justify-content-between mb-1">
                            <span><?= (int)$it['quantity'] ?>x <?= e($it['name']) ?></span>
                            <span><?= e(rupiah((($it['promo_price'] ?: $it['price']) + $it['additional_price']) * $it['quantity'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <div class="d-flex justify-content-between small mb-1"><span>Subtotal Produk</span><span><?= e(rupiah($previewCalc['subtotal'])) ?></span></div>
                <div class="d-flex justify-content-between small mb-1"><span>Estimasi Ongkos Kirim</span><span><?= e(rupiah($previewCalc['delivery_fee'])) ?></span></div>
                <div class="d-flex justify-content-between small mb-2"><span>Biaya Layanan Platform</span><span><?= e(rupiah($previewCalc['service_fee'])) ?></span></div>
                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 mb-3">
                    <span>Estimasi Total</span><span class="text-primary"><?= e(rupiah($previewCalc['total_amount'])) ?></span>
                </div>
                <button type="submit" class="btn btn-dk-green w-100 py-2 fw-bold">Buat Pesanan Sekarang</button>
            </div>
        </div>
    </div>
</form>
