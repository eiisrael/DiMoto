<?php
declare(strict_types=1); require __DIR__ . '/middleware.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$u=require_auth($pdo); if($u['role']!=='passenger') respond(['error'=>'Only passengers'],403);
$in=json_input(); $pk_lat=floatval($in['pickup_lat']??0); $pk_lng=floatval($in['pickup_lng']??0); $dp_lat=floatval($in['drop_lat']??0); $dp_lng=floatval($in['drop_lng']??0);
if(!$pk_lat||!$pk_lng||!$dp_lat||!$dp_lng) respond(['error'=>'Invalid coordinates'],422);
$pdo->beginTransaction();
try{
  $pdo->prepare("INSERT INTO ride_requests (passenger_id,pickup_lat,pickup_lng,drop_lat,drop_lng) VALUES (?,?,?,?,?)")
      ->execute([$u['id'],$pk_lat,$pk_lng,$dp_lat,$dp_lng]);
  $req=(int)$pdo->lastInsertId();
  $pdo->prepare("INSERT INTO rides (request_id,passenger_id,status) VALUES (?,?, 'waiting_driver')")->execute([$req,$u['id']]);
  $ride=(int)$pdo->lastInsertId(); $pdo->commit(); respond(['ok'=>true,'ride_id'=>$ride,'request_id'=>$req]);
}catch(Throwable $e){ $pdo->rollBack(); respond(['error'=>'Could not create ride'],500); }
