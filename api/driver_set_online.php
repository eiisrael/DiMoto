<?php
declare(strict_types=1); require __DIR__ . '/middleware.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$u=require_auth($pdo); if($u['role']!=='driver') respond(['error'=>'Only drivers'],403);
$in=json_input(); $on=!empty($in['is_online'])?1:0;
$pdo->prepare("UPDATE drivers SET is_online=? WHERE user_id=?")->execute([$on,$u['id']]);
respond(['ok'=>true,'is_online'=>$on]);
