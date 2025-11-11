<?php
declare(strict_types=1); require __DIR__ . '/middleware.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$u=require_auth($pdo);
$in=json_input(); $rideId=intval($in['ride_id']??0); $fare_cents=intval($in['fare_cents']??0);
if(!$rideId||$fare_cents<=0) respond(['error'=>'ride_id and positive fare_cents required'],422);
$st=$pdo->prepare("SELECT passenger_id,driver_id FROM rides WHERE id=?"); $st->execute([$rideId]); $r=$st->fetch();
if(!$r) respond(['error'=>'Ride not found'],404);
if(!in_array($u['id'],[$r['passenger_id'],$r['driver_id']])) respond(['error'=>'Forbidden'],403);
$pdo->beginTransaction();
try{
  $pdo->prepare("UPDATE rides SET status='completed', ended_at=NOW(), fare_cents=? WHERE id=?")->execute([$fare_cents,$rideId]);
  $pdo->prepare("INSERT INTO payments (ride_id,method,status,amount_cents) VALUES (?,?,?,?)")->execute([$rideId,'cash','paid',$fare_cents]);
  $pdo->commit(); respond(['ok'=>true]);
}catch(Throwable $e){ $pdo->rollBack(); respond(['error'=>'Could not complete ride'],500); }
