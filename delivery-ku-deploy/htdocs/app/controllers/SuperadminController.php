<?php
declare(strict_types=1);

final class SuperadminController
{
    public function dashboard(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();
        $includeDemo = get_setting('include_demo_in_reports', '0') === '1';
        $demoFilter = $includeDemo ? '' : ' AND is_demo = 0';
        $ordDemoFilter = $includeDemo ? '' : ' AND o.is_demo = 0';

        $stats = [
            'total_customers' => (int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='user' AND u.deleted_at IS NULL")->fetchColumn(),
            'active_merchants' => (int)$pdo->query("SELECT COUNT(*) FROM merchants WHERE verification_status='approved' AND deleted_at IS NULL")->fetchColumn(),
            'active_drivers' => (int)$pdo->query("SELECT COUNT(*) FROM drivers WHERE verification_status='approved' AND deleted_at IS NULL")->fetchColumn(),
            'orders_today' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE(){$demoFilter}")->fetchColumn(),
            'orders_waiting' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('menunggu_pembayaran','menunggu_konfirmasi_merchant'){$demoFilter}")->fetchColumn(),
            'orders_processing' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pesanan_dikonfirmasi','sedang_disiapkan','menunggu_driver'){$demoFilter}")->fetchColumn(),
            'orders_delivering' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('driver_menuju_merchant','pesanan_diambil','dalam_pengiriman'){$demoFilter}")->fetchColumn(),
            'orders_completed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='selesai'{$demoFilter}")->fetchColumn(),
            'orders_cancelled' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('dibatalkan','pengembalian_dana'){$demoFilter}")->fetchColumn(),
            'transactions_today' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status='selesai'{$demoFilter}")->fetchColumn(),
            'platform_revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='selesai'{$demoFilter}")->fetchColumn(),
            'platform_commission' => (float)$pdo->query("SELECT COALESCE(SUM(platform_commission + service_fee),0) FROM orders WHERE status='selesai'{$demoFilter}")->fetchColumn(),
        ];

        // Grafik 7 hari terakhir
        $chartTrx = $pdo->query(
            "SELECT DATE(created_at) AS tgl, COUNT(*) AS total_orders, COALESCE(SUM(total_amount),0) AS omzet,
                    COALESCE(SUM(platform_commission + service_fee),0) AS komisi
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY){$demoFilter}
             GROUP BY DATE(created_at)
             ORDER BY tgl ASC"
        )->fetchAll();

        // Grafik bulanan
        $chartMonthly = $pdo->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS bulan, COALESCE(SUM(total_amount),0) AS omzet,
                    COALESCE(SUM(platform_commission + service_fee),0) AS komisi
             FROM orders WHERE status='selesai'{$demoFilter}
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY bulan DESC LIMIT 6"
        )->fetchAll();

        // Komposisi pengguna per role
        $userComposition = $pdo->query(
            "SELECT r.name AS role_name, COUNT(u.id) AS total
             FROM roles r LEFT JOIN users u ON u.role_id=r.id AND u.deleted_at IS NULL
             GROUP BY r.id, r.name ORDER BY r.id ASC"
        )->fetchAll();

        $recentOrders = $pdo->query(
            "SELECT o.*, u.full_name AS customer_name, m.store_name
             FROM orders o
             JOIN users u ON u.id=o.user_id
             JOIN merchants m ON m.id=o.merchant_id
             WHERE 1=1{$ordDemoFilter}
             ORDER BY o.id DESC LIMIT 8"
        )->fetchAll();

        $recentLogs = $pdo->query(
            "SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.id DESC LIMIT 8"
        )->fetchAll();

        $topMerchants = $pdo->query(
            "SELECT m.store_name, COUNT(o.id) AS total_orders, COALESCE(SUM(o.merchant_net_income),0) AS total_net
             FROM merchants m
             LEFT JOIN orders o ON o.merchant_id=m.id AND o.status='selesai'{$ordDemoFilter}
             WHERE m.deleted_at IS NULL
             GROUP BY m.id, m.store_name ORDER BY total_orders DESC LIMIT 5"
        )->fetchAll();

