<?php
declare(strict_types=1);
require __DIR__ . '/config.php'; require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$in=json_input(); $email=sanitize_email($in['email']??''); $pass=$in['password']??'';
if(!$email||!$pass) respond(['error'=>'Invalid credentials'],422);

$stmt=$pdo->prepare("SELECT id,password_hash,role,name FROM users WHERE email=?"); $stmt->execute([$email]); $u=$stmt->fetch();
if(!$u||!password_verify($pass,$u['password_hash'])) respond(['error'=>'Invalid credentials'],401);

$sid=random_id(64); $csrf=random_id(64);
$pdo->prepare("INSERT INTO sessions (id,user_id,csrf_token) VALUES (?,?,?)")->execute([$sid,$u['id'],$csrf]);
setcookie('dimoto_session',$sid,['expires'=>time()+604800,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
respond(['ok'=>true,'csrf'=>$csrf,'user'=>['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']]]);
