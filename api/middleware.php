<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

function require_auth(PDO $pdo): array {
  $sid = $_COOKIE['dimoto_session'] ?? '';
  if (!$sid) respond(['error'=>'Not authenticated'],401);

  $stmt = $pdo->prepare(
    "SELECT s.user_id, s.csrf_token, u.id, u.role, u.name, u.email
     FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.id=?"
  );
  $stmt->execute([$sid]);
  $user = $stmt->fetch();
  if (!$user) respond(['error'=>'Invalid session'],401);

  if (in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT','PATCH','DELETE'])) {
    $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$header || !hash_equals($user['csrf_token'], $header)) respond(['error'=>'CSRF token invalid'],403);
  }
  return $user;
}
