<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$in=json_input();
$name=trim($in['name']??''); $email=sanitize_email($in['email']??''); $phone=trim($in['phone']??''); $role=$in['role']??'passenger'; $pass=$in['password']??'';
if(!$name||!$email||!$pass||!in_array($role,['passenger','driver'])) respond(['error'=>'Invalid fields'],422);

$hash=password_hash($pass,PASSWORD_DEFAULT);
$pdo->beginTransaction();
try{
  $pdo->prepare("INSERT INTO users (role,name,email,phone,password_hash) VALUES (?,?,?,?,?)")->execute([$role,$name,$email,$phone,$hash]);
  $uid=(int)$pdo->lastInsertId();
  if($role==='driver'){ respond(['error'=>'Use /api/register_driver_upload.php para motorista'],422); }
  $pdo->commit(); respond(['ok'=>true,'user_id'=>$uid]);
}catch(Throwable $e){ $pdo->rollBack(); respond(['error'=>'Email already registered'],409); }
