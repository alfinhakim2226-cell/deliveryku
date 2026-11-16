<!-- Lokasi Pengantaran Indramayu & Pencarian -->
<div class="dk-card p-3 mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-md-5">
            <form method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="route" value="user/home">
                <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
                <select name="district_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($districts as $d): ?>
                        <option value="<?= (int)$d['id'] ?>" <?= $selectedDistrictId === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?>, Kab. Indramayu</option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="col-md-7">
            <form method="GET" action="<?= e(base_url('index.php')) ?>" class="input-group input-group-sm">
                <input type="hidden" name="route" value="user/search">
                <input type="hidden" name="district_id" value="<?= (int)$selectedDistrictId ?>">
                <input type="text" name="q" class="form-control" placeholder="Mau makan apa hari ini? Cari Nasi Lengko, Ikan Bakar, UMKM...">
                <button class="btn btn-dk-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
            </form>
        </div>
    </div>
</div>

<!-- Banner Promosi -->
<div class="dk-hero-banner p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <?php $b = $banners[0] ?? ['badge_text' => 'DELIVERY KU INDRAMAYU', 'title' => 'Pesan Antar Cepat Makanan & Produk UMKM Lokal', 'subtitle' => 'Melayani Jatibarang, Sukagumiwang, Kertasemaya, Widasari, Tukdana, Bangodua & Krangkeng']; ?>
            <span class="badge bg-success mb-2"><?= e($b['badge_text'] ?? 'PROMO') ?></span>
            <h3 class="fw-bold mb-1"><?= e($b['title']) ?></h3>
            <p class="mb-3 opacity-75"><?= e($b['subtitle']) ?></p>
            <a href="<?= e(route_url('user/search')) ?>" class="btn btn-light text-primary fw-semibold btn-sm">Jelajahi Semua Menu</a>
        </div>
    </div>
</div>

<!-- Kategori Makanan -->
<h5 class="fw-bold mb-2">Kategori Pilihan</h5>
<div class="row g-2 mb-4">
    <?php foreach ($categories as $c): ?>
        <div class="col-4 col-md-2">
            <a href="<?= e(route_url('user/search', ['category_id' => $c['id']])) ?>" class="dk-card p-2 text-center d-block text-decoration-none text-dark h-100">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-1" style="width:42px;height:42px;">
                    <i class="bi <?= e($c['icon']) ?> fs-5"></i>
                </div>
                <div class="small fw-semibold lh-sm"><?= e($c['name']) ?></div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Merchant Rekomendasi & Terdekat -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="fw-bold mb-0">Restoran & UMKM Rekomendasi Indramayu</h5>
    <a href="<?= e(route_url('user/search')) ?>" class="small text-decoration-none">Lihat Semua</a>
</div>
<div class="row g-3 mb-4">
    <?php foreach ($merchants as $m): ?>
        <div class="col-md-6 col-lg-4">
            <a href="<?= e(route_url('user/merchant', ['id' => $m['id']])) ?>" class="dk-card p-3 d-block text-decoration-none text-dark h-100">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="fw-bold mb-0 text-dk-navy"><?= e($m['store_name']) ?></h6>
                    <span class="badge text-bg-<?= $m['is_open'] ? 'success' : 'secondary' ?>"><?= $m['is_open'] ? 'Buka' : 'Tutup' ?></span>
                </div>
                <div class="small text-muted mb-2"><?= e($m['category_name'] ?? 'UMKM') ?> • <?= e($m['district_name'] ?? 'Indramayu') ?></div>
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="text-warning fw-semibold">★ <?= e((string)$m['rating_avg']) ?> (<?= (int)$m['total_reviews'] ?>)</span>
                    <span class="text-primary fw-semibold">Lihat Menu <i class="bi bi-arrow-right"></i></span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Promo Hari Ini & Makanan Populer -->
<h5 class="fw-bold mb-2">Promo Hari Ini & Menu Populer</h5>
<div class="row g-3 mb-4">
    <?php foreach ($popularProducts as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="dk-card h-100 d-flex flex-column">
                <img src="<?= e(upload_url($p['primary_image'])) ?>" alt="<?= e($p['name']) ?>" class="product-card-img w-100">
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <div class="fw-bold small mb-1"><?= e($p['name']) ?></div>
                    <div class="text-muted mb-2" style="font-size:.76rem;"><?= e($p['store_name']) ?></div>
                    <div class="mb-2">
                        <?php if (!empty($p['promo_price'])): ?>
                            <span class="fw-bold text-primary"><?= e(rupiah($p['promo_price'])) ?></span>
                            <small class="text-decoration-line-through text-muted d-block" style="font-size:.72rem;"><?= e(rupiah($p['price'])) ?></small>
                        <?php else: ?>
                            <span class="fw-bold text-primary"><?= e(rupiah($p['price'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-auto">
                        <a href="<?= e(route_url('user/merchant', ['id' => $p['merchant_id']])) ?>" class="btn btn-dk-primary btn-sm w-100">Pesan Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
