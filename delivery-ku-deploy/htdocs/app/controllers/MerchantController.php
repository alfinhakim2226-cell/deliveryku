<?php
declare(strict_types=1);

final class MerchantController
{
    private function getMerchant(): array
    {
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        $stmt = $pdo->prepare('SELECT * FROM merchants WHERE user_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$uid]);
        $merchant = $stmt->fetch();
        if (!$merchant) {
            flash_set('warning', 'Profil toko belum tersedia. Lengkapi profil toko terlebih dahulu.');
            redirect_to('merchant/profile');
        }
        return $merchant;
    }

    public function dashboard(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $merchant = $this->getMerchant();
        $mid = (int)$merchant['id'];

        $stats = [
            'incoming' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE merchant_id={$mid} AND status='menunggu_konfirmasi_merchant'")->fetchColumn(),
            'preparing' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE merchant_id={$mid} AND status IN ('pesanan_dikonfirmasi','sedang_disiapkan','menunggu_driver')")->fetchColumn(),
            'completed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE merchant_id={$mid} AND status='selesai'")->fetchColumn(),
            'gross_sales' => (float)$pdo->query("SELECT COALESCE(SUM(subtotal),0) FROM orders WHERE merchant_id={$mid} AND status='selesai'")->fetchColumn(),
            'platform_comm' => (float)$pdo->query("SELECT COALESCE(SUM(platform_commission),0) FROM orders WHERE merchant_id={$mid} AND status='selesai'")->fetchColumn(),
            'net_income' => (float)$pdo->query("SELECT COALESCE(SUM(merchant_net_income),0) FROM orders WHERE merchant_id={$mid} AND status='selesai'")->fetchColumn(),
            'products_count' => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE merchant_id={$mid} AND deleted_at IS NULL")->fetchColumn(),
        ];

        $recentOrders = $pdo->query(
            "SELECT o.*, u.full_name AS customer_name FROM orders o JOIN users u ON u.id=o.user_id WHERE o.merchant_id={$mid} ORDER BY o.id DESC LIMIT 8"
        )->fetchAll();

        render_view('merchant/dashboard', compact('merchant', 'stats', 'recentOrders') + [
            'pageTitle' => 'Dashboard Merchant - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function storeProfile(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? 'save_profile';
            if ($action === 'toggle_open') {
                $pdo->prepare('UPDATE merchants SET is_open = NOT is_open WHERE user_id = ?')->execute([$uid]);
                flash_set('success', 'Status buka/tutup toko diperbarui.');
                redirect_to('merchant/profile');
            }

            $logoPath = null;
            if (!empty($_FILES['logo']['name'])) {
                $up = UploadService::uploadImage($_FILES['logo'], 'merchants');
                if ($up['ok']) {
                    $logoPath = $up['path'];
                }
            }
            $bannerPath = null;
            if (!empty($_FILES['banner']['name'])) {
                $up = UploadService::uploadImage($_FILES['banner'], 'merchants');
                if ($up['ok']) {
                    $bannerPath = $up['path'];
                }
            }

            $existing = $pdo->prepare('SELECT * FROM merchants WHERE user_id = ? LIMIT 1');
            $existing->execute([$uid]);
            $m = $existing->fetch();

            if ($m) {
                $pdo->prepare(
                    'UPDATE merchants SET store_name=?, merchant_category_id=?, district_id=?, description=?, address=?, postal_code=?, latitude=?, longitude=?, open_time=?, close_time=?, is_open=?, logo_path=COALESCE(?, logo_path), banner_path=COALESCE(?, banner_path) WHERE id=?'
                )->execute([
                    trim((string)$_POST['store_name']),
                    (int)$_POST['merchant_category_id'],
                    (int)$_POST['district_id'],
                    trim((string)$_POST['description']),
                    trim((string)$_POST['address']),
                    trim((string)($_POST['postal_code'] ?? '')),
                    !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                    !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                    $_POST['open_time'] ?: '08:00:00',
                    $_POST['close_time'] ?: '21:00:00',
                    isset($_POST['is_open']) ? 1 : 0,
                    $logoPath,
                    $bannerPath,
                    $m['id'],
                ]);
                flash_set('success', 'Profil toko berhasil diperbarui.');
            }
            redirect_to('merchant/profile');
        }

        $merchant = $this->getMerchant();
        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 ORDER BY name')->fetchAll();
        $categories = $pdo->query('SELECT * FROM merchant_categories WHERE is_active = 1 ORDER BY name')->fetchAll();
        render_view('merchant/store_profile', compact('merchant', 'districts', 'categories') + [
            'pageTitle' => 'Profil & Pengaturan Toko - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function products(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $merchant = $this->getMerchant();
        $mid = (int)$merchant['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? '';
            if ($action === 'save') {
                $pid = (int)($_POST['product_id'] ?? 0);
                $name = trim((string)$_POST['name']);
                $price = (float)$_POST['price'];
                $promo = !empty($_POST['promo_price']) ? (float)$_POST['promo_price'] : null;
                $stock = (int)($_POST['stock'] ?? 50);
                $isAvail = isset($_POST['is_available']) ? 1 : 0;

                if ($pid > 0) {
                    $pdo->prepare('UPDATE products SET category_id=?, name=?, description=?, price=?, promo_price=?, stock=?, is_available=? WHERE id=? AND merchant_id=?')
                        ->execute([(int)$_POST['category_id'], $name, trim((string)$_POST['description']), $price, $promo, $stock, $isAvail, $pid, $mid]);
                    flash_set('success', 'Produk berhasil diperbarui.');
                } else {
                    $pdo->prepare('INSERT INTO products (merchant_id, category_id, name, slug, description, price, promo_price, stock, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                        ->execute([$mid, (int)$_POST['category_id'], $name, slugify($name) . '-' . random_int(100, 999), trim((string)$_POST['description']), $price, $promo, $stock, $isAvail]);
                    $pid = (int)$pdo->lastInsertId();
                    if (!empty($_POST['variant_name'])) {
                        $pdo->prepare('INSERT INTO product_variants (product_id, variant_name, additional_price) VALUES (?, ?, ?)')
                            ->execute([$pid, trim((string)$_POST['variant_name']), (float)($_POST['variant_price'] ?? 0)]);
                    }
                    flash_set('success', 'Produk baru ditambahkan ke menu toko.');
                }

                if (!empty($_FILES['image']['name']) && $pid > 0) {
                    $up = UploadService::uploadImage($_FILES['image'], 'products');
                    if ($up['ok']) {
                        $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?')->execute([$pid]);
                        $pdo->prepare('INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, 1)')->execute([$pid, $up['path']]);
                    }
                }
            } elseif ($action === 'toggle') {
                $pdo->prepare('UPDATE products SET is_available = NOT is_available WHERE id = ? AND merchant_id = ?')
                    ->execute([(int)$_POST['product_id'], $mid]);
                flash_set('success', 'Status ketersediaan produk diperbarui.');
            } elseif ($action === 'delete') {
                $pdo->prepare('UPDATE products SET deleted_at = NOW(), is_available = 0 WHERE id = ? AND merchant_id = ?')
                    ->execute([(int)$_POST['product_id'], $mid]);
                flash_set('success', 'Produk dihapus dari daftar menu.');
            }
            redirect_to('merchant/products');
        }

        $products = $pdo->query(
            "SELECT p.*, c.name AS category_name,
                    (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
             FROM products p LEFT JOIN categories c ON c.id=p.category_id
             WHERE p.merchant_id={$mid} AND p.deleted_at IS NULL ORDER BY p.id DESC"
        )->fetchAll();
        $categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order')->fetchAll();

        render_view('merchant/products', compact('merchant', 'products', 'categories') + [
            'pageTitle' => 'Manajemen Menu & Produk - Merchant Delivery Ku',
        ], 'dashboard_shell');
    }

    public function orders(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $merchant = $this->getMerchant();
        $mid = (int)$merchant['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $oid = (int)$_POST['order_id'];
            $check = $pdo->prepare('SELECT id FROM orders WHERE id = ? AND merchant_id = ?');
            $check->execute([$oid, $mid]);
            if ($check->fetch()) {
                $toStatus = (string)$_POST['to_status'];
                $notes = trim((string)($_POST['notes'] ?? ''));
                $res = OrderService::transitionStatus($oid, $toStatus, (int)current_user()['id'], 'merchant', $notes);
                flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
            }
            redirect_to('merchant/orders');
        }

        $orders = $pdo->query(
            "SELECT o.*, u.full_name AS customer_name, u.phone AS customer_phone
             FROM orders o JOIN users u ON u.id=o.user_id
             WHERE o.merchant_id={$mid} ORDER BY o.id DESC LIMIT 60"
        )->fetchAll();

        foreach ($orders as &$ord) {
            $iStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $iStmt->execute([$ord['id']]);
            $ord['items'] = $iStmt->fetchAll();
        }
        unset($ord);

        render_view('merchant/orders', compact('merchant', 'orders') + [
            'pageTitle' => 'Pesanan Masuk & Riwayat - Merchant Delivery Ku',
        ], 'dashboard_shell');
    }

    public function finance(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $merchant = $this->getMerchant();
        $mid = (int)$merchant['id'];

        $daily = $pdo->query(
            "SELECT DATE(created_at) AS tgl, COUNT(*) AS jml, SUM(subtotal) AS omzet, SUM(platform_commission) AS komisi, SUM(merchant_net_income) AS bersih
             FROM orders WHERE merchant_id={$mid} AND status='selesai'
             GROUP BY DATE(created_at) ORDER BY tgl DESC LIMIT 30"
        )->fetchAll();

        $monthly = $pdo->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS bulan, COUNT(*) AS jml, SUM(subtotal) AS omzet, SUM(platform_commission) AS komisi, SUM(merchant_net_income) AS bersih
             FROM orders WHERE merchant_id={$mid} AND status='selesai'
             GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY bulan DESC LIMIT 12"
        )->fetchAll();

        render_view('merchant/finance', compact('merchant', 'daily', 'monthly') + [
            'pageTitle' => 'Rekap Keuangan & Komisi - Merchant Delivery Ku',
        ], 'dashboard_shell');
    }

    public function reviews(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $merchant = $this->getMerchant();
        $reviews = $pdo->query(
            "SELECT r.*, u.full_name AS customer_name, o.order_code
             FROM reviews r JOIN users u ON u.id=r.user_id JOIN orders o ON o.id=r.order_id
             WHERE r.merchant_id={$merchant['id']} ORDER BY r.id DESC"
        )->fetchAll();
        render_view('merchant/reviews', compact('merchant', 'reviews') + [
            'pageTitle' => 'Ulasan Pelanggan - Merchant Delivery Ku',
        ], 'dashboard_shell');
    }
}
