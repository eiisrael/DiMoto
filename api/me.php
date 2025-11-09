<?php
declare(strict_types=1);
require __DIR__ . '/middleware.php';

$user = require_auth($pdo);
respond(['user'=>[
  'id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']
]]);
