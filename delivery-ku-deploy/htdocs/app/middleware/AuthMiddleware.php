<?php
declare(strict_types=1);

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!is_logged_in()) {
            flash_set('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
            redirect_to('auth/login');
        }
        $user = current_user();
        if (($user['status'] ?? 'inactive') !== 'active') {
            unset($_SESSION['auth_user']);
            flash_set('danger', 'Akun Anda sedang nonaktif atau menunggu verifikasi.');
            redirect_to('auth/login');
        }
    }
}
