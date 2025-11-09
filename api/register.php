<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method not allowed'],405);

$in = json_input();
$name = trim($in['name'] ?? '');
$email = sanitize_email($in['email'] ?? '');
$phone = trim($in['phone'] ?? '');
$role = $in['role'] ?? 'passenger';
$pass = $in['password'] ?? '';

if (!$name || !$email || !$pass || !in_array($role,['passenger','driver'])) {
  respond(['error'=>'Invalid fields'],422);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$pdo->beginTransaction();
try {
  $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password_hash) VALUES (?,?,?,?,?)");
  $stmt->execute([$role, $name, $email, $phone, $hash]);
  $uid = (int)$pdo->lastInsertId();

  if ($role === 'driver') {
    $cnh = trim($in['cnh'] ?? '');
    $plate = strtoupper(trim($in['vehicle_plate'] ?? ''));
    $model = trim($in['vehicle_model'] ?? '');
    if (!$cnh || !$plate || !$model) throw new Exception('Driver data required');
    $stmt2 = $pdo->prepare("INSERT INTO drivers (user_id, cnh, vehicle_plate, vehicle_model) VALUES (?,?,?,?)");
    $stmt2->execute([$uid, $cnh, $plate, $model]);
  }

  $pdo->commit();
  respond(['ok'=>true,'user_id'=>$uid]);
} catch (Throwable $e) {
  $pdo->rollBack();
  // e-mail único
  respond(['error'=>'Email already registered or invalid driver data'],409);
}
