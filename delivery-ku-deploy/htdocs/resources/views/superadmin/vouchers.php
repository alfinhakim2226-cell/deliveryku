<h4 class="fw-bold mb-3">Manajemen Voucher & Diskon</h4>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-3">Buat Voucher Baru</h6>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="mb-2"><label class="form-label small">Kode Voucher</label><input type="text" name="code" class="form-control form-control-sm text-uppercase" required placeholder="INDRAMAYU10"></div>
                <div class="mb-2"><label class="form-label small">Judul Promo</label><input type="text" name="title" class="form-control form-control-sm" required></div>
                <div class="mb-2"><label class="form-label small">Tipe Diskon</label><select name="discount_type" class="form-select form-select-sm"><option value="fixed">Potongan Nominal (Rp)</option><option value="percent">Persentase (%)</option></select></div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label small">Nilai Diskon</label><input type="number" name="discount_value" class="form-control form-control-sm" required></div>
                    <div class="col"><label class="form-label small">Maks Diskon (Rp)</label><input type="number" name="max_discount" class="form-control form-control-sm"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label small">Min Transaksi</label><input type="number" name="min_transaction" value="20000" class="form-control form-control-sm"></div>
                    <div class="col"><label class="form-label small">Kuota</label><input type="number" name="quota" value="100" class="form-control form-control-sm"></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col"><label class="form-label small">Mulai</label><input type="date" name="start_date" value="<?= date('Y-m-d') ?>" class="form-control form-control-sm"></div>
                    <div class="col"><label class="form-label small">Berakhir</label><input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+90 days')) ?>" class="form-control form-control-sm"></div>
                </div>
                <button class="btn btn-dk-primary btn-sm w-100">Simpan Voucher</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-3">Daftar Voucher</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle small">
                    <thead class="table-light"><tr><th>Kode</th><th>Judul</th><th>Diskon</th><th>Min. Belanja</th><th>Kuota</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($vouchers as $v): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= e($v['code']) ?></td>
                            <td><?= e($v['title']) ?></td>
                            <td><?= $v['discount_type'] === 'percent' ? e((string)$v['discount_value']) . '%' : rupiah($v['discount_value']) ?></td>
                            <td><?= e(rupiah($v['min_transaction'])) ?></td>
                            <td><?= (int)$v['used_count'] ?> / <?= (int)$v['quota'] ?></td>
                            <td><span class="badge text-bg-<?= $v['is_active'] ? 'success' : 'secondary' ?>"><?= $v['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td>
                                <form method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="voucher_id" value="<?= (int)$v['id'] ?>">
                                    <button class="btn btn-outline-secondary btn-sm"><?= $v['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
