<h4 class="fw-bold mb-3">Pengaturan Aplikasi, Tarif, Komisi & Rekening</h4>
<div class="dk-card p-4">
    <form method="POST">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary">Identitas Aplikasi</h6>
                <div class="mb-2"><label class="form-label small">Nama Aplikasi</label><input type="text" name="settings[app_name]" value="<?= e($settings['app_name'] ?? 'Delivery Ku') ?>" class="form-control form-control-sm"></div>
                <div class="mb-2"><label class="form-label small">Tagline</label><input type="text" name="settings[app_tagline]" value="<?= e($settings['app_tagline'] ?? '') ?>" class="form-control form-control-sm"></div>
                <div class="mb-2"><label class="form-label small">Nomor CS / WhatsApp</label><input type="text" name="settings[support_phone]" value="<?= e($settings['support_phone'] ?? '') ?>" class="form-control form-control-sm"></div>
                <div class="mb-2"><label class="form-label small">Email Resmi</label><input type="email" name="settings[support_email]" value="<?= e($settings['support_email'] ?? '') ?>" class="form-control form-control-sm"></div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-success">Komisi, Tarif Ongkir & Biaya Layanan</h6>
                <div class="row g-2">
                    <div class="col-6 mb-2"><label class="form-label small">Komisi Merchant Default (%)</label><input type="number" step="0.5" name="settings[default_commission_rate]" value="<?= e($settings['default_commission_rate'] ?? '15') ?>" class="form-control form-control-sm"></div>
                    <div class="col-6 mb-2"><label class="form-label small">Bagian Ongkir Driver (%)</label><input type="number" step="0.5" name="settings[driver_share_rate]" value="<?= e($settings['driver_share_rate'] ?? '85') ?>" class="form-control form-control-sm"></div>
                    <div class="col-6 mb-2"><label class="form-label small">Biaya Layanan (Rp)</label><input type="number" name="settings[service_fee_amount]" value="<?= e($settings['service_fee_amount'] ?? '2000') ?>" class="form-control form-control-sm"></div>
                    <div class="col-6 mb-2"><label class="form-label small">Min. Transaksi (Rp)</label><input type="number" name="settings[min_transaction_amount]" value="<?= e($settings['min_transaction_amount'] ?? '15000') ?>" class="form-control form-control-sm"></div>
                    <div class="col-6 mb-2"><label class="form-label small">Ongkir Dasar Default (Rp)</label><input type="number" name="settings[base_delivery_fee]" value="<?= e($settings['base_delivery_fee'] ?? '6000') ?>" class="form-control form-control-sm"></div>
                    <div class="col-6 mb-2"><label class="form-label small">Tarif per Km (Rp)</label><input type="number" name="settings[per_km_delivery_fee]" value="<?= e($settings['per_km_delivery_fee'] ?? '2500') ?>" class="form-control form-control-sm"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Sertakan Data Demo di KPI Dashboard?</label>
                    <select name="settings[include_demo_in_reports]" class="form-select form-select-sm">
                        <option value="0" <?= ($settings['include_demo_in_reports'] ?? '0') === '0' ? 'selected' : '' ?>>0 - Pisahkan (Produksi Saja)</option>
                        <option value="1" <?= ($settings['include_demo_in_reports'] ?? '0') === '1' ? 'selected' : '' ?>>1 - Tampilkan Data Demo di KPI</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <h6 class="fw-bold text-dark">Rekening Bank Pembayaran Manual (JSON)</h6>
                <textarea name="settings[bank_accounts_json]" class="form-control form-control-sm font-monospace" rows="3"><?= e($settings['bank_accounts_json'] ?? '[]') ?></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-dk-primary mt-3 px-4">Simpan Semua Pengaturan</button>
    </form>
</div>
