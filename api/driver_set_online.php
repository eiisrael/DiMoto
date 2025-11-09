<?php
declare(strict_types=1);
require __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error'=>'Method not allowed'],405);
$user = require_auth($pdo);
if ($user['role'] !== 'driver') respond(['error'=>'Only drivers'],403);

$in = json_input();
$is_online = !empty($in['is_online']) ? 1 : 0;

$stmt = $pdo->prepare("UPDATE drivers SET is_online=? WHERE user_id=?");
$stmt->execute([$is_online, $user['id']]);

respond(['ok'=>true,'is_online'=>$is_online]);
