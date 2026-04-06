<?php
require_once __DIR__ . '/helpers.php';

function auth_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('pksb_session');
        session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
        session_start();
    }
}

function is_logged_in(): bool {
    auth_start();
    return !empty($_SESSION['admin_user']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . admin_url('index.php'));
        exit;
    }
}

function admin_url(string $path = ''): string {
    $proto  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If already inside /admin, keep it; else append
    if (!str_ends_with(rtrim($script, '/'), '/admin')) {
        $script = rtrim($script, '/') . '/admin';
    }
    return $proto . '://' . $host . rtrim($script, '/') . '/' . ltrim($path, '/');
}

function try_login(string $username, string $password): bool {
    $users = load_json('users.json');
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            auth_start();
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_name'] = $user['name'];
            return true;
        }
    }
    return false;
}

function do_logout(): void {
    auth_start();
    $_SESSION = [];
    session_destroy();
}

function csrf_token(): string {
    auth_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): bool {
    return !empty($_POST['_csrf']) && $_POST['_csrf'] === ($_SESSION['csrf'] ?? '');
}
