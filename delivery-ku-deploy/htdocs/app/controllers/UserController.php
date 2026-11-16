<?php
declare(strict_types=1);

final class UserController
{
    public function home(): void
    {
        $pdo = Database::connection();
        $selectedDistrictId = (int)($_GET['district_id'] ?? ($_SESSION['selected_district_id'] ?? 1));
        $_SESSION['selected_district_id'] = $selectedDistrictId;

        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name')->fetchAll();
        $banners = $pdo->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order, id DESC')->fetchAll();
        $categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order')->fetchAll();

        $merchants = $pdo->query(
            'SELECT m.*, d.name AS district_name, mc.name AS category_name
             FROM merchants m
             LEFT JOIN districts d ON d.id=m.district_id
             LEFT JOIN merchant_categories mc ON mc.id=m.merchant_category_id
             WHERE m.verification_status="approved" AND m.deleted_at IS NULL
             ORDER BY m.is_recommended DESC, m.rating_avg DESC LIMIT 12'
        )->fetchAll();

        $popularProducts = $pdo->query(
            'SELECT p.*, m.store_name, m.is_open,
                    (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
             FROM products p JOIN merchants m ON m.id=p.merchant_id
             WHERE p.is_available = 1 AND p.deleted_at IS NULL AND m.verification_status="approved"
             ORDER BY p.sold_count DESC LIMIT 8'
        )->fetchAll();

        $promoProducts = $pdo->query(
            'SELECT p.*, m.store_name, m.is_open,
                    (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
             FROM products p JOIN merchants m ON m.id=p.merchant_id
             WHERE p.is_available = 1 AND p.deleted_at IS NULL AND p.promo_price IS NOT NULL AND p.promo_price < p.price
             ORDER BY p.id DESC LIMIT 6'
        )->fetchAll();

        $newestProducts = $pdo->query(
            'SELECT p.*, m.store_name, m.is_open,
                    (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
             FROM products p JOIN merchants m ON m.id=p.merchant_id
             WHERE p.is_available = 1 AND p.deleted_at IS NULL
             ORDER BY p.id DESC LIMIT 6'
        )->fetchAll();

        render_view('user/home', compact(
            'districts', 'selectedDistrictId', 'banners', 'categories',
            'merchants', 'popularProducts', 'promoProducts', 'newestProducts'
        ) + ['pageTitle' => 'Delivery Ku — Pesan Antar Makanan & UMKM Indramayu'], 'user_shell');
    }

    public function search(): void
    {
        $pdo = Database::connection();
        $q = trim((string)($_GET['q'] ?? ''));
        $catId = (int)($_GET['category_id'] ?? 0);
        $distId = (int)($_GET['district_id'] ?? 0);

        $sql = 'SELECT p.*, m.store_name, m.id AS merchant_id, d.name AS district_name,
                       (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
                FROM products p
                JOIN merchants m ON m.id=p.merchant_id
                LEFT JOIN districts d ON d.id=m.district_id
                WHERE p.is_available = 1 AND p.deleted_at IS NULL AND m.verification_status="approved"';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND (p.name LIKE ? OR m.store_name LIKE ? OR p.description LIKE ?)';
            $like = "%{$q}%";
            array_push($params, $like, $like, $like);
        }
        if ($catId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $catId;
        }
        if ($distId > 0) {
            $sql .= ' AND m.district_id = ?';
            $params[] = $distId;
        }
        $sql .= ' ORDER BY p.sold_count DESC LIMIT 50';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order')->fetchAll();
        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name')->fetchAll();

        render_view('user/search', compact('products', 'categories', 'districts', 'q', 'catId', 'distId') + [
            'pageTitle' => 'Cari Makanan & Merchant - Delivery Ku',
        ], 'user_shell');
    }

    public function merchantDetail(): void
    {
        $pdo = Database::connection();
        $mid = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare(
            'SELECT m.*, d.name AS district_name, mc.name AS category_name
             FROM merchants m
             LEFT JOIN districts d ON d.id=m.district_id
             LEFT JOIN merchant_categories mc ON mc.id=m.merchant_category_id
             WHERE m.id = ? AND m.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$mid]);
        $merchant = $stmt->fetch();
        if (!$merchant) {
            flash_set('danger', 'Merchant tidak ditemukan.');
            redirect_to('user/home');
        }

        $products = $pdo->query(
            "SELECT p.*, c.name AS category_name,
                    (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
             FROM products p LEFT JOIN categories c ON c.id=p.category_id
             WHERE p.merchant_id={$mid} AND p.deleted_at IS NULL ORDER BY p.is_available DESC, p.sold_count DESC"
        )->fetchAll();

        foreach ($products as &$p) {
            $vStmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? AND is_available = 1');
            $vStmt->execute([$p['id']]);
            $p['variants'] = $vStmt->fetchAll();
        }
        unset($p);

        $isFavorited = false;
        if (is_logged_in()) {
            $fStmt = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND merchant_id = ?');
            $fStmt->execute([current_user()['id'], $mid]);
            $isFavorited = (bool)$fStmt->fetch();
        }

        $reviews = $pdo->query(
            "SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.merchant_id={$mid} AND r.is_visible=1 ORDER BY r.id DESC LIMIT 10"
        )->fetchAll();

        render_view('user/merchant_detail', compact('merchant', 'products', 'isFavorited', 'reviews') + [
            'pageTitle' => $merchant['store_name'] . ' - Delivery Ku',
        ], 'user_shell');
    }

    public function cart(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? 'add';

            if ($action === 'add') {
                $pid = (int)$_POST['product_id'];
                $qty = max(1, (int)($_POST['quantity'] ?? 1));
                $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
                $notes = trim((string)($_POST['notes'] ?? ''));

                $pStmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND is_available = 1 LIMIT 1');
                $pStmt->execute([$pid]);
                $prod = $pStmt->fetch();
                if ($prod) {
                    $mid = (int)$prod['merchant_id'];
                    $cStmt = $pdo->prepare('SELECT id FROM carts WHERE user_id = ? AND merchant_id = ? LIMIT 1');
                    $cStmt->execute([$uid, $mid]);
                    $cartId = $cStmt->fetchColumn();
                    if (!$cartId) {
                        $pdo->prepare('INSERT INTO carts (user_id, merchant_id) VALUES (?, ?)')->execute([$uid, $mid]);
                        $cartId = (int)$pdo->lastInsertId();
                    }

                    $exStmt = $pdo->prepare('SELECT id FROM cart_items WHERE cart_id = ? AND product_id = ? AND COALESCE(variant_id, 0) = ? LIMIT 1');
                    $exStmt->execute([$cartId, $pid, $variantId ?? 0]);
                    $exId = $exStmt->fetchColumn();
                    if ($exId) {
                        $pdo->prepare('UPDATE cart_items SET quantity = quantity + ?, notes = ? WHERE id = ?')->execute([$qty, $notes, $exId]);
                    } else {
                        $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, notes) VALUES (?, ?, ?, ?, ?)')->execute([$cartId, $pid, $variantId, $qty, $notes]);
                    }
                    flash_set('success', $prod['name'] . ' ditambahkan ke keranjang.');
                }
                $backMerchant = (int)($_POST['merchant_id'] ?? 0);
                if ($backMerchant > 0 && empty($_POST['go_cart'])) {
                    redirect_to('user/merchant', ['id' => $backMerchant]);
                }
            } elseif ($action === 'update_qty') {
                $ciId = (int)$_POST['cart_item_id'];
                $qty = (int)$_POST['quantity'];
                if ($qty <= 0) {
                    $pdo->prepare('DELETE FROM cart_items WHERE id = ?')->execute([$ciId]);
                } else {
                    $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$qty, $ciId]);
                }
            } elseif ($action === 'remove') {
                $pdo->prepare('DELETE FROM cart_items WHERE id = ?')->execute([(int)$_POST['cart_item_id']]);
                flash_set('info', 'Item dihapus dari keranjang.');
            }
            redirect_to('user/cart');
        }

