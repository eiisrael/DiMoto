<?php
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/helpers.php';
require_once __DIR__ . '/../api/middleware.php';
$user = require_auth($pdo);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Motorista</title>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>Motorista: <?= htmlspecialchars($user['name']) ?></span></div>
  <form method="post" action="../api/logout.php"><button class="btn btn-light">Sair</button></form>
</header>

<main class="container">
  <div class="card">
    <h2>Status</h2>
    <div id="status">🟢 Disponível</div>
    <button id="toggle" class="btn btn-brand btn-full mt12">Alternar Status</button>
  </div>
  <div id="map" style="height:400px;border-radius:12px;border:1px solid var(--line);margin-top:12px;"></div>
</main>

<script>
let online = true;
document.getElementById('toggle').onclick = () => {
  online = !online;
  document.getElementById('status').textContent = online ? '🟢 Disponível' : '🔴 Offline';
  // aqui você poderia fazer POST para atualizar status no backend
};

// mapa e marcador
let mapDriver = L.map('map').setView([-8.05,-34.9], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(mapDriver);
let marker;
function updatePosition() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
      const latlng = [pos.coords.latitude, pos.coords.longitude];
      if (!marker) {
        marker = L.marker(latlng, {icon: L.divIcon({html:'🏍️', className:'', iconSize:[24,24]})}).addTo(mapDriver);
      } else {
        marker.setLatLng(latlng);
      }
      mapDriver.setView(latlng, 14);
      // poderia fazer POST para api/driver_update_location.php
    });
  }
}
updatePosition();
setInterval(updatePosition, 10000);
</script>
</body>
</html>
