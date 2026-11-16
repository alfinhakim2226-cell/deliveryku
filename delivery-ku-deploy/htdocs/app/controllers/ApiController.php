<?php
declare(strict_types=1);

final class ApiController
{
    public function orderStatus(): void
    {
        AuthMiddleware::handle();
        $orderId = (int)($_GET['id'] ?? 0);
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT o.id, o.order_code, o.status, o.updated_at, d.status AS delivery_status,
                    u_drv.full_name AS driver_name, u_drv.phone AS driver_phone, drv.license_plate
             FROM orders o
             LEFT JOIN deliveries d ON d.order_id = o.id
             LEFT JOIN drivers drv ON drv.id = o.driver_id
             LEFT JOIN users u_drv ON u_drv.id = drv.user_id
             WHERE o.id = ? LIMIT 1'
        );
        $stmt->execute([$orderId]);
        $row = $stmt->fetch();
        if (!$row) {
            json_response(['ok' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }
        $row['status_label'] = order_status_label($row['status']);
        json_response(['ok' => true, 'data' => $row]);
    }

    public function notificationsUnread(): void
    {
        AuthMiddleware::handle();
        $user = current_user();
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$user['id']]);
        $count = (int)$stmt->fetchColumn();

        $listStmt = $pdo->prepare('SELECT id, title, message, link_url, created_at FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5');
        $listStmt->execute([$user['id']]);
        json_response(['ok' => true, 'unread' => $count, 'items' => $listStmt->fetchAll()]);
    }

    public function merchantPendingCount(): void
    {
        RoleMiddleware::handle(['merchant']);
        $pdo = Database::connection();
        $uid = (int)current_user()['id'];
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM orders o
             INNER JOIN merchants m ON m.id = o.merchant_id
             WHERE m.user_id = ? AND o.status = "menunggu_konfirmasi_merchant"'
        );
        $stmt->execute([$uid]);
        json_response(['ok' => true, 'pending_count' => (int)$stmt->fetchColumn()]);
    }

    public function driverAvailableCount(): void
    {
        RoleMiddleware::handle(['driver']);
        $pdo = Database::connection();
        $count = (int)$pdo->query('SELECT COUNT(*) FROM orders WHERE status = "menunggu_driver" AND driver_id IS NULL')->fetchColumn();
        json_response(['ok' => true, 'available_count' => $count]);
    }

    public function villagesByDistrict(): void
    {
        $districtId = (int)($_GET['district_id'] ?? 0);
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id, name, postal_code, latitude, longitude FROM villages WHERE district_id = ? AND is_active = 1 ORDER BY name');
        $stmt->execute([$districtId]);
        json_response(['ok' => true, 'villages' => $stmt->fetchAll()]);
    }
}
