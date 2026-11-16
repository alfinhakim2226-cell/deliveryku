<?php
declare(strict_types=1);

return [
    'session_name' => $_ENV['SESSION_NAME'] ?? 'DELIVERYKU_SESSID',
    'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
    'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
    'login_lockout_minutes' => (int)($_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15),
    'max_upload_bytes' => 2 * 1024 * 1024, // 2 MB
    'allowed_image_mimes' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],
];
