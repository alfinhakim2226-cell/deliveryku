<?php
declare(strict_types=1);

final class AdminController
{
    public function dashboard(): void
    {
        RoleMiddleware::handle(['admin'], 'view_dashboard');
        $pdo = Database::connection();

        $summary = [
            'pending_merchants' => (int)$pdo->query("SELECT COUNT(*) FROM merchants WHERE verification_status='pending' AND deleted_at IS NULL")->fetchColumn(),
            'pending_drivers' => (int)$pdo->query("SELECT COUNT(*) FROM drivers WHERE verification_status='pending' AND deleted_at IS NULL")->fetchColumn(),
            'pending_payments' => (int)$pdo->query("SELECT COUNT(*) FROM payments WHERE status='waiting_verification'")->fetchColumn(),
            'open_complaints' => (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('open','in_progress')")->fetchColumn(),
            'active_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('selesai','dibatalkan','pengembalian_dana') AND is_demo = 0")->fetchColumn(),
        ];

        $recentOrders = $pdo->query(
            'SELECT o.*, u.full_name AS customer_name, m.store_name
             FROM orders o JOIN users u ON u.id=o.user_id JOIN merchants m ON m.id=o.merchant_id
             ORDER BY o.id DESC LIMIT 10'
        )->fetchAll();

        render_view('admin/dashboard', compact('summary', 'recentOrders') + [
            'pageTitle' => 'Dashboard Admin Operasional - Delivery Ku',
        ], 'dashboard_shell');
    }

    public function verifications(): void
    {
        RoleMiddleware::handle(['admin']);
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $type = $_POST['type'] ?? '';
            $status = in_array($_POST['status'] ?? '', ['approved', 'rejected'], true) ? $_POST['status'] : 'pending';
            $notes = trim((string)($_POST['notes'] ?? ''));

            if ($type === 'merchant' && has_permission('verify_merchants')) {
                $mid = (int)$_POST['id'];
                $pdo->prepare('UPDATE merchants SET verification_status = ?, verification_notes = ? WHERE id = ?')->execute([$status, $notes, $mid]);
                log_activity('ADMIN_VERIFY_MERCHANT', 'merchants', "Admin verifikasi merchant #{$mid} -> {$status}");
                flash_set('success', 'Verifikasi merchant berhasil disimpan.');
            } elseif ($type === 'driver' && has_permission('verify_drivers')) {
                $did = (int)$_POST['id'];
                $pdo->prepare('UPDATE drivers SET verification_status = ?, verification_notes = ? WHERE id = ?')->execute([$status, $notes, $did]);
                log_activity('ADMIN_VERIFY_DRIVER', 'drivers', "Admin verifikasi driver #{$did} -> {$status}");
                flash_set('success', 'Verifikasi driver berhasil disimpan.');
            } elseif ($type === 'payment' && has_permission('verify_payments')) {
                $payId = (int)$_POST['id'];
                $dec = $status === 'approved' ? 'verified' : 'rejected';
                $pdo->prepare('UPDATE payments SET status = ?, verified_by = ?, verified_at = NOW(), paid_at = NOW(), verification_notes = ? WHERE id = ?')
                    ->execute([$dec, current_user()['id'], $notes, $payId]);
                $orderId = (int)$pdo->query("SELECT order_id FROM payments WHERE id={$payId}")->fetchColumn();
                if ($dec === 'verified' && $orderId > 0) {
                    OrderService::transitionStatus($orderId, 'menunggu_konfirmasi_merchant', (int)current_user()['id'], 'admin', 'Bukti transfer diverifikasi Admin.');
                }
                flash_set('success', 'Verifikasi pembayaran diperbarui.');
            }
            redirect_to('admin/verifications');
        }

        $merchants = $pdo->query('SELECT m.*, u.full_name, u.phone FROM merchants m JOIN users u ON u.id=m.user_id WHERE m.deleted_at IS NULL ORDER BY m.verification_status="pending" DESC, m.id DESC')->fetchAll();
        $drivers = $pdo->query('SELECT d.*, u.full_name, u.phone FROM drivers d JOIN users u ON u.id=d.user_id WHERE d.deleted_at IS NULL ORDER BY d.verification_status="pending" DESC, d.id DESC')->fetchAll();
        $payments = $pdo->query('SELECT p.*, o.order_code, pp.proof_image_path, pp.transfer_amount, pp.sender_name, pp.transfer_time FROM payments p JOIN orders o ON o.id=p.order_id LEFT JOIN payment_proofs pp ON pp.payment_id=p.id WHERE p.method IN ("manual_proof","bank_transfer") ORDER BY p.id DESC LIMIT 30')->fetchAll();

        render_view('admin/verifications', compact('merchants', 'drivers', 'payments') + [
            'pageTitle' => 'Verifikasi Mitra & Pembayaran - Admin Delivery Ku',
        ], 'dashboard_shell');
    }

