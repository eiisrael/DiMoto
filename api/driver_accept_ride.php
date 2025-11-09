<?php
declare(strict_types=1);
require __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method not allowed'],405);
$user = require_auth($pdo);
if ($user['role'] !== 'driver') respond(['error'=>'Only drivers'],403);

$in = json_input();
$rideId = intval($in['ride_id'] ?? 0);
if (!$rideId) respond(['error'=>'ride_id required'],422);

// Simple rule: first driver wins if ride is waiting_driver
$pdo->beginTransaction();
try {
  $stmt = $pdo->prepare("SELECT status, driver_id FROM rides WHERE id = ? FOR UPDATE");
  $stmt->execute([$rideId]);
  $ride = $stmt->fetch();
  if (!$ride || $ride['status'] !== 'waiting_driver' || $ride['driver_id']) {
    throw new Exception('Ride not available');
  }

  $pdo->prepare("UPDATE rides SET driver_id=?, status='en_route', started_at=NOW() WHERE id=?")
      ->execute([$user['id'], $rideId]);

  $pdo->prepare("UPDATE ride_requests SET status='matched' WHERE id=(SELECT request_id FROM rides WHERE id=?)")
      ->execute([$rideId]);

  $pdo->commit();
  respond(['ok'=>true]);
} catch (Throwable $e) {
  $pdo->rollBack();
  respond(['error'=>'Ride already taken or invalid'],409);
}
