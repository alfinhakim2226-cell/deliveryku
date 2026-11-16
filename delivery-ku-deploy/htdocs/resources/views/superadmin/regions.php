<h4 class="fw-bold mb-3">Wilayah Operasional (Kabupaten Indramayu & Ekspansi)</h4>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="dk-card p-3 mb-3">
            <h6 class="fw-bold mb-2">Tambah Kecamatan Baru</h6>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_district">
                <div class="mb-2"><label class="form-label small">Kabupaten</label><input type="text" name="regency_name" value="Kabupaten Indramayu" class="form-control form-control-sm" required></div>
                <div class="mb-2"><label class="form-label small">Nama Kecamatan</label><input type="text" name="name" placeholder="Contoh: Kecamatan Indramayu / Lohbener" class="form-control form-control-sm" required></div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label small">Kode Pos</label><input type="text" name="postal_code" class="form-control form-control-sm" placeholder="45211"></div>
                    <div class="col"><label class="form-label small">Tarif Dasar (Rp)</label><input type="number" name="base_delivery_fee" value="6000" class="form-control form-control-sm"></div>
                    <div class="col"><label class="form-label small">Per Km (Rp)</label><input type="number" name="per_km_fee" value="2500" class="form-control form-control-sm"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label small">Latitude</label><input type="text" name="latitude" class="form-control form-control-sm" placeholder="-6.3275"></div>
                    <div class="col"><label class="form-label small">Longitude</label><input type="text" name="longitude" class="form-control form-control-sm" placeholder="108.3249"></div>
                </div>
                <button class="btn btn-dk-primary btn-sm w-100">Simpan Kecamatan</button>
            </form>
        </div>

        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Tambah Desa / Kelurahan</h6>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_village">
                <div class="mb-2"><label class="form-label small">Kecamatan</label><select name="district_id" class="form-select form-select-sm"><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
                <div class="row g-2 mb-2">
                    <div class="col-8"><label class="form-label small">Nama Desa/Kelurahan</label><input type="text" name="name" class="form-control form-control-sm" required></div>
                    <div class="col-4"><label class="form-label small">Kode Pos</label><input type="text" name="postal_code" class="form-control form-control-sm"></div>
                </div>
                <button class="btn btn-dk-green btn-sm w-100">Simpan Desa/Kelurahan</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="dk-card p-3 mb-3">
            <h6 class="fw-bold mb-2">Daftar Kecamatan Aktif & Nonaktif</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle small">
                    <thead class="table-light"><tr><th>Kecamatan</th><th>Kode Pos</th><th>Ongkir Dasar</th><th>Per Km</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($districts as $d): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($d['name']) ?><br><small class="text-muted"><?= e($d['regency_name']) ?></small></td>
                            <td><?= e($d['postal_code']) ?></td>
                            <td><?= e(rupiah($d['base_delivery_fee'])) ?></td>
                            <td><?= e(rupiah($d['per_km_fee'])) ?></td>
                            <td><span class="badge text-bg-<?= $d['is_active'] ? 'success' : 'secondary' ?>"><?= $d['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td>
                                <form method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_district">
                                    <input type="hidden" name="district_id" value="<?= (int)$d['id'] ?>">
                                    <button class="btn btn-outline-secondary btn-sm"><?= $d['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Daftar Desa / Kelurahan</h6>
            <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                <table class="table table-sm small mb-0">
                    <thead class="table-light"><tr><th>Desa/Kelurahan</th><th>Kecamatan</th><th>Kode Pos</th><th>Koordinat</th></tr></thead>
                    <tbody>
                    <?php foreach ($villages as $v): ?>
                        <tr><td><?= e($v['name']) ?></td><td><?= e($v['district_name']) ?></td><td><?= e($v['postal_code']) ?></td><td><?= e((string)$v['latitude']) ?>, <?= e((string)$v['longitude']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
