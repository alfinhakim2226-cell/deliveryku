<?php
declare(strict_types=1);

/**
 * Loader Konfigurasi Aplikasi (db_credentials.php + .env opsional)
 * Mode produksi menonaktifkan tampilan error ke browser demi keamanan.
 */
$credentials = [];
$credFile = BASE_PATH . '/app/config/db_credentials.php';
if (is_file($credFile) && is_readable($credFile)) {
    $loaded = require $credFile;
    if (is_array($loaded)) {
        $credentials = $loaded;
    }
}

$envFile = BASE_PATH . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            $eqPos = strpos($trimmed, '=');
            if ($eqPos === false) {
                continue;
            }
            $key = trim(substr($trimmed, 0, $eqPos));
            $val = trim(substr($trimmed, $eqPos + 1));
            if (
                (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))
            ) {
                $val = substr($val, 1, -1);
            }
            $_ENV[$key] = $val;
        }
    }
}

$appDebug = isset($_ENV['APP_DEBUG'])
    ? filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN)
    : (bool)($credentials['app_debug'] ?? false);

if ($appDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/app.log');

$timezone = $_ENV['APP_TIMEZONE'] ?? ($credentials['timezone'] ?? 'Asia/Jakarta');
date_default_timezone_set($timezone);

return $GLOBALS['dk_config'] = [
    'name'        => $_ENV['APP_NAME'] ?? ($credentials['app_name'] ?? 'Delivery Ku'),
    'env'         => $_ENV['APP_ENV'] ?? ($credentials['app_env'] ?? 'production'),
    'debug'       => $appDebug,
    'url'         => rtrim((string)($_ENV['APP_URL'] ?? ($credentials['app_url'] ?? '')), '/'),
    'timezone'    => $timezone,
    'force_https' => isset($_ENV['FORCE_HTTPS'])
        ? filter_var($_ENV['FORCE_HTTPS'], FILTER_VALIDATE_BOOLEAN)
        : (bool)($credentials['force_https'] ?? false),
    'db_host'     => $_ENV['DB_HOST'] ?? ($credentials['db_host'] ?? '127.0.0.1'),
    'db_port'     => $_ENV['DB_PORT'] ?? ($credentials['db_port'] ?? '3306'),
    'db_name'     => $_ENV['DB_NAME'] ?? ($credentials['db_name'] ?? 'deliveryku'),
    'db_user'     => $_ENV['DB_USER'] ?? ($credentials['db_user'] ?? 'root'),
    'db_pass'     => $_ENV['DB_PASS'] ?? ($credentials['db_pass'] ?? ''),
];
