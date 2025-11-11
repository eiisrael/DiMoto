<?php
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/helpers.php';
require_once __DIR__ . '/../api/middleware.php';
$user = require_auth($pdo);
?>
<!doctype html>
<html>
<head>
<title>DiMoto — Passageiro</title>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
<!-- cabeçalho com nome e botão de logout -->
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>Bem-vindo(a), <?= htmlspecialchars($user['name']) ?></span></div>
  <form method="post" action="../api/logout.php"><button class="btn btn-light">Sair</button></form>
</header>

<main class="container">
  <div class="card">
    <h2>Origem e destino</h2>
    <label class="label">Origem (lat,lng)</label>
    <input id="origin" class="input" placeholder="-8.0,-34.9">
    <label class="label">Destino (lat,lng)</label>
    <input id="destination" class="input" placeholder="-8.1,-34.95">
    <button id="btnRoute" class="btn btn-brand btn-full mt12">Traçar rota</button>
    <div id="fare" class="info mt8"></div>
  </div>
  <div id="map" style="height:400px;margin-top:10px;border-radius:12px;border:1px solid var(--line);"></div>
</main>

<script>
let map = L.map('map').setView([-8.05, -34.9], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
let routeLayer;
document.getElementById('btnRoute').onclick = async () => {
  const origin = document.getElementById('origin').value.split(',').map(parseFloat);
  const dest   = document.getElementById('destination').value.split(',').map(parseFloat);
  let km, min, coords;
  try {
    const res  = await fetch(`https://router.project-osrm.org/route/v1/driving/${origin[1]},${origin[0]};${dest[1]},${dest[0]}?overview=full&geometries=geojson`);
    const data = await res.json();
    if (data.routes && data.routes[0]) {
      km     = data.routes[0].distance/1000;
      min    = data.routes[0].duration/60;
      coords = data.routes[0].geometry.coordinates.map(([x,y])=>[y,x]);
    }
  } catch (e) {}
  // fallback Haversine se OSRM falhar
  if (!coords) {
    const toRad = deg => deg * Math.PI/180;
    const dLat  = toRad(dest[0] - origin[0]);
    const dLng  = toRad(dest[1] - origin[1]);
    const a     = Math.sin(dLat/2)**2 + Math.cos(toRad(origin[0])) * Math.cos(toRad(dest[0])) * Math.sin(dLng/2)**2;
    km = 2 * 6371 * Math.asin(Math.sqrt(a));
    min = km * 2; // estimativa: 30 km/h
    coords = [origin, dest];
  }
  if (routeLayer) routeLayer.remove();
  routeLayer = L.polyline(coords, {color:'red', weight:5}).addTo(map);
  map.fitBounds(routeLayer.getBounds());
  // tarifação
  const base=3.00, perMin=0.25;
  const t1=2.00, t2=1.50, t3=1.00;
  let fareKm;
  if (km <= 5) fareKm = km * t1;
  else if (km <= 15) fareKm = 5 * t1 + (km - 5) * t2;
  else fareKm = 5 * t1 + 10 * t2 + (km - 15) * t3;
  const total = base + fareKm + (perMin * min);
  document.getElementById('fare').textContent = `Distância ${km.toFixed(1)} km • Tempo ${min.toFixed(0)} min • Valor: R$ ${total.toFixed(2).replace('.',',')}`;
};
</script>
</body>
</html>
