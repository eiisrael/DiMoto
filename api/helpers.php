<?php
declare(strict_types=1);

/**
 * Funções helpers globais com proteção contra redefinição.
 */

if (!function_exists('respond')) {
    function respond(array $data, int $code = 200): void {
        http_response_code($code);
        // Só define o content-type se o cliente aceitar JSON
        if (!headers_sent() && (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('json_input')) {
    function json_input(): array {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email(string $email): string {
        $email = trim(strtolower($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }
}

if (!function_exists('sanitize_text')) {
    function sanitize_text(string $txt): string {
        return htmlspecialchars(trim($txt), ENT_QUOTES, 'UTF-8');
    }
}
