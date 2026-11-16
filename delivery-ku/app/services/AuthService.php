<?php
declare(strict_types=1);

final class AuthService
{
    public static function attemptLogin(string $email, string $password): array
    {
        $pdo = Database::connection();
        $email = strtolower(trim($email));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (RateLimitMiddleware::isLockedOut($email, $ip)) {
            return ['ok' => false, 'message' => 'Terlalu banyak percobaan login gagal. Coba lagi dalam beberapa menit.'];
        }

        $stmt = $pdo->prepare(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            RateLimitMiddleware::recordAttempt($email, $ip, false);
            return ['ok' => false, 'message' => 'Email atau kata sandi tidak sesuai.'];
        }

        if ($user['status'] !== 'active') {
            return ['ok' => false, 'message' => 'Status akun Anda saat ini: ' . $user['status'] . '. Hubungi Admin jika butuh bantuan.'];
        }

        RateLimitMiddleware::recordAttempt($email, $ip, true);
        session_regenerate_id(true);

        // Load permissions (admin_user_permissions jika admin, atau role_permissions)
        $permissions = [];
        if ($user['role_slug'] === 'admin') {
            $pStmt = $pdo->prepare(
                'SELECT p.slug FROM permissions p
                 INNER JOIN admin_user_permissions aup ON aup.permission_id = p.id
                 WHERE aup.user_id = ?'
            );
            $pStmt->execute([$user['id']]);
            $permissions = $pStmt->fetchAll(PDO::FETCH_COLUMN);
        } elseif ($user['role_slug'] === 'superadmin') {
            $permissions = $pdo->query('SELECT slug FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
        }

        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

        unset($user['password_hash'], $user['reset_token']);
        $user['permissions'] = $permissions;
        $_SESSION['auth_user'] = $user;

        log_activity('LOGIN', 'auth', 'Pengguna berhasil masuk ke sistem.', (int)$user['id'], $user['role_slug']);
        return ['ok' => true, 'user' => $user];
    }

    public static function registerAccount(array $input): array
    {
        $pdo = Database::connection();
        $roleSlug = $input['role_slug'] ?? 'user';
        if (!in_array($roleSlug, ['user', 'merchant', 'driver'], true)) {
            $roleSlug = 'user';
        }

        $email = strtolower(trim((string)$input['email']));
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            return ['ok' => false, 'message' => 'Email sudah terdaftar di Delivery Ku.'];
        }

        $roleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = " . $pdo->quote($roleSlug))->fetchColumn();
        $hash = password_hash((string)$input['password'], PASSWORD_BCRYPT);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (role_id, full_name, email, phone, password_hash, status, is_demo) VALUES (?, ?, ?, ?, ?, ?, 0)'
            );
            $stmt->execute([
                $roleId,
                trim((string)$input['full_name']),
                $email,
                trim((string)$input['phone']),
                $hash,
                'active',
            ]);
            $userId = (int)$pdo->lastInsertId();

            if ($roleSlug === 'merchant') {
                $storeName = trim((string)($input['store_name'] ?? $input['full_name'] . ' Store'));
                $slug = slugify($storeName) . '-' . $userId;
                $mStmt = $pdo->prepare(
                    'INSERT INTO merchants (user_id, merchant_category_id, district_id, store_name, slug, address, is_open, verification_status, is_demo)
                     VALUES (?, ?, ?, ?, ?, ?, 1, "pending", 0)'
                );
                $mStmt->execute([
                    $userId,
                    (int)($input['merchant_category_id'] ?? 1),
                    (int)($input['district_id'] ?? 1),
                    $storeName,
                    $slug,
                    trim((string)($input['address'] ?? 'Kabupaten Indramayu')),
                ]);
            } elseif ($roleSlug === 'driver') {
                $dStmt = $pdo->prepare(
                    'INSERT INTO drivers (user_id, district_id, vehicle_type, vehicle_brand, license_plate, is_online, verification_status, is_demo)
                     VALUES (?, ?, ?, ?, ?, 0, "pending", 0)'
                );
                $dStmt->execute([
                    $userId,
                    (int)($input['district_id'] ?? 1),
                    (string)($input['vehicle_type'] ?? 'Motor'),
                    trim((string)($input['vehicle_brand'] ?? 'Honda Beat')),
                    strtoupper(trim((string)($input['license_plate'] ?? 'E 0000 XX'))),
                ]);
            }

            $pdo->commit();
            log_activity('REGISTER', 'auth', "Pendaftaran akun baru ({$roleSlug}): {$email}", $userId, $roleSlug);
            return ['ok' => true, 'user_id' => $userId];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Gagal mendaftarkan akun: ' . $e->getMessage()];
        }
    }

    public static function logout(): void
    {
        if (is_logged_in()) {
            log_activity('LOGOUT', 'auth', 'Pengguna keluar dari sistem.');
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
