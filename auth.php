<?php
// auth.php — sessão e helpers de autenticação (protegido contra re-declarações)

// Carrega config apenas uma vez
$config = require __DIR__ . '/config.php';

// Inicia a sessão apenas uma vez, com nome definido no config
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!headers_sent()) {
        session_name($config['app']['session_name'] ?? 'projapp_sess');
    }
    session_start();
}

// ---- Helpers ----

if (!function_exists('require_login')) {
    function require_login(): void {
        if (empty($_SESSION['user'])) {
            $_SESSION['after_login_redirect'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('do_login')) {
    function do_login(array $user): void {
        $_SESSION['user'] = [
            'id'       => (int)$user['id'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'is_admin' => (int)($user['is_admin'] ?? 0),
        ];
    }
}

if (!function_exists('do_logout')) {
    function do_logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}

if (!function_exists('redirect_after_login')) {
    function redirect_after_login(string $fallback = 'index.php'): void {
        $to = $_SESSION['after_login_redirect'] ?? $fallback;
        unset($_SESSION['after_login_redirect']);
        header('Location: ' . $to);
        exit;
    }
}
