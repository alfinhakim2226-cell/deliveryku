<?php
declare(strict_types=1);

/**
 * Helper global Delivery Ku
 */

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiah(float|int|string|null $amount): string
{
    return 'Rp ' . number_format((float)($amount ?? 0), 0, ',', '.');
}

function base_url(string $path = ''): string
{
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $base = $scriptDir === '' ? '' : $scriptDir;
    $cleanPath = ltrim($path, '/');
    return $cleanPath === '' ? ($base ?: '/') : "{$base}/{$cleanPath}";
}

function route_url(string $route, array $params = []): string
{
    $query = array_merge(['route' => trim($route, '/')], $params);
    return base_url('index.php') . '?' . http_build_query($query);
}

function asset_url(string $path): string
{
    return base_url('public/assets/' . ltrim($path, '/'));
}

function upload_url(?string $path): string
{
    if ($path === null || trim($path) === '') {
        return asset_url('images/placeholder-food.svg');
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return base_url('public/uploads/' . ltrim($path, '/'));
}

function redirect_to(string $route, array $params = []): never
{
    header('Location: ' . route_url($route, $params));
    exit;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function current_user(): ?array
{
    return $_SESSION['auth_user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['auth_user']['id']);
}

function user_role(): ?string
{
    return $_SESSION['auth_user']['role_slug'] ?? null;
}

function has_permission(string $slug): bool
{
    $user = current_user();
    if ($user === null) {
        return false;
    }
    if (($user['role_slug'] ?? '') === 'superadmin') {
        return true;
    }
    $perms = $user['permissions'] ?? [];
    return in_array($slug, $perms, true);
}

function order_status_badge(string $status): string
{
    $meta = ORDER_STATUSES[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'badge' => 'secondary'];
    return '<span class="badge text-bg-' . e($meta['badge']) . '">' . e($meta['label']) . '</span>';
}

function order_status_label(string $status): string
{
    return ORDER_STATUSES[$status]['label'] ?? ucfirst(str_replace('_', ' ', $status));
}

function format_indo_date(?string $datetime): string
{
    if ($datetime === null || $datetime === '') {
        return '-';
    }
    $ts = strtotime($datetime);
    return $ts ? date('d M Y, H:i', $ts) . ' WIB' : $datetime;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? 'item';
    return trim($text, '-') ?: 'item';
}

function haversine_km(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round(max(0.5, $earthRadius * $c), 2);
}

function get_setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $stmt = Database::connection()->query('SELECT setting_key, setting_value FROM application_settings');
            foreach ($stmt->fetchAll() as $row) {
                $cache[$row['setting_key']] = (string)($row['setting_value'] ?? '');
            }
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function log_activity(string $action, string $module, string $description, ?int $userId = null, ?string $roleSlug = null): void
{
    try {
        $user = current_user();
        $uid = $userId ?? ($user['id'] ?? null);
        $role = $roleSlug ?? ($user['role_slug'] ?? 'guest');
        $stmt = Database::connection()->prepare(
            'INSERT INTO activity_logs (user_id, role_slug, action, module, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $uid,
            $role,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 250),
        ]);
    } catch (Throwable $e) {
        error_log('Activity Log Error: ' . $e->getMessage());
    }
}

function notify_user(int $userId, string $title, string $message, ?string $linkUrl = null): void
{
    try {
        $stmt = Database::connection()->prepare(
            'INSERT INTO notifications (user_id, title, message, link_url) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $message, $linkUrl]);
    } catch (Throwable $e) {
        error_log('Notify Error: ' . $e->getMessage());
    }
}

function render_view(string $view, array $data = [], ?string $layout = null): void
{
    extract($data, EXTR_SKIP);
    $viewFile = BASE_PATH . '/resources/views/' . str_replace('.', '/', $view) . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        exit('View tidak ditemukan: ' . e($view));
    }

    if ($layout !== null) {
        ob_start();
        require $viewFile;
        $content = (string)ob_get_clean();
        $layoutFile = BASE_PATH . '/resources/views/layouts/' . $layout . '.php';
        require $layoutFile;
        return;
    }

    require $viewFile;
}
