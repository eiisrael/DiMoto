<?php
declare(strict_types=1); require __DIR__ . '/config.php'; require __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$sid=$_COOKIE['dimoto_admin']??''; if($sid){ $pdo->prepare("DELETE FROM admin_sessions WHERE id=?")->execute([$sid]); setcookie('dimoto_admin','',time()-3600,'/'); }
respond(['ok'=>true]);