        $carts = $pdo->query(
            "SELECT c.*, m.store_name, m.address AS merchant_address
             FROM carts c JOIN merchants m ON m.id=c.merchant_id
             WHERE c.user_id={$uid}"
        )->fetchAll();

        foreach ($carts as &$cart) {
            $iStmt = $pdo->prepare(
                'SELECT ci.*, p.name, p.price, p.promo_price, pv.variant_name, COALESCE(pv.additional_price, 0) AS additional_price,
                        (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
                 FROM cart_items ci
                 JOIN products p ON p.id=ci.product_id
                 LEFT JOIN product_variants pv ON pv.id=ci.variant_id
                 WHERE ci.cart_id = ?'
            );
            $iStmt->execute([$cart['id']]);
            $cart['items'] = $iStmt->fetchAll();
        }
        unset($cart);

        render_view('user/cart', ['carts' => $carts, 'pageTitle' => 'Keranjang Belanja - Delivery Ku'], 'user_shell');
    }

    public function checkout(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        $merchantId = (int)($_GET['merchant_id'] ?? $_POST['merchant_id'] ?? 0);

        if ($merchantId <= 0) {
            redirect_to('user/cart');
        }

        $cStmt = $pdo->prepare('SELECT * FROM carts WHERE user_id = ? AND merchant_id = ? LIMIT 1');
        $cStmt->execute([$uid, $merchantId]);
        $cart = $cStmt->fetch();
        if (!$cart) {
            flash_set('warning', 'Keranjang untuk toko tersebut kosong.');
            redirect_to('user/cart');
        }

        $itemsStmt = $pdo->prepare(
            'SELECT ci.*, p.name, p.price, p.promo_price, pv.variant_name, COALESCE(pv.additional_price, 0) AS additional_price
             FROM cart_items ci JOIN products p ON p.id=ci.product_id LEFT JOIN product_variants pv ON pv.id=ci.variant_id
             WHERE ci.cart_id = ?'
        );
        $itemsStmt->execute([$cart['id']]);
        $items = $itemsStmt->fetchAll();
        $merchant = $pdo->query("SELECT * FROM merchants WHERE id={$merchantId}")->fetch();
        $addresses = $pdo->query("SELECT ua.*, d.name AS district_name FROM user_addresses ua LEFT JOIN districts d ON d.id=ua.district_id WHERE ua.user_id={$uid} AND ua.deleted_at IS NULL ORDER BY ua.is_default DESC")->fetchAll();
        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name')->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $addressId = (int)($_POST['address_id'] ?? 0);
            $addressData = null;
            if ($addressId > 0) {
                $aStmt = $pdo->prepare('SELECT * FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1');
                $aStmt->execute([$addressId, $uid]);
                $addressData = $aStmt->fetch();
            }
            if (!$addressData) {
                $addressData = [
                    'recipient_name' => trim((string)($_POST['recipient_name'] ?? current_user()['full_name'])),
                    'recipient_phone' => trim((string)($_POST['recipient_phone'] ?? current_user()['phone'])),
                    'district_id' => (int)($_POST['district_id'] ?? 1),
                    'full_address' => trim((string)($_POST['full_address'] ?? '')),
                    'latitude' => null,
                    'longitude' => null,
                ];
            }

            if (trim((string)$addressData['full_address']) === '') {
                flash_set('danger', 'Alamat pengiriman wajib diisi.');
                redirect_to('user/checkout', ['merchant_id' => $merchantId]);
            }

            $res = OrderService::createOrderFromCart(
                $uid,
                $merchantId,
                $addressData,
                (string)($_POST['payment_method'] ?? 'cod'),
                $_POST['voucher_code'] ?? null,
                trim((string)($_POST['customer_notes'] ?? ''))
            );
            if (!$res['ok']) {
                flash_set('danger', $res['message']);
                redirect_to('user/checkout', ['merchant_id' => $merchantId]);
            }

            flash_set('success', 'Pesanan #' . $res['order_code'] . ' berhasil dibuat!');
            redirect_to('user/order-detail', ['id' => $res['order_id']]);
        }