        $topDrivers = $pdo->query(
            "SELECT u.full_name, d.license_plate, d.total_deliveries, d.rating_avg
             FROM drivers d JOIN users u ON u.id=d.user_id
             WHERE d.deleted_at IS NULL ORDER BY d.total_deliveries DESC LIMIT 5"
        )->fetchAll();

        render_view('superadmin/dashboard', compact(
            'stats', 'chartTrx', 'chartMonthly', 'userComposition',
            'recentOrders', 'recentLogs', 'topMerchants', 'topDrivers', 'includeDemo'
        ) + ['pageTitle' => 'Dashboard Superadmin - Delivery Ku'], 'dashboard_shell');
    }

    public function users(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $hash = password_hash((string)($_POST['password'] ?? 'DeliveryKu2026!'), PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO users (role_id, full_name, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, "active")');
                $stmt->execute([(int)$_POST['role_id'], trim((string)$_POST['full_name']), strtolower(trim((string)$_POST['email'])), trim((string)$_POST['phone']), $hash]);
                $newUid = (int)$pdo->lastInsertId();
                if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                    $pStmt = $pdo->prepare('INSERT IGNORE INTO admin_user_permissions (user_id, permission_id) VALUES (?, ?)');
                    foreach ($_POST['permissions'] as $pid) {
                        $pStmt->execute([$newUid, (int)$pid]);
                    }
                }
                log_activity('CREATE_USER', 'users', 'Superadmin membuat akun baru #' . $newUid);
                flash_set('success', 'Akun pengguna berhasil dibuat.');
            } elseif ($action === 'toggle_status') {
                $uid = (int)$_POST['user_id'];
                $newStatus = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
                $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND id != ?')->execute([$newStatus, $uid, current_user()['id']]);
                log_activity('TOGGLE_USER', 'users', "Status akun #{$uid} diubah menjadi {$newStatus}");
                flash_set('success', 'Status akun diperbarui.');
            } elseif ($action === 'reset_password') {
                $uid = (int)$_POST['user_id'];
                $newPass = trim((string)($_POST['new_password'] ?? 'DeliveryKu2026!'));
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($newPass, PASSWORD_BCRYPT), $uid]);
                log_activity('RESET_PASSWORD', 'users', "Reset kata sandi akun #{$uid}");
                flash_set('success', 'Kata sandi pengguna berhasil direset.');
            } elseif ($action === 'update_admin_perms') {
                $uid = (int)$_POST['user_id'];
                $pdo->prepare('DELETE FROM admin_user_permissions WHERE user_id = ?')->execute([$uid]);
                $pStmt = $pdo->prepare('INSERT INTO admin_user_permissions (user_id, permission_id) VALUES (?, ?)');
                foreach (($_POST['permissions'] ?? []) as $pid) {
                    $pStmt->execute([$uid, (int)$pid]);
                }
                log_activity('UPDATE_ADMIN_PERMS', 'users', "Hak akses Admin #{$uid} diperbarui.");
                flash_set('success', 'Hak akses Admin berhasil disimpan.');
            } elseif ($action === 'delete') {
                $uid = (int)$_POST['user_id'];
                $pdo->prepare('UPDATE users SET deleted_at = NOW(), status = "inactive" WHERE id = ? AND id != ?')->execute([$uid, current_user()['id']]);
                log_activity('DELETE_USER', 'users', "Soft delete akun #{$uid}");
                flash_set('success', 'Akun pengguna dihapus.');
            }
            redirect_to('superadmin/users', ['role' => $_GET['role'] ?? '']);
        }

        $roleFilter = trim((string)($_GET['role'] ?? ''));
        $search = trim((string)($_GET['q'] ?? ''));
        $sql = 'SELECT u.*, r.slug AS role_slug, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.deleted_at IS NULL';
        $params = [];
        if ($roleFilter !== '') {
            $sql .= ' AND r.slug = ?';
            $params[] = $roleFilter;
        }
        if ($search !== '') {
            $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            $like = "%{$search}%";
            array_push($params, $like, $like, $like);
        }
        $sql .= ' ORDER BY u.id DESC LIMIT 100';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $roles = $pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
        $permissions = $pdo->query('SELECT * FROM permissions ORDER BY id')->fetchAll();
        $adminPermsRaw = $pdo->query('SELECT user_id, permission_id FROM admin_user_permissions')->fetchAll();
        $adminPerms = [];
        foreach ($adminPermsRaw as $ap) {
            $adminPerms[$ap['user_id']][] = (int)$ap['permission_id'];
        }

        render_view('superadmin/users', compact('users', 'roles', 'permissions', 'adminPerms', 'roleFilter', 'search') + [
            'pageTitle' => 'Manajemen Pengguna & Hak Akses - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function merchants(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $mid = (int)($_POST['merchant_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            if ($action === 'verify') {
                $status = in_array($_POST['status'] ?? '', ['approved', 'rejected', 'pending'], true) ? $_POST['status'] : 'pending';
                $notes = trim((string)($_POST['notes'] ?? ''));
                $pdo->prepare('UPDATE merchants SET verification_status = ?, verification_notes = ? WHERE id = ?')->execute([$status, $notes, $mid]);
                $mUser = (int)$pdo->query("SELECT user_id FROM merchants WHERE id={$mid}")->fetchColumn();
                notify_user($mUser, 'Status Verifikasi Merchant', "Status verifikasi toko Anda: {$status}. {$notes}", route_url('merchant/dashboard'));
                log_activity('VERIFY_MERCHANT', 'merchants', "Merchant #{$mid} -> {$status}");
                flash_set('success', 'Status verifikasi merchant diperbarui.');
            } elseif ($action === 'commission') {
                $rate = $_POST['commission_rate'] !== '' ? (float)$_POST['commission_rate'] : null;
                $rec = isset($_POST['is_recommended']) ? 1 : 0;
                $pdo->prepare('UPDATE merchants SET commission_rate = ?, is_recommended = ? WHERE id = ?')->execute([$rate, $rec, $mid]);
                log_activity('MERCHANT_COMMISSION', 'merchants', "Komisi khusus merchant #{$mid} diperbarui.");
                flash_set('success', 'Pengaturan merchant disimpan.');
            }
            redirect_to('superadmin/merchants');
        }

        $merchants = $pdo->query(
            'SELECT m.*, u.full_name AS owner_name, u.email, u.phone, d.name AS district_name, mc.name AS category_name
             FROM merchants m
             JOIN users u ON u.id=m.user_id
             LEFT JOIN districts d ON d.id=m.district_id
             LEFT JOIN merchant_categories mc ON mc.id=m.merchant_category_id
             WHERE m.deleted_at IS NULL ORDER BY m.id DESC'
        )->fetchAll();

        render_view('superadmin/merchants', ['merchants' => $merchants, 'pageTitle' => 'Manajemen Merchant - Delivery Ku'], 'dashboard_shell');
    }

    public function drivers(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $did = (int)($_POST['driver_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['approved', 'rejected', 'pending'], true) ? $_POST['status'] : 'pending';
            $notes = trim((string)($_POST['notes'] ?? ''));
            $pdo->prepare('UPDATE drivers SET verification_status = ?, verification_notes = ? WHERE id = ?')->execute([$status, $notes, $did]);
            $dUser = (int)$pdo->query("SELECT user_id FROM drivers WHERE id={$did}")->fetchColumn();
            notify_user($dUser, 'Status Verifikasi Driver', "Status verifikasi kurir Anda: {$status}. {$notes}", route_url('driver/dashboard'));
            log_activity('VERIFY_DRIVER', 'drivers', "Driver #{$did} -> {$status}");
            flash_set('success', 'Status verifikasi driver berhasil diperbarui.');
            redirect_to('superadmin/drivers');
        }

        $drivers = $pdo->query(
            'SELECT drv.*, u.full_name, u.email, u.phone, d.name AS district_name
             FROM drivers drv
             JOIN users u ON u.id=drv.user_id
             LEFT JOIN districts d ON d.id=drv.district_id
             WHERE drv.deleted_at IS NULL ORDER BY drv.id DESC'
        )->fetchAll();

        render_view('superadmin/drivers', ['drivers' => $drivers, 'pageTitle' => 'Manajemen Driver - Delivery Ku'], 'dashboard_shell');
    }

    public function categoriesAndProducts(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? '';
            if ($action === 'save_category') {
                $name = trim((string)$_POST['name']);
                $icon = trim((string)($_POST['icon'] ?? 'bi-bag'));
                $pdo->prepare('INSERT INTO categories (name, slug, icon, is_active) VALUES (?, ?, ?, 1)')->execute([$name, slugify($name) . '-' . random_int(10, 99), $icon]);
                flash_set('success', 'Kategori produk ditambahkan.');
            } elseif ($action === 'toggle_product') {
                $pid = (int)$_POST['product_id'];
                $avail = (int)$_POST['is_available'];
                $pdo->prepare('UPDATE products SET is_available = ? WHERE id = ?')->execute([$avail, $pid]);
                flash_set('success', 'Status ketersediaan produk diperbarui.');
            }
            redirect_to('superadmin/products');
        }

        $categories = $pdo->query('SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY sort_order, id')->fetchAll();
        $products = $pdo->query(
            'SELECT p.*, m.store_name, c.name AS category_name
             FROM products p
             JOIN merchants m ON m.id=p.merchant_id
             LEFT JOIN categories c ON c.id=p.category_id
             WHERE p.deleted_at IS NULL ORDER BY p.id DESC LIMIT 100'
        )->fetchAll();

        render_view('superadmin/products', compact('categories', 'products') + ['pageTitle' => 'Kategori & Produk - Delivery Ku'], 'dashboard_shell');
    }

    public function ordersAndDeliveries(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $oid = (int)$_POST['order_id'];
            $toStatus = (string)$_POST['to_status'];
            $notes = trim((string)($_POST['notes'] ?? 'Pembaruan oleh Superadmin'));
            $driverId = !empty($_POST['driver_id']) ? (int)$_POST['driver_id'] : null;
            $res = OrderService::transitionStatus($oid, $toStatus, (int)current_user()['id'], 'superadmin', $notes, $driverId);
            flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
            redirect_to('superadmin/orders');
        }

        $statusFilter = trim((string)($_GET['status'] ?? ''));
        $sql = 'SELECT o.*, u.full_name AS customer_name, m.store_name, drv_u.full_name AS driver_name, pay.status AS payment_status
                FROM orders o
                JOIN users u ON u.id=o.user_id
                JOIN merchants m ON m.id=o.merchant_id
                LEFT JOIN drivers drv ON drv.id=o.driver_id
                LEFT JOIN users drv_u ON drv_u.id=drv.user_id
                LEFT JOIN payments pay ON pay.order_id=o.id
                WHERE o.deleted_at IS NULL';
        $params = [];
        if ($statusFilter !== '') {
            $sql .= ' AND o.status = ?';
            $params[] = $statusFilter;
        }
        $sql .= ' ORDER BY o.id DESC LIMIT 100';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $activeDrivers = $pdo->query(
            'SELECT d.id, u.full_name, d.license_plate FROM drivers d JOIN users u ON u.id=d.user_id WHERE d.verification_status="approved" AND d.deleted_at IS NULL'
        )->fetchAll();

        render_view('superadmin/orders', compact('orders', 'activeDrivers', 'statusFilter') + [
            'pageTitle' => 'Manajemen Pesanan & Pengiriman - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function paymentsAndCommissions(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $payId = (int)$_POST['payment_id'];
            $decision = $_POST['decision'] === 'verified' ? 'verified' : 'rejected';
            $notes = trim((string)($_POST['notes'] ?? ''));

            $pRow = $pdo->prepare('SELECT * FROM payments WHERE id = ? LIMIT 1');
            $pRow->execute([$payId]);
            $payment = $pRow->fetch();
            if ($payment) {
                $pdo->prepare('UPDATE payments SET status = ?, verified_by = ?, verified_at = NOW(), paid_at = NOW(), verification_notes = ? WHERE id = ?')
                    ->execute([$decision, current_user()['id'], $notes, $payId]);
                $pdo->prepare('UPDATE payment_proofs SET status = ?, admin_notes = ? WHERE payment_id = ?')
                    ->execute([$decision, $notes, $payId]);

                if ($decision === 'verified') {
                    OrderService::transitionStatus((int)$payment['order_id'], 'menunggu_konfirmasi_merchant', (int)current_user()['id'], 'superadmin', 'Pembayaran manual diverifikasi.');
                }
                flash_set('success', 'Status pembayaran diperbarui: ' . $decision);
            }
            redirect_to('superadmin/payments');
        }

        $payments = $pdo->query(
            'SELECT p.*, o.order_code, o.total_amount, u.full_name AS customer_name,
                    pp.proof_image_path, pp.transfer_amount, pp.transfer_time, pp.sender_name, pp.sender_bank
             FROM payments p
             JOIN orders o ON o.id=p.order_id
             JOIN users u ON u.id=o.user_id
             LEFT JOIN payment_proofs pp ON pp.payment_id=p.id
             ORDER BY p.id DESC LIMIT 100'
        )->fetchAll();

        $commissions = $pdo->query(
            'SELECT c.*, o.order_code, o.is_demo, m.store_name
             FROM commissions c
             JOIN orders o ON o.id=c.order_id
             JOIN merchants m ON m.id=c.merchant_id
             ORDER BY c.id DESC LIMIT 100'
        )->fetchAll();

        render_view('superadmin/payments', compact('payments', 'commissions') + [
            'pageTitle' => 'Pembayaran & Komisi - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function vouchers(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? 'create';
            if ($action === 'create') {
                $stmt = $pdo->prepare(
                    'INSERT INTO vouchers (code, title, description, discount_type, discount_value, max_discount, min_transaction, quota, start_date, end_date, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([
                    strtoupper(trim((string)$_POST['code'])),
                    trim((string)$_POST['title']),
                    trim((string)($_POST['description'] ?? '')),
                    $_POST['discount_type'] === 'percent' ? 'percent' : 'fixed',
                    (float)$_POST['discount_value'],
                    !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null,
                    (float)($_POST['min_transaction'] ?? 0),
                    (int)($_POST['quota'] ?? 100),
                    $_POST['start_date'] . ' 00:00:00',
                    $_POST['end_date'] . ' 23:59:59',
                ]);
                flash_set('success', 'Voucher berhasil ditambahkan.');
            } elseif ($action === 'toggle') {
                $pdo->prepare('UPDATE vouchers SET is_active = NOT is_active WHERE id = ?')->execute([(int)$_POST['voucher_id']]);
                flash_set('success', 'Status voucher diperbarui.');
            }
            redirect_to('superadmin/vouchers');
        }

        $vouchers = $pdo->query('SELECT * FROM vouchers WHERE deleted_at IS NULL ORDER BY id DESC')->fetchAll();
        render_view('superadmin/vouchers', ['vouchers' => $vouchers, 'pageTitle' => 'Manajemen Voucher - Delivery Ku'], 'dashboard_shell');
    }

    public function regions(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? '';
            if ($action === 'create_district') {
                $stmt = $pdo->prepare(
                    'INSERT INTO districts (regency_name, province_name, name, postal_code, latitude, longitude, base_delivery_fee, per_km_fee, is_active)
                     VALUES (?, "Jawa Barat", ?, ?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([
                    trim((string)($_POST['regency_name'] ?? 'Kabupaten Indramayu')),
                    trim((string)$_POST['name']),
                    trim((string)($_POST['postal_code'] ?? '')),
                    !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                    !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                    (float)($_POST['base_delivery_fee'] ?? 6000),
                    (float)($_POST['per_km_fee'] ?? 2500),
                ]);
                flash_set('success', 'Kecamatan operasional baru berhasil ditambahkan.');
            } elseif ($action === 'create_village') {
                $stmt = $pdo->prepare('INSERT INTO villages (district_id, name, postal_code, latitude, longitude, is_active) VALUES (?, ?, ?, ?, ?, 1)');
                $stmt->execute([
                    (int)$_POST['district_id'],
                    trim((string)$_POST['name']),
                    trim((string)($_POST['postal_code'] ?? '')),
                    !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                    !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                ]);
                flash_set('success', 'Desa/Kelurahan berhasil ditambahkan.');
            } elseif ($action === 'toggle_district') {
                $pdo->prepare('UPDATE districts SET is_active = NOT is_active WHERE id = ?')->execute([(int)$_POST['district_id']]);
                flash_set('success', 'Status wilayah kecamatan diperbarui.');
            }
            redirect_to('superadmin/regions');
        }

        $districts = $pdo->query('SELECT * FROM districts WHERE deleted_at IS NULL ORDER BY name')->fetchAll();
        $villages = $pdo->query('SELECT v.*, d.name AS district_name FROM villages v JOIN districts d ON d.id=v.district_id WHERE v.deleted_at IS NULL ORDER BY d.name, v.name')->fetchAll();
        render_view('superadmin/regions', compact('districts', 'villages') + ['pageTitle' => 'Wilayah Operasional Indramayu - Delivery Ku'], 'dashboard_shell');
    }

    public function bannersAndReviews(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $action = $_POST['action'] ?? '';
            if ($action === 'create_banner') {
                $imgPath = null;
                if (!empty($_FILES['image']['name'])) {
                    $up = UploadService::uploadImage($_FILES['image'], 'merchants');
                    if ($up['ok']) {
                        $imgPath = $up['path'];
                    }
                }
                $pdo->prepare('INSERT INTO banners (title, subtitle, image_path, badge_text, target_url, is_active) VALUES (?, ?, ?, ?, ?, 1)')
                    ->execute([trim((string)$_POST['title']), trim((string)$_POST['subtitle']), $imgPath, trim((string)($_POST['badge_text'] ?? 'PROMO')), trim((string)($_POST['target_url'] ?? '?route=user/search'))]);
                flash_set('success', 'Banner promosi ditambahkan.');
            } elseif ($action === 'toggle_banner') {
                $pdo->prepare('UPDATE banners SET is_active = NOT is_active WHERE id = ?')->execute([(int)$_POST['banner_id']]);
                flash_set('success', 'Status banner diperbarui.');
            } elseif ($action === 'toggle_review') {
                $pdo->prepare('UPDATE reviews SET is_visible = NOT is_visible WHERE id = ?')->execute([(int)$_POST['review_id']]);
                flash_set('success', 'Visibilitas ulasan diperbarui.');
            }
            redirect_to('superadmin/banners');
        }

        $banners = $pdo->query('SELECT * FROM banners ORDER BY sort_order, id DESC')->fetchAll();
        $reviews = $pdo->query(
            'SELECT r.*, u.full_name AS customer_name, m.store_name, o.order_code
             FROM reviews r JOIN users u ON u.id=r.user_id JOIN merchants m ON m.id=r.merchant_id JOIN orders o ON o.id=r.order_id
             ORDER BY r.id DESC LIMIT 50'
        )->fetchAll();

        render_view('superadmin/banners', compact('banners', 'reviews') + ['pageTitle' => 'Banner Promosi & Ulasan - Delivery Ku'], 'dashboard_shell');
    }

    public function reports(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        $scope = $_GET['scope'] ?? 'production'; // 'production' | 'demo' | 'all'
        $whereDemo = match ($scope) {
            'demo' => ' AND o.is_demo = 1',
            'all' => '',
            default => ' AND o.is_demo = 0',
        };

        $orders = $pdo->query(
            "SELECT o.*, u.full_name AS customer_name, m.store_name
             FROM orders o
             JOIN users u ON u.id=o.user_id
             JOIN merchants m ON m.id=o.merchant_id
             WHERE o.deleted_at IS NULL{$whereDemo}
             ORDER BY o.id DESC LIMIT 200"
        )->fetchAll();

        $summaryStmt = $pdo->query(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(CASE WHEN o.status='selesai' THEN o.total_amount ELSE 0 END),0) AS gross_gmv,
                    COALESCE(SUM(CASE WHEN o.status='selesai' THEN o.platform_commission + o.service_fee ELSE 0 END),0) AS platform_income,
                    COALESCE(SUM(CASE WHEN o.status='selesai' THEN o.merchant_net_income ELSE 0 END),0) AS merchant_income,
                    COALESCE(SUM(CASE WHEN o.status='selesai' THEN o.driver_net_income ELSE 0 END),0) AS driver_income
             FROM orders o WHERE o.deleted_at IS NULL{$whereDemo}"
        );
        $summary = $summaryStmt->fetch();

        $export = $_GET['export'] ?? '';
        if ($export === 'csv' || $export === 'pdf') {
            $headers = ['Kode Pesanan', 'Tanggal', 'Pelanggan', 'Merchant', 'Subtotal', 'Ongkir', 'Biaya Layanan', 'Komisi Platform', 'Total Bayar', 'Status', 'Tipe Data'];
            $rows = array_map(fn($r) => [
                $r['order_code'],
                $r['created_at'],
                $r['customer_name'],
                $r['store_name'],
                $r['subtotal'],
                $r['delivery_fee'],
                $r['service_fee'],
                $r['platform_commission'],
                $r['total_amount'],
                order_status_label($r['status']),
                ((int)$r['is_demo'] === 1 ? 'DEMO UJI' : 'PRODUKSI'),
            ], $orders);

            if ($export === 'csv') {
                ExportService::downloadCsv('laporan_deliveryku_' . date('Ymd') . '.csv', $headers, $rows);
            }
            ExportService::renderPrintableReport('Laporan Keuangan & Transaksi (' . strtoupper($scope) . ')', $headers, $rows, [
                'Total Pesanan' => $summary['total_orders'],
                'Total GMV Selesai' => rupiah($summary['gross_gmv']),
                'Pendapatan Platform' => rupiah($summary['platform_income']),
                'Pendapatan Merchant' => rupiah($summary['merchant_income']),
            ]);
        }

        render_view('superadmin/reports', compact('orders', 'summary', 'scope') + ['pageTitle' => 'Laporan Keuangan & Transaksi - Delivery Ku'], 'dashboard_shell');
    }

    public function settings(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $stmt = $pdo->prepare('INSERT INTO application_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach (($_POST['settings'] ?? []) as $k => $v) {
                $stmt->execute([trim((string)$k), trim((string)$v)]);
            }
            log_activity('UPDATE_SETTINGS', 'settings', 'Superadmin memperbarui konfigurasi sistem.');
            flash_set('success', 'Pengaturan sistem berhasil disimpan.');
            redirect_to('superadmin/settings');
        }

        $rows = $pdo->query('SELECT * FROM application_settings ORDER BY setting_group, id')->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        render_view('superadmin/settings', ['settings' => $settings, 'pageTitle' => 'Pengaturan Aplikasi & Tarif - Delivery Ku'], 'dashboard_shell');
    }

    public function logs(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();
        $logs = $pdo->query('SELECT al.*, u.full_name, u.email FROM activity_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.id DESC LIMIT 150')->fetchAll();
        render_view('superadmin/logs', ['logs' => $logs, 'pageTitle' => 'Log Aktivitas Sistem - Delivery Ku'], 'dashboard_shell');
    }

    public function backup(): void
    {
        RoleMiddleware::handle(['superadmin']);
        if (isset($_GET['download']) && $_GET['download'] === '1') {
            BackupService::downloadSqlBackup();
        }
        $tables = Database::connection()->query('SHOW TABLE STATUS')->fetchAll();
        render_view('superadmin/backup', ['tables' => $tables, 'pageTitle' => 'Backup Database SQL - Delivery Ku'], 'dashboard_shell');
    }

    public function profile(): void
    {
        RoleMiddleware::handle(['superadmin']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?')->execute([trim((string)$_POST['full_name']), trim((string)$_POST['phone']), $uid]);
            if (!empty($_POST['new_password'])) {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash((string)$_POST['new_password'], PASSWORD_BCRYPT), $uid]);
            }
            $_SESSION['auth_user']['full_name'] = trim((string)$_POST['full_name']);
            $_SESSION['auth_user']['phone'] = trim((string)$_POST['phone']);
            flash_set('success', 'Profil Superadmin diperbarui.');
            redirect_to('superadmin/profile');
        }
        render_view('superadmin/profile', ['user' => current_user(), 'pageTitle' => 'Profil Superadmin - Delivery Ku'], 'dashboard_shell');
    }
}
