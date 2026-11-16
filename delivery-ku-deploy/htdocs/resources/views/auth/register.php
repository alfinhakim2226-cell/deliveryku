<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Daftar - Delivery Ku') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/deliveryku.css')) ?>" rel="stylesheet">
</head>
<body class="py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="text-center mb-3">
                <h3 class="fw-bold text-dk-blue">Daftar Akun Delivery Ku</h3>
                <p class="text-muted small">Gabung sebagai Pelanggan, Merchant UMKM, atau Driver Kabupaten Indramayu</p>
            </div>
            <?php foreach (flash_get_all() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
            <?php endforeach; ?>
            <div class="dk-card p-4">
                <form method="POST" action="<?= e(route_url('auth/register')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Daftar Sebagai</label>
                        <select name="role_slug" id="role_slug" class="form-select">
                            <option value="user">Pelanggan (Pesan Makanan & Produk)</option>
                            <option value="merchant">Merchant / Pemilik Toko UMKM</option>
                            <option value="driver">Driver / Kurir Indramayu</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nomor HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" required placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Kata Sandi (min. 6 karakter)</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                    </div>

                    <div id="region_fields" class="mb-2 d-none">
                        <label class="form-label">Kecamatan Operasional (Kab. Indramayu)</label>
                        <select name="district_id" class="form-select">
                            <?php foreach ($districts as $d): ?>
                                <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?> (<?= e($d['postal_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="merchant_fields" class="d-none border rounded p-3 bg-light mb-3">
                        <h6 class="fw-bold text-primary">Detail Toko Merchant</h6>
                        <div class="mb-2">
                            <label class="form-label">Nama Toko / Warung</label>
                            <input type="text" name="store_name" class="form-control" placeholder="Contoh: Warung Nasi Lengko Kang Asep">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Kategori Usaha</label>
                            <select name="merchant_category_id" class="form-select">
                                <?php foreach ($merchantCategories as $mc): ?>
                                    <option value="<?= (int)$mc['id'] ?>"><?= e($mc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Alamat Lengkap Toko</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div id="driver_fields" class="d-none border rounded p-3 bg-light mb-3">
                        <h6 class="fw-bold text-success">Data Kendaraan Kurir</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Jenis</label>
                                <select name="vehicle_type" class="form-select">
                                    <option value="Motor">Motor</option>
                                    <option value="Mobil">Mobil</option>
                                    <option value="Sepeda Listrik">Sepeda Listrik</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Merk / Tipe</label>
                                <input type="text" name="vehicle_brand" class="form-control" placeholder="Honda Vario">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nomor Polisi</label>
                                <input type="text" name="license_plate" class="form-control" placeholder="E 1234 DK">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dk-primary w-100 py-2 fw-semibold mt-2">Buat Akun Sekarang</button>
                </form>
                <div class="text-center mt-3 small">
                    Sudah punya akun? <a href="<?= e(route_url('auth/login')) ?>" class="fw-semibold">Masuk</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const roleSel = document.getElementById('role_slug');
const updateFields = () => {
    const r = roleSel.value;
    document.getElementById('region_fields').classList.toggle('d-none', r === 'user');
    document.getElementById('merchant_fields').classList.toggle('d-none', r !== 'merchant');
    document.getElementById('driver_fields').classList.toggle('d-none', r !== 'driver');
};
roleSel.addEventListener('change', updateFields);
</script>
</body>
</html>
