<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method not allowed'],405);

$in = json_input();
$email = sanitize_email($in['email'] ?? '');
$pass = $in['password'] ?? '';

if (!$email || !$pass) respond(['error'=>'Invalid credentials'],422);

$stmt = $pdo->prepare("SELECT id, password_hash, role, name FROM users WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || !password_verify($pass, $user['password_hash'])) {
  respond(['error'=>'Invalid credentials'],401);
}

$sessionId = random_id(64);
$csrf = random_id(64);
$stmt2 = $pdo->prepare("INSERT INTO sessions (id, user_id, csrf_token) VALUES (?,?,?)");
$stmt2->execute([$sessionId, $user['id'], $csrf]);

setcookie('dimoto_session', $sessionId, [
  'expires' => time()+60*60*24*7,
  'path' => '/',
  'httponly' => true,
  'samesite' => 'Lax',
  // 'secure' => true, // habilite em HTTPS
]);

respond(['ok'=>true,'csrf'=>$csrf,'user'=>['id'=>$user['id'],'name'=>$user['name'],'role'=>$user['role']]]);
