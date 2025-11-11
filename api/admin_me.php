<?php
declare(strict_types=1); require __DIR__ . '/config.php'; require __DIR__ . '/helpers.php';
$sid=$_COOKIE['dimoto_admin']??''; if(!$sid) respond(['error'=>'Not authenticated'],401);
$st=$pdo->prepare("SELECT s.admin_id, s.csrf_token, u.name FROM admin_sessions s JOIN users u ON u.id=s.admin_id WHERE s.id=?");
$st->execute([$sid]); $row=$st->fetch(); if(!$row) respond(['error'=>'Invalid session'],401);
respond(['admin'=>['id'=>$row['admin_id'],'name'=>$row['name']], 'csrf'=>$row['csrf_token']]);
