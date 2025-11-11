<?php
declare(strict_types=1);

/**
 * Configura a conexão PDO e funções utilitárias globais.
 * Usa condicional para não recriar conexão nem funções em múltiplos includes.
 */

if (!isset($GLOBALS['pdo'])) {
    $DB_HOST = '127.0.0.1';
    $DB_NAME = 'dimoto';
    $DB_USER = 'root';
    $DB_PASS = '';

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
    $GLOBALS['pdo'] = $pdo;

    // Define random_id apenas uma vez
    if (!function_exists('random_id')) {
        function random_id(int $length = 64): string {
            return bin2hex(random_bytes(intval($length / 2)));
        }
    }

    // Define cabeçalho JSON somente quando a resposta for JSON
    if (php_sapi_name() !== 'cli' && (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
        header('Content-Type: application/json; charset=utf-8');
    }
    ini_set('session.use_only_cookies', '1');
}
