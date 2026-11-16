<?php
declare(strict_types=1);

final class CsrfMiddleware
{
    public static function verify(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }
        $token = (string)($_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $sessionToken = (string)($_SESSION['_csrf_token'] ?? '');
        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(419);
            flash_set('danger', 'Sesi formulir kedaluwarsa atau token CSRF tidak valid. Silakan coba lagi.');
            $referer = $_SERVER['HTTP_REFERER'] ?? route_url('auth/login');
            header('Location: ' . $referer);
            exit;
        }
    }
}
