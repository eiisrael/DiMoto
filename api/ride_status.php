<?php
declare(strict_types=1); require __DIR__ . '/middleware.php';
if ($_SERVER['REQUEST_METHOD']!=='GET') respond(['error'=>'Method not allowed'],405);
$u=require_auth($pdo); $rideId=intval($_GET['ride_id']??0); if(!$rideId) respond(['error'=>'ride_id required'],422);
$stmt=$pdo->prepare("SELECT r.*, d.current_lat, d.current_lng, u2.name AS driver_name
 FROM rides r
 LEFT JOIN users u2 ON u2.id=r.driver_id
 LEFT JOIN drivers d ON d.user_id=r.driver_id
 WHERE r.id=? AND (r.passenger_id=? OR r.driver_id=?)");
$stmt->execute([$rideId,$u['id'],$u['id']]); $row=$stmt->fetch(); if(!$row) respond(['error'=>'Ride not found'],404);
respond(['ride'=>$row]);
