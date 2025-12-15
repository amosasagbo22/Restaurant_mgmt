<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function is_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function format_currency(float $amount): string {
    return '$' . number_format($amount, 2);
}

function post(string $key, $default = null) {
    return $_POST[$key] ?? $default;
}

function get(string $key, $default = null) {
    return $_GET[$key] ?? $default;
}

function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