    public function orders(): void
    {
        RoleMiddleware::handle(['admin'], 'manage_orders');
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $res = OrderService::transitionStatus((int)$_POST['order_id'], (string)$_POST['to_status'], (int)current_user()['id'], 'admin', trim((string)($_POST['notes'] ?? '')));
            flash_set($res['ok'] ? 'success' : 'danger', $res['message']);
            redirect_to('admin/orders');
        }

        $orders = $pdo->query(
            'SELECT o.*, u.full_name AS customer_name, m.store_name
             FROM orders o JOIN users u ON u.id=o.user_id JOIN merchants m ON m.id=o.merchant_id
             WHERE o.deleted_at IS NULL ORDER BY o.id DESC LIMIT 100'
        )->fetchAll();

        render_view('admin/orders', ['orders' => $orders, 'pageTitle' => 'Pemantauan Pesanan - Admin Delivery Ku'], 'dashboard_shell');
    }

    public function complaints(): void
    {
        RoleMiddleware::handle(['admin'], 'manage_complaints');
        $pdo = Database::connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $cid = (int)$_POST['complaint_id'];
            $status = in_array($_POST['status'] ?? '', ['open', 'in_progress', 'resolved', 'closed'], true) ? $_POST['status'] : 'in_progress';
            $response = trim((string)($_POST['admin_response'] ?? ''));
            $pdo->prepare('UPDATE complaints SET status = ?, admin_response = ?, handled_by = ? WHERE id = ?')
                ->execute([$status, $response, current_user()['id'], $cid]);
            $uid = (int)$pdo->query("SELECT user_id FROM complaints WHERE id={$cid}")->fetchColumn();
            notify_user($uid, 'Tanggapan Pengaduan', "Pengaduan Anda telah diperbarui ({$status}): {$response}", route_url('user/help'));
            flash_set('success', 'Tanggapan pengaduan disimpan.');
            redirect_to('admin/complaints');
        }

        $complaints = $pdo->query(
            'SELECT c.*, u.full_name, u.phone, o.order_code
             FROM complaints c JOIN users u ON u.id=c.user_id LEFT JOIN orders o ON o.id=c.order_id
             ORDER BY c.id DESC'
        )->fetchAll();
        render_view('admin/complaints', ['complaints' => $complaints, 'pageTitle' => 'Pengaduan Pelanggan - Admin Delivery Ku'], 'dashboard_shell');
    }

    public function reports(): void
    {
        RoleMiddleware::handle(['admin'], 'view_reports');
        $pdo = Database::connection();
        $orders = $pdo->query(
            'SELECT o.*, u.full_name AS customer_name, m.store_name
             FROM orders o JOIN users u ON u.id=o.user_id JOIN merchants m ON m.id=o.merchant_id
             WHERE o.is_demo = 0 AND o.deleted_at IS NULL ORDER BY o.id DESC LIMIT 100'
        )->fetchAll();
        render_view('admin/reports', ['orders' => $orders, 'pageTitle' => 'Laporan Operasional - Admin Delivery Ku'], 'dashboard_shell');
    }
}
