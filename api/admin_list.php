<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

// Verifica cookie da sessão admin (mesmo nome usado no login)
$cookie = $_COOKIE['dimoto_admin'] ?? '';
if (!$cookie) respond(['error'=>'Não autenticado'], 401);

// Verifica sessão na tabela admin_sessions
$st = $pdo->prepare("SELECT admin_id, csrf_token FROM admin_sessions WHERE id=?");
$st->execute([$cookie]);
$s = $st->fetch();
if (!$s) respond(['error'=>'Sessão inválida'], 401);

// Verifica se o user ainda é admin
$st2 = $pdo->prepare("SELECT role, name FROM users WHERE id=?");
$st2->execute([$s['admin_id']]);
$u = $st2->fetch();
if (!$u || $u['role'] !== 'admin') respond(['error'=>'Acesso negado'], 403);

// Puxa dados necessários
$rides = $pdo->query("SELECT r.id, r.status, r.fare_cents, r.started_at, r.ended_at,
  (SELECT name FROM users WHERE id=r.passenger_id) AS passenger,
  (SELECT name FROM users WHERE id=r.driver_id) AS driver
  FROM rides r ORDER BY r.id DESC LIMIT 100")->fetchAll();

$drivers = $pdo->query("SELECT u.id,u.name,u.email, d.vehicle_plate, d.vehicle_model, d.is_online, d.current_lat, d.current_lng
  FROM drivers d JOIN users u ON u.id=d.user_id ORDER BY u.name")->fetchAll();

// Tarifa
$cfg = $pdo->query("SELECT * FROM fare_config WHERE id=1")->fetch();

// Retorna dados e token CSRF da sessão
respond([
  'ok' => true,
  'rides' => $rides,
  'drivers' => $drivers,
  'fare' => $cfg,
  'csrf' => $s['csrf_token']
]);
