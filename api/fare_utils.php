<?php
declare(strict_types=1);

function fare_calculate(PDO $pdo, float $distance_km, float $duration_min): array {
  $cfg = $pdo->query("SELECT base, per_minute, tier1_km, tier2_km, tier3_km, surge_multiplier FROM fare_config WHERE id=1")->fetch();
  if (!$cfg) {
    $cfg = ['base'=>3.00,'per_minute'=>0.25,'tier1_km'=>2.00,'tier2_km'=>1.50,'tier3_km'=>1.00,'surge_multiplier'=>1.00];
  }
  $base=(float)$cfg['base']; $per_min=(float)$cfg['per_minute']; $tier1=(float)$cfg['tier1_km'];
  $tier2=(float)$cfg['tier2_km']; $tier3=(float)$cfg['tier3_km']; $surge=(float)$cfg['surge_multiplier'];
  $km=max(0.0,$distance_km); $t=max(0.0,$duration_min);

  $km1=min($km,5);
  $km2=max(0,min($km-5,10));
  $km3=max(0,$km-15);

  $price_km = $km1*$tier1 + $km2*$tier2 + $km3*$tier3;
  $price_t  = $t * $per_min;
  $total    = ($base + $price_km + $price_t) * $surge;

  return [
    'base'=>$base,'per_minute'=>$per_min,'tier1_km'=>$tier1,'tier2_km'=>$tier2,'tier3_km'=>$tier3,
    'surge_multiplier'=>$surge,'distance_km'=>$km,'duration_min'=>$t,'total'=>round($total,2)
  ];
}
