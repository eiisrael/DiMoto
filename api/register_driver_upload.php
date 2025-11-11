<?php
declare(strict_types=1);
require __DIR__ . '/config.php'; require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);

$name=trim($_POST['name']??''); $email=sanitize_email($_POST['email']??''); $phone=trim($_POST['phone']??'');
$pass=$_POST['password']??''; $cnh=trim($_POST['cnh']??''); $plate=strtoupper(trim($_POST['vehicle_plate']??'')); $model=trim($_POST['vehicle_model']??'');
if(!$name||!$email||!$pass||!$cnh||!$plate||!$model) respond(['error'=>'Invalid fields'],422);
$hash=password_hash($pass,PASSWORD_DEFAULT);

$up_dir=dirname(__DIR__).'/uploads/driver_docs/'; if(!is_dir($up_dir)) @mkdir($up_dir,0777,true);
function save_upload(string $field, string $prefix): ?string{
  if(!isset($_FILES[$field])||$_FILES[$field]['error']!==UPLOAD_ERR_OK) return null;
  $ext=pathinfo($_FILES[$field]['name'],PATHINFO_EXTENSION)?:'jpg';
  $fname=$prefix.'_'.bin2hex(random_bytes(6)).'.'.$ext; $dest=dirname(__DIR__).'/uploads/driver_docs/'.$fname;
  if(!move_uploaded_file($_FILES[$field]['tmp_name'],$dest)) return null;
  return 'uploads/driver_docs/'.$fname;
}
$photo_path=save_upload('photo','photo'); $cnh_path=save_upload('cnh_image','cnh');

$pdo->beginTransaction();
try{
  $pdo->prepare("INSERT INTO users (role,name,email,phone,password_hash) VALUES ('driver',?,?,?,?)")->execute([$name,$email,$phone,$hash]);
  $uid=(int)$pdo->lastInsertId();
  $pdo->prepare("INSERT INTO drivers (user_id,vehicle_type,cnh,vehicle_plate,vehicle_model,photo_path,cnh_path) VALUES (?,?,?,?,?,?,?)")
      ->execute([$uid,'moto',$cnh,$plate,$model,$photo_path,$cnh_path]);
  $pdo->commit(); respond(['ok'=>true,'user_id'=>$uid,'photo'=>$photo_path,'cnh'=>$cnh_path]);
}catch(Throwable $e){ $pdo->rollBack(); respond(['error'=>'Email already registered or upload failed'],409); }
