<?php
declare(strict_types=1);

final class DriverController
{
    private function getDriver(): array
    {
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        $stmt = $pdo->prepare('SELECT drv.*, u.full_name, u.phone, u.email FROM drivers drv JOIN users u ON u.id=drv.user_id WHERE drv.user_id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $driver = $stmt->fetch();
        if (!$driver) {
            flash_set('danger', 'Data profil driver tidak ditemukan.');
            redirect_to('auth/logout');
        }
        return $driver;
    }

    public function dashboard(): void
    {
        RoleMiddleware::handle(['driver']);
        $pdo = Database::connection();
        $driver = $this->getDriver();
        $did = (int)$driver['id'];

        $availableOrders = $pdo->query(
            "SELECT o.*, m.store_name, m.address AS merchant_address, m.latitude AS m_lat, m.longitude AS m_lng
             FROM orders o JOIN merchants m ON m.id=o.merchant_id
             WHERE o.status = 'menunggu_driver' AND o.driver_id IS NULL
             ORDER BY o.id DESC"
        )->fetchAll();

        $activeDeliveries = $pdo->query(
            "SELECT o.*, m.store_name, m.address AS merchant_address, m.latitude AS m_lat, m.longitude AS m_lng,
                    u.full_name AS customer_name, u.phone AS customer_phone, d.verification_pin
             FROM orders o
             JOIN merchants m ON m.id=o.merchant_id
             JOIN users u ON u.id=o.user_id
             LEFT JOIN deliveries d ON d.order_id=o.id
             WHERE o.driver_id = {$did} AND o.status IN ('driver_menuju_merchant','pesanan_diambil','dalam_pengiriman')
             ORDER BY o.id DESC"
        )->fetchAll();

        $totalEarnings = (float)$pdo->query("SELECT COALESCE(SUM(driver_net_income),0) FROM orders WHERE driver_id={$did} AND status='selesai'")->fetchColumn();

        render_view('driver/dashboard', compact('driver', 'availableOrders', 'activeDeliveries', 'totalEarnings') + [
            'pageTitle' => 'Dashboard Kurir - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function actionOrder(): void
    {
        RoleMiddleware::handle(['driver']);
        CsrfMiddleware::verify();
        $driver = $this->getDriver();

        if ($driver['verification_status'] !== 'approved') {
            flash_set('warning', 'Akun driver Anda belum terverifikasi Admin/Superadmin.');
            redirect_to('driver/dashboard');
        }

        $action = $_POST['action'] ?? '';
        $oid = (int)($_POST['order_id'] ?? 0);
        $uid = (int)current_user()['id'];
        $did = (int)$driver['id'];

        if ($action === 'toggle_online') {
            Database::connection()->prepare('UPDATE drivers SET is_online = NOT is_online WHERE id = ?')->execute([$did]);
            flash_set('success', 'Status ketersediaan online/offline diperbarui.');
            redirect_to('driver/dashboard');
        }

        if ($action === 'accept') {
            $res = OrderService::transitionStatus($oid, 'driver_menuju_merchant', $uid, 'driver', 'Driver menerima pesanan dan menuju ke toko.', $did);
            flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
        } elseif ($action === 'pickup') {
            $res = OrderService::transitionStatus($oid, 'pesanan_diambil', $uid, 'driver', 'Driver sudah mengambil pesanan di merchant.');
            flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
        } elseif ($action === 'delivering') {
            $res = OrderService::transitionStatus($oid, 'dalam_pengiriman', $uid, 'driver', 'Pesanan sedang dalam perjalanan ke lokasi pelanggan.');
            flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
        } elseif ($action === 'complete') {
            $inputPin = trim((string)($_POST['verification_pin'] ?? ''));
            $pinRow = Database::connection()->prepare('SELECT verification_pin FROM deliveries WHERE order_id = ?');
            $pinRow->execute([$oid]);
            $realPin = (string)$pinRow->fetchColumn();
            if ($realPin !== '' && $inputPin !== '' && $inputPin !== $realPin) {
                flash_set('danger', 'PIN verifikasi penerimaan dari pelanggan tidak cocok.');
                redirect_to('driver/dashboard');
            }
            $res = OrderService::transitionStatus($oid, 'selesai', $uid, 'driver', 'Pesanan diserahkan ke pelanggan dan terverifikasi.');
            flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
        }

        redirect_to('driver/dashboard');
    }

    public function deliveriesAndEarnings(): void
    {
        RoleMiddleware::handle(['driver']);
        $pdo = Database::connection();
        $driver = $this->getDriver();
        $did = (int)$driver['id'];

        $history = $pdo->query(
            "SELECT o.*, m.store_name, u.full_name AS customer_name
             FROM orders o JOIN merchants m ON m.id=o.merchant_id JOIN users u ON u.id=o.user_id
             WHERE o.driver_id = {$did} ORDER BY o.id DESC LIMIT 100"
        )->fetchAll();

        $totalEarnings = (float)$pdo->query("SELECT COALESCE(SUM(driver_net_income),0) FROM orders WHERE driver_id={$did} AND status='selesai'")->fetchColumn();
        render_view('driver/deliveries', compact('driver', 'history', 'totalEarnings') + [
            'pageTitle' => 'Riwayat Pengantaran & Penghasilan - Driver Delivery Ku',
        ], 'dashboard_shell');
    }

    public function profile(): void
    {
        RoleMiddleware::handle(['driver']);
        $pdo = Database::connection();
        $driver = $this->getDriver();
        $did = (int)$driver['id'];
        $uid = (int)current_user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $pdo->prepare('UPDATE users SET phone = ? WHERE id = ?')->execute([trim((string)$_POST['phone']), $uid]);
            $pdo->prepare('UPDATE drivers SET vehicle_type = ?, vehicle_brand = ?, license_plate = ?, district_id = ? WHERE id = ?')
                ->execute([$_POST['vehicle_type'], trim((string)$_POST['vehicle_brand']), strtoupper(trim((string)$_POST['license_plate'])), (int)$_POST['district_id'], $did]);

            if (!empty($_FILES['doc_file']['name'])) {
                $up = UploadService::uploadImage($_FILES['doc_file'], 'documents');
                if ($up['ok']) {
                    $pdo->prepare('INSERT INTO driver_documents (driver_id, doc_type, file_path, status) VALUES (?, ?, ?, "pending")')
                        ->execute([$did, $_POST['doc_type'] ?? 'sim', $up['path']]);
                }
            }
            flash_set('success', 'Profil & dokumen driver berhasil disimpan.');
            redirect_to('driver/profile');
        }

        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 ORDER BY name')->fetchAll();
        $docs = $pdo->query("SELECT * FROM driver_documents WHERE driver_id={$did} ORDER BY id DESC")->fetchAll();
        render_view('driver/profile', compact('driver', 'districts', 'docs') + [
            'pageTitle' => 'Profil & Verifikasi Driver - Delivery Ku',
        ], 'dashboard_shell');
    }
}
