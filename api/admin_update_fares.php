<?php
declare(strict_types=1); require __DIR__ . '/config.php'; require __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$sid=$_COOKIE['dimoto_admin']??''; if(!$sid) respond(['error'=>'Not authenticated'],401);
$st=$pdo->prepare("SELECT csrf_token FROM admin_sessions WHERE id=?"); $st->execute([$sid]); $s=$st->fetch(); if(!$s) respond(['error'=>'Invalid session'],401);
$header=$_SERVER['HTTP_X_CSRF_TOKEN']??''; if(!$header||!hash_equals($s['csrf_token'],$header)) respond(['error'=>'CSRF token invalid'],403);

$in=json_input();
$base=floatval($in['base']??3.0);
$per_min=floatval($in['per_minute']??0.25);
$t1=floatval($in['tier1_km']??2.0);
$t2=floatval($in['tier2_km']??1.5);
$t3=floatval($in['tier3_km']??1.0);
$surge=floatval($in['surge_multiplier']??1.0);

$pdo->prepare("UPDATE fare_config SET base=?, per_minute=?, tier1_km=?, tier2_km=?, tier3_km=?, surge_multiplier=? WHERE id=1")
    ->execute([$base,$per_min,$t1,$t2,$t3,$surge]);
respond(['ok'=>true]);
