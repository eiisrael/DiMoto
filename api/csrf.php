<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);

$sessionId = $_COOKIE['dimoto_session'] ?? '';
if (!$sessionId) respond(['error'=>'Not authenticated'],401);

$stmt = $pdo->prepare("SELECT csrf_token FROM sessions WHERE id=?");
$stmt->execute([$sessionId]);
$row = $stmt->fetch();
if(!$row) respond(['error'=>'Invalid session'],401);

respond(['csrf'=>$row['csrf_token']]);
