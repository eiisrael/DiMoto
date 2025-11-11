<?php
declare(strict_types=1); require __DIR__ . '/middleware.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$u=require_auth($pdo); if($u['role']!=='driver') respond(['error'=>'Only drivers'],403);
$in=json_input(); $lat=isset($in['lat'])?floatval($in['lat']):null; $lng=isset($in['lng'])?floatval($in['lng']):null;
if($lat===null||$lng===null) respond(['error'=>'lat/lng required'],422);
$pdo->prepare("UPDATE drivers SET current_lat=?, current_lng=? WHERE user_id=?")->execute([$lat,$lng,$u['id']]);
respond(['ok'=>true]);
