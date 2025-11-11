<?php
declare(strict_types=1);
require_once __DIR__ . '/../api/middleware.php';
$user = require_auth($pdo);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Motorista</title>
<link rel="stylesheet" href="css/style.css"/>
<style>
.map-sim{height:300px;border-radius:12px;border:1px solid var(--line);
  display:flex;align-items:center;justify-content:center;color:var(--muted);background:#181818;margin-top:10px;}
.status{margin-top:12px;font-weight:600;}
</style>
</head>
<body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>Motorista: <?= htmlspecialchars($user['name']) ?></span></div>
  <form method="post" action="../api/logout.php"><button class="btn btn-light" style="font-size:.9rem;">Sair</button></form>
</header>

<main class="container">
  <div class="card">
    <h2>Status de Corridas</h2>
    <div id="status" class="status">🟢 Disponível para receber corridas</div>
    <button id="toggle" class="btn btn-brand btn-full mt12">Alternar Status</button>
  </div>

  <div class="map-sim">
    🛵 Mapa Simulado do Motorista
  </div>
</main>

<script>
let disponivel = true;
toggle.onclick = () => {
  disponivel = !disponivel;
  status.textContent = disponivel ? '🟢 Disponível para receber corridas' : '🔴 Ocupado / offline';
};
</script>
</body>
</html>
