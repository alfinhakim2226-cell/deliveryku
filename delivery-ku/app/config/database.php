<?php
declare(strict_types=1);

/**
 * Koneksi Database PDO Singleton (MySQL / MariaDB - Kompatibel InfinityFree)
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $cfg = $GLOBALS['dk_config'] ?? [];
        $host = (string)($cfg['db_host'] ?? ($_ENV['DB_HOST'] ?? 'sql103.infinityfree.com'));
        $port = (string)($cfg['db_port'] ?? ($_ENV['DB_PORT'] ?? '3306'));
        $dbname = (string)($cfg['db_name'] ?? ($_ENV['DB_NAME'] ?? 'if0_43456311_deliveryku'));
        $user = (string)($cfg['db_user'] ?? ($_ENV['DB_USER'] ?? 'if0_43456311'));
        $pass = (string)($cfg['db_pass'] ?? ($_ENV['DB_PASS'] ?? 'deliveryku'));

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
            return self::$instance;
        } catch (PDOException $e) {
            @error_log('[' . date('Y-m-d H:i:s') . '] DB Connection Error: ' . $e->getMessage() . PHP_EOL, 3, BASE_PATH . '/storage/logs/app.log');
            http_response_code(503);
            $debug = (bool)($cfg['debug'] ?? false);
            $detail = $debug ? '<pre class="mt-3 text-danger small">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>' : '';
            exit(
                '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
                . '<title>Koneksi Database Belum Siap - Delivery Ku</title>'
                . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
                . '</head><body class="bg-light d-flex align-items-center min-vh-100"><div class="container"><div class="card shadow-sm mx-auto" style="max-width:580px">'
                . '<div class="card-body p-4"><h4 class="text-primary fw-bold mb-2">Delivery Ku — Konfigurasi Database InfinityFree</h4>'
                . '<p class="text-muted mb-2">Aplikasi belum dapat terhubung ke server database MySQL. Pastikan:</p>'
                . '<ol class="small text-muted mb-0"><li>File <code>app/config/db_credentials.php</code> (atau <code>.env</code>) sudah diisi dengan Hostname, Database, Username, dan Password dari panel <strong>InfinityFree -> MySQL Databases</strong>.</li>'
                . '<li>File <code>database.sql</code> sudah diimpor ke <strong>phpMyAdmin</strong>.</li></ol>'
                . $detail
                . '</div></div></div></body></html>'
            );
        }
    }
}
