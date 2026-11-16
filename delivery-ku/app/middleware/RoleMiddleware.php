<?php
declare(strict_types=1);

final class RoleMiddleware
{
    public static function handle(array $allowedRoles, ?string $requiredPermission = null): void
    {
        AuthMiddleware::handle();
        $role = user_role();
        if (!in_array($role, $allowedRoles, true)) {
            http_response_code(403);
            flash_set('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            $home = ROLE_HOME_ROUTES[$role ?? 'user'] ?? 'auth/login';
            redirect_to($home);
        }

        if ($requiredPermission !== null && $role === 'admin' && !has_permission($requiredPermission)) {
            http_response_code(403);
            flash_set('danger', 'Superadmin belum memberikan izin akses modul ini kepada akun Admin Anda.');
            redirect_to('admin/dashboard');
        }
    }
}
