<?php
// flash.php — helpers de mensagens de feedback (alertas)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_flash(string $key, string $message, string $type = 'info'): void {
    $_SESSION['flashes'][$key] = ['msg' => $message, 'type' => $type];
}

function get_flash(?string $key = null): ?array {
    if ($key === null) {
        if (empty($_SESSION['flashes'])) return null;
        $flashes = $_SESSION['flashes'];
        unset($_SESSION['flashes']);
        return $flashes;
    }
    if (!isset($_SESSION['flashes'][$key])) return null;
    $data = $_SESSION['flashes'][$key];
    unset($_SESSION['flashes'][$key]);
    return $data;
}
