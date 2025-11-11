<?php
declare(strict_types=1);

$DB_HOST = '127.0.0.1';
$DB_NAME = 'dimoto';
$DB_USER = 'root';
$DB_PASS = '';

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}

header('Content-Type: application/json; charset=utf-8');
ini_set('session.use_only_cookies', '1');

function random_id(int $len = 64): string {
  return bin2hex(random_bytes(intval($len / 2)));
}
