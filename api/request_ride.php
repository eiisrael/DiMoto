<?php
declare(strict_types=1);
require __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method not allowed'],405);
$user = require_auth($pdo);
if ($user['role'] !== 'passenger') respond(['error'=>'Only passengers can request rides'],403);

$in = json_input();
$pk_lat = floatval($in['pickup_lat'] ?? 0);
$pk_lng = floatval($in['pickup_lng'] ?? 0);
$dp_lat = floatval($in['drop_lat'] ?? 0);
$dp_lng = floatval($in['drop_lng'] ?? 0);

if (!$pk_lat || !$pk_lng || !$dp_lat || !$dp_lng) respond(['error'=>'Invalid coordinates'],422);

$pdo->beginTransaction();
try {
  $pdo->prepare("INSERT INTO ride_requests (passenger_id,pickup_lat,pickup_lng,drop_lat,drop_lng) VALUES (?,?,?,?,?)")
      ->execute([$user['id'],$pk_lat,$pk_lng,$dp_lat,$dp_lng]);
  $reqId = (int)$pdo->lastInsertId();

  $pdo->prepare("INSERT INTO rides (request_id,passenger_id,status) VALUES (?,?, 'waiting_driver')")
      ->execute([$reqId,$user['id']]);
  $rideId = (int)$pdo->lastInsertId();
  $pdo->commit();

  respond(['ok'=>true,'ride_id'=>$rideId,'request_id'=>$reqId]);
} catch (Throwable $e) {
  $pdo->rollBack();
  respond(['error'=>'Could not create ride'],500);
}
