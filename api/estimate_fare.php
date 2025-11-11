<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/fare_utils.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') respond(['error'=>'Method not allowed'],405);
$in=json_input();
$km=isset($in['distance_km'])?floatval($in['distance_km']):0.0;
$min=isset($in['duration_min'])?floatval($in['duration_min']):0.0;
if ($km<=0 || $min<0) respond(['error'=>'Invalid distance/duration'],422);

$fare=fare_calculate($pdo,$km,$min);
respond(['fare'=>$fare]);
