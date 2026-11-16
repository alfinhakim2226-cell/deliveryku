<?php
declare(strict_types=1);

final class AuthController
{
    public function showLogin(): void
    {
        if (is_logged_in()) {
            redirect_to(ROLE_HOME_ROUTES[user_role() ?? 'user'] ?? 'user/home');
        }
        render_view('auth/login', ['pageTitle' => 'Masuk - Delivery Ku']);
    }

    public function login(): void
    {
        CsrfMiddleware::verify();
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (!Validator::email($email) || $password === '') {
            flash_set('danger', 'Masukkan email yang valid dan kata sandi.');
            redirect_to('auth/login');
        }

        $res = AuthService::attemptLogin($email, $password);
        if (!$res['ok']) {
            flash_set('danger', $res['message']);
            redirect_to('auth/login');
        }

        $role = $res['user']['role_slug'];
        flash_set('success', 'Selamat datang kembali, ' . $res['user']['full_name'] . '!');
        redirect_to(ROLE_HOME_ROUTES[$role] ?? 'user/home');
    }

    public function showRegister(): void
    {
        if (is_logged_in()) {
            redirect_to(ROLE_HOME_ROUTES[user_role() ?? 'user'] ?? 'user/home');
        }
        $pdo = Database::connection();
        $districts = $pdo->query('SELECT * FROM districts WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name')->fetchAll();
        $merchantCategories = $pdo->query('SELECT * FROM merchant_categories WHERE is_active = 1 ORDER BY name')->fetchAll();

        render_view('auth/register', [
            'pageTitle' => 'Daftar Akun - Delivery Ku',
            'districts' => $districts,
            'merchantCategories' => $merchantCategories,
        ]);
    }

    public function register(): void
    {
        CsrfMiddleware::verify();
        $errors = Validator::required($_POST, [
            'full_name' => 'Nama Lengkap',
            'email' => 'Email',
            'phone' => 'Nomor HP/WhatsApp',
            'password' => 'Kata Sandi',
        ]);

        if (!Validator::email((string)($_POST['email'] ?? ''))) {
            $errors[] = 'Format email tidak valid.';
        }
        if (!Validator::phone((string)($_POST['phone'] ?? ''))) {
            $errors[] = 'Format nomor HP tidak valid.';
        }
        if (strlen((string)($_POST['password'] ?? '')) < 6) {
            $errors[] = 'Kata sandi minimal 6 karakter.';
        }

        if (!empty($errors)) {
            flash_set('danger', implode(' ', $errors));
            redirect_to('auth/register');
        }

        $res = AuthService::registerAccount($_POST);
        if (!$res['ok']) {
            flash_set('danger', $res['message']);
            redirect_to('auth/register');
        }

        flash_set('success', 'Pendaftaran berhasil! Silakan masuk dengan akun Anda.');
        redirect_to('auth/login');
    }

    public function showForgotPassword(): void
    {
        render_view('auth/forgot_password', ['pageTitle' => 'Lupa Kata Sandi - Delivery Ku']);
    }

    public function forgotPassword(): void
    {
        CsrfMiddleware::verify();
        $pdo = Database::connection();
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $newPass = (string)($_POST['new_password'] ?? '');
        $token = trim((string)($_POST['reset_token'] ?? ''));

        if ($token !== '' && $newPass !== '') {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND reset_token = ? AND reset_expires_at >= NOW() LIMIT 1');
            $stmt->execute([$email, $token]);
            $u = $stmt->fetch();
            if (!$u) {
                flash_set('danger', 'Kode reset tidak valid atau sudah kedaluwarsa.');
                redirect_to('auth/forgot-password');
            }
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?')
                ->execute([$hash, $u['id']]);
            flash_set('success', 'Kata sandi berhasil diperbarui. Silakan login.');
            redirect_to('auth/login');
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u) {
            $genToken = strtoupper(bin2hex(random_bytes(3)));
            $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?')
                ->execute([$genToken, $u['id']]);
            flash_set('info', 'Kode reset dibuat (simulasi SMTP shared hosting): ' . $genToken . ' — berlaku 30 menit.');
        } else {
            flash_set('info', 'Jika email terdaftar, instruksi reset kata sandi telah diproses.');
        }
        redirect_to('auth/forgot-password', ['email' => $email]);
    }

    public function logout(): void
    {
        AuthService::logout();
        flash_set('success', 'Anda telah keluar dari Delivery Ku.');
        redirect_to('auth/login');
    }
}
