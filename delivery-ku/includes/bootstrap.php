<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Buat folder storage & uploads lebih awal agar error_log siap digunakan
foreach (['storage/logs', 'public/uploads/products', 'public/uploads/merchants', 'public/uploads/proofs', 'public/uploads/documents', 'public/uploads/avatars'] as $dir) {
    $full = BASE_PATH . '/' . $dir;
    if (!is_dir($full)) {
        @mkdir($full, 0755, true);
    }
}

$appConfig = require BASE_PATH . '/app/config/app.php';
$securityConfig = require BASE_PATH . '/app/config/security.php';

// Deteksi HTTPS (termasuk di balik proxy Cloudflare / SSL InfinityFree)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? null) == 443)
    || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');

$hostName = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
$isLocal = str_starts_with($hostName, 'localhost') || str_starts_with($hostName, '127.0.0.1');

if (!empty($appConfig['force_https']) && !$isHttps && !$isLocal && PHP_SAPI !== 'cli') {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: https://' . $hostName . $uri, true, 301);
    exit;
}

session_name($securityConfig['session_name']);
session_set_cookie_params([
    'lifetime' => $securityConfig['session_lifetime'],
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once BASE_PATH . '/app/config/constants.php';
require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/helpers/functions.php';
require_once BASE_PATH . '/app/helpers/flash.php';
require_once BASE_PATH . '/app/helpers/validator.php';

spl_autoload_register(function (string $class): void {
    $dirs = ['models', 'services', 'controllers', 'middleware'];
    foreach ($dirs as $dir) {
        $file = BASE_PATH . "/app/{$dir}/{$class}.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
