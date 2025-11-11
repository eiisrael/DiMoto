<?php
declare(strict_types=1);
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/helpers.php';
require_once __DIR__ . '/../api/middleware.php';

// Autentica e captura dados do usuário logado
$user = require_auth($pdo);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Passageiro</title>
<link rel="stylesheet" href="css/style.css"/>
<style>
.map-sim{
  height:300px;border-radius:12px;border:1px solid var(--line);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted);background:#181818;margin-top:10px;
}
</style>
</head>
<body>
<header class="topbar">
  <div class="brand">
    <div class="badge">DM</div>
    <span>Bem-vindo(a), <?= htmlspecialchars($user['name']) ?></span>
  </div>
  <form method="post" action="../api/logout.php">
    <button class="btn btn-light" style="font-size:.9rem;">Sair</button>
  </form>
</header>

<main class="container">
  <div class="card">
    <h2>Simulador de Corrida</h2>
    <p>Calcule o valor estimado da viagem:</p>
    <div class="row">
      <div style="flex:1">
        <label class="label">Distância (km)</label>
        <input id="dist" class="input" type="number" value="8" min="1" step="0.1">
      </div>
      <div style="flex:1">
        <label class="label">Tempo (min)</label>
        <input id="tempo" class="input" type="number" value="20" min="1">
      </div>
    </div>
    <button id="calc" class="btn btn-brand btn-full mt12">Calcular Tarifa</button>
    <div id="res" class="info mt8"></div>
  </div>
  <div class="map-sim">🧭 Mapa Simulado (localização em breve)</div>
</main>

<script>
const tarifa_base = 3.00;
const valor_minuto = 0.25;
const faixa1 = 2.00; // até 5 km
const faixa2 = 1.50; // 5 a 15 km
const faixa3 = 1.00; // acima de 15 km

document.getElementById('calc').onclick = () => {
  const km = parseFloat(dist.value);
  const min = parseFloat(tempo.value);

  let valor_km = faixa3;
  if (km <= 5) valor_km = faixa1;
  else if (km <= 15) valor_km = faixa2;

  const total = tarifa_base + (valor_km * km) + (valor_minuto * min);
  res.textContent = `💰 Valor estimado: R$ ${total.toFixed(2).replace('.', ',')}`;
};
</script>
</body>
</html>