        $selectedDistrict = $districts[0] ?? null;
        if (!empty($addresses[0]['district_id'])) {
            foreach ($districts as $d) {
                if ((int)$d['id'] === (int)$addresses[0]['district_id']) {
                    $selectedDistrict = $d;
                    break;
                }
            }
        }
        $previewCalc = PricingService::calculate($items, $merchant, $selectedDistrict, 2.0, null);
        $bankAccounts = json_decode(get_setting('bank_accounts_json', '[]'), true) ?: [];

        render_view('user/checkout', compact('merchant', 'items', 'addresses', 'districts', 'previewCalc', 'bankAccounts') + [
            'pageTitle' => 'Checkout Pesanan - Delivery Ku',
        ], 'user_shell');
    }

    public function orders(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        $orders = $pdo->query(
            "SELECT o.*, m.store_name FROM orders o JOIN merchants m ON m.id=o.merchant_id WHERE o.user_id={$uid} ORDER BY o.id DESC"
        )->fetchAll();
        render_view('user/orders', ['orders' => $orders, 'pageTitle' => 'Pesanan Saya - Delivery Ku'], 'user_shell');
    }

    public function orderDetail(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        $oid = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare(
            'SELECT o.*, m.store_name, m.address AS merchant_address, drv.license_plate, drv_u.full_name AS driver_name, drv_u.phone AS driver_phone,
                    pay.id AS payment_id, pay.status AS payment_status, d.verification_pin
             FROM orders o
             JOIN merchants m ON m.id=o.merchant_id
             LEFT JOIN drivers drv ON drv.id=o.driver_id
             LEFT JOIN users drv_u ON drv_u.id=drv.user_id
             LEFT JOIN payments pay ON pay.order_id=o.id
             LEFT JOIN deliveries d ON d.order_id=o.id
             WHERE o.id = ? AND o.user_id = ? LIMIT 1'
        );
        $stmt->execute([$oid, $uid]);
        $order = $stmt->fetch();
        if (!$order) {
            flash_set('danger', 'Pesanan tidak ditemukan.');
            redirect_to('user/orders');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? '';
            if ($action === 'upload_proof' && !empty($_FILES['proof_image']['name'])) {
                $up = UploadService::uploadImage($_FILES['proof_image'], 'proofs');
                if ($up['ok']) {
                    $pdo->prepare('INSERT INTO payment_proofs (payment_id, user_id, proof_image_path, transfer_amount, transfer_time, sender_name, sender_bank, status) VALUES (?, ?, ?, ?, ?, ?, ?, "pending")')
                        ->execute([$order['payment_id'], $uid, $up['path'], (float)$_POST['transfer_amount'], $_POST['transfer_time'] ?: date('Y-m-d H:i:s'), trim((string)$_POST['sender_name']), trim((string)$_POST['sender_bank'])]);
                    $pdo->prepare('UPDATE payments SET status = "waiting_verification" WHERE id = ?')->execute([$order['payment_id']]);
                    flash_set('success', 'Bukti transfer berhasil diunggah dan menunggu verifikasi Admin.');
                } else {
                    flash_set('danger', $up['message']);
                }
            } elseif ($action === 'confirm_received') {
                $res = OrderService::transitionStatus($oid, 'selesai', $uid, 'user', 'Pelanggan mengonfirmasi pesanan telah diterima.');
                flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
            } elseif ($action === 'submit_review' && $order['status'] === 'selesai') {
                $pdo->prepare(
                    'INSERT INTO reviews (order_id, user_id, merchant_id, driver_id, merchant_rating, driver_rating, comment)
                     VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE merchant_rating=VALUES(merchant_rating), driver_rating=VALUES(driver_rating), comment=VALUES(comment)'
                )->execute([$oid, $uid, $order['merchant_id'], $order['driver_id'], (int)$_POST['merchant_rating'], (int)($_POST['driver_rating'] ?? 5), trim((string)$_POST['comment'])]);

                // Hitung ulang rata-rata rating merchant
                $avgM = $pdo->query("SELECT AVG(merchant_rating) AS avg_r, COUNT(*) AS cnt FROM reviews WHERE merchant_id={$order['merchant_id']}")->fetch();
                $pdo->prepare('UPDATE merchants SET rating_avg=?, total_reviews=? WHERE id=?')->execute([round((float)$avgM['avg_r'], 2), (int)$avgM['cnt'], $order['merchant_id']]);
                flash_set('success', 'Terima kasih atas penilaian dan ulasan Anda!');
            }
            redirect_to('user/order-detail', ['id' => $oid]);
        }

        $items = $pdo->query("SELECT * FROM order_items WHERE order_id={$oid}")->fetchAll();
        $histories = $pdo->query("SELECT * FROM order_status_histories WHERE order_id={$oid} ORDER BY id DESC")->fetchAll();
        $bankAccounts = json_decode(get_setting('bank_accounts_json', '[]'), true) ?: [];
        $hasReview = (bool)$pdo->query("SELECT id FROM reviews WHERE order_id={$oid} LIMIT 1")->fetchColumn();

        render_view('user/order_detail', compact('order', 'items', 'histories', 'bankAccounts', 'hasReview') + [
            'pageTitle' => 'Detail Pesanan #' . $order['order_code'],
        ], 'user_shell');
    }

    public function favorites(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $mid = (int)$_POST['merchant_id'];
            $ex = $pdo->prepare('SELECT id FROM favorites WHERE user_id=? AND merchant_id=?');
            $ex->execute([$uid, $mid]);
            if ($ex->fetch()) {
                $pdo->prepare('DELETE FROM favorites WHERE user_id=? AND merchant_id=?')->execute([$uid, $mid]);
                flash_set('info', 'Toko dihapus dari favorit.');
            } else {
                $pdo->prepare('INSERT INTO favorites (user_id, merchant_id) VALUES (?, ?)')->execute([$uid, $mid]);
                flash_set('success', 'Toko disimpan ke favorit.');
            }
            redirect_to($_POST['redirect'] ?? 'user/favorites', !empty($_POST['merchant_id']) && ($_POST['redirect'] ?? '') === 'user/merchant' ? ['id' => $mid] : []);
        }

        $favorites = $pdo->query(
            "SELECT m.*, d.name AS district_name FROM favorites f JOIN merchants m ON m.id=f.merchant_id LEFT JOIN districts d ON d.id=m.district_id WHERE f.user_id={$uid}"
        )->fetchAll();
        render_view('user/favorites', ['favorites' => $favorites, 'pageTitle' => 'Toko Favorit - Delivery Ku'], 'user_shell');
    }

    public function addresses(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? 'create';
            if ($action === 'create') {
                if (isset($_POST['is_default'])) {
                    $pdo->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
                }
                $pdo->prepare(
                    'INSERT INTO user_addresses (user_id, label, recipient_name, recipient_phone, district_id, full_address, postal_code, notes, is_default)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                )->execute([$uid, trim((string)$_POST['label']), trim((string)$_POST['recipient_name']), trim((string)$_POST['recipient_phone']), (int)$_POST['district_id'], trim((string)$_POST['full_address']), trim((string)($_POST['postal_code'] ?? '')), trim((string)($_POST['notes'] ?? '')), isset($_POST['is_default']) ? 1 : 0]);
                flash_set('success', 'Alamat pengiriman ditambahkan.');
            } elseif ($action === 'delete') {
                $pdo->prepare('UPDATE user_addresses SET deleted_at = NOW() WHERE id = ? AND user_id = ?')->execute([(int)$_POST['address_id'], $uid]);
                flash_set('info', 'Alamat dihapus.');
            }
            redirect_to('user/addresses');
        }

        $addresses = $pdo->query("SELECT ua.*, d.name AS district_name FROM user_addresses ua LEFT JOIN districts d ON d.id=ua.district_id WHERE ua.user_id={$uid} AND ua.deleted_at IS NULL ORDER BY ua.is_default DESC, ua.id DESC")->fetchAll();
        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 ORDER BY name')->fetchAll();
        render_view('user/addresses', compact('addresses', 'districts') + ['pageTitle' => 'Buku Alamat - Delivery Ku'], 'user_shell');
    }

    public function profile(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $avatarPath = null;
            if (!empty($_FILES['avatar']['name'])) {
                $up = UploadService::uploadImage($_FILES['avatar'], 'avatars');
                if ($up['ok']) {
                    $avatarPath = $up['path'];
                }
            }
            $pdo->prepare('UPDATE users SET full_name=?, phone=?, avatar_path=COALESCE(?, avatar_path) WHERE id=?')
                ->execute([trim((string)$_POST['full_name']), trim((string)$_POST['phone']), $avatarPath, $uid]);
            $_SESSION['auth_user']['full_name'] = trim((string)$_POST['full_name']);
            $_SESSION['auth_user']['phone'] = trim((string)$_POST['phone']);
            if ($avatarPath !== null) {
                $_SESSION['auth_user']['avatar_path'] = $avatarPath;
            }
            flash_set('success', 'Profil pelanggan diperbarui.');
            redirect_to('user/profile');
        }
        $notifications = $pdo->query("SELECT * FROM notifications WHERE user_id={$uid} ORDER BY id DESC LIMIT 15")->fetchAll();
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$uid]);
        render_view('user/profile', ['user' => current_user(), 'notifications' => $notifications, 'pageTitle' => 'Profil Saya - Delivery Ku'], 'user_shell');
    }

    public function help(): void
    {
        RoleMiddleware::handle(['user']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $code = 'CMP-' . date('Ymd') . '-' . random_int(100, 999);
            $pdo->prepare('INSERT INTO complaints (ticket_code, user_id, order_id, subject, description) VALUES (?, ?, ?, ?, ?)')
                ->execute([$code, $uid, !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null, trim((string)$_POST['subject']), trim((string)$_POST['description'])]);
            flash_set('success', "Tiket pengaduan #{$code} terkirim ke tim Admin.");
            redirect_to('user/help');
        }
        $orders = $pdo->query("SELECT id, order_code FROM orders WHERE user_id={$uid} ORDER BY id DESC LIMIT 20")->fetchAll();
        $complaints = $pdo->query("SELECT * FROM complaints WHERE user_id={$uid} ORDER BY id DESC")->fetchAll();
        render_view('user/help', compact('orders', 'complaints') + ['pageTitle' => 'Pusat Bantuan & Pengaduan - Delivery Ku'], 'user_shell');
    }
}
