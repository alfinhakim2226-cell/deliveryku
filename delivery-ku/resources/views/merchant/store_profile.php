<h4 class="fw-bold mb-3">Profil & Pengaturan Toko</h4>
<div class="dk-card p-4">
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama Toko</label><input type="text" name="store_name" value="<?= e($merchant['store_name']) ?>" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Kategori Usaha</label><select name="merchant_category_id" class="form-select"><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$merchant['merchant_category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Kecamatan</label><select name="district_id" class="form-select"><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>" <?= (int)$merchant['district_id'] === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">Deskripsi Toko</label><textarea name="description" class="form-control" rows="2"><?= e($merchant['description'] ?? '') ?></textarea></div>
            <div class="col-md-8"><label class="form-label">Alamat Lengkap Toko</label><input type="text" name="address" value="<?= e($merchant['address']) ?>" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Kode Pos</label><input type="text" name="postal_code" value="<?= e($merchant['postal_code'] ?? '') ?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Latitude</label><input type="text" name="latitude" value="<?= e((string)($merchant['latitude'] ?? '')) ?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Longitude</label><input type="text" name="longitude" value="<?= e((string)($merchant['longitude'] ?? '')) ?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Jam Buka</label><input type="time" name="open_time" value="<?= e(substr($merchant['open_time'], 0, 5)) ?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Jam Tutup</label><input type="time" name="close_time" value="<?= e(substr($merchant['close_time'], 0, 5)) ?>" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Logo Toko</label><input type="file" name="logo" accept="image/*" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Foto / Banner Toko</label><input type="file" name="banner" accept="image/*" class="form-control"></div>
            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_open" id="m_open" <?= $merchant['is_open'] ? 'checked' : '' ?>><label class="form-check-label" for="m_open">Toko sedang buka menerima pesanan</label></div></div>
        </div>
        <button class="btn btn-dk-primary mt-3">Simpan Profil Toko</button>
    </form>
</div>
