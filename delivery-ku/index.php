<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$routes = require BASE_PATH . '/routes/web.php';
$route = trim((string)($_GET['route'] ?? ''), '/');

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>404 - Delivery Ku</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light d-flex align-items-center min-vh-100"><div class="container text-center"><h1 class="display-5 fw-bold text-primary">404</h1><p class="text-muted">Halaman tidak ditemukan di Delivery Ku.</p><a href="' . e(route_url('user/home')) . '" class="btn btn-primary">Kembali ke Beranda</a></div></body></html>';
    exit;
}

$handler = $routes[$route];
$isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

if ($isPost && isset($handler[2], $handler[3])) {
    $controllerClass = $handler[2];
    $method = $handler[3];
} else {
    $controllerClass = $handler[0];
    $method = $handler[1];
}

$controller = new $controllerClass();
$controller->{$method}();
