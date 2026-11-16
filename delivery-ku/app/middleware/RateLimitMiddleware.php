<?php
declare(strict_types=1);

final class RateLimitMiddleware
{
    public static function isLockedOut(string $email, string $ip): bool
    {
        $security = require BASE_PATH . '/app/config/security.php';
        $maxAttempts = (int)$security['max_login_attempts'];
        $windowMinutes = (int)$security['login_lockout_minutes'];

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE (email = ? OR ip_address = ?)
               AND is_success = 0
               AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );
        $stmt->execute([$email, $ip, $windowMinutes]);
        return ((int)$stmt->fetchColumn()) >= $maxAttempts;
    }

    public static function recordAttempt(string $email, string $ip, bool $success): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO login_attempts (email, ip_address, is_success) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $ip, $success ? 1 : 0]);

        if ($success) {
            $clear = $pdo->prepare('DELETE FROM login_attempts WHERE email = ? AND is_success = 0');
            $clear->execute([$email]);
        }
    }
}
