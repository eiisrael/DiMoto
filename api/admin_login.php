<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(['error' => 'Method not allowed'], 405);
}

$in = json_input();
$email = sanitize_email($in['email'] ?? '');
$pass  = $in['password'] ?? '';

if (!$email || !$pass) {
  respond(['error' => 'Campos obrigatórios'], 422);
}

// Busca usuário admin
$st = $pdo->prepare("SELECT id,password_hash,role,name FROM users WHERE email=? AND role='admin' LIMIT 1");
$st->execute([$email]);
$u = $st->fetch();

if (!$u || !password_verify($pass, $u['password_hash'])) {
  respond(['error' => 'Invalid credentials'], 401);
}

// Cria sessão de admin
$sid = random_id(64);
$csrf = random_id(64);
$pdo->prepare("INSERT INTO admin_sessions (id,admin_id,csrf_token) VALUES (?,?,?)")
    ->execute([$sid, $u['id'], $csrf]);

// Ajuste: cookie disponível em toda a app (/DiMoto/)
$cookie_path = '/DiMoto/'; // ajuste se seu projeto estiver em outra pasta
setcookie('dimoto_admin', $sid, [
  'expires' => time() + 604800,
  'path' => $cookie_path,
  'httponly' => true,
  'samesite' => 'Lax'
]);

respond([
  'ok' => true,
  'csrf' => $csrf,
  'admin' => ['id' => $u['id'], 'name' => $u['name'], 'email' => $email]
]);
