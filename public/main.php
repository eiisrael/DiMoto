<!doctype html><html lang="pt-BR"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Mapa</title>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head><body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>DiMoto</span></div>
  <div class="row" style="gap:8px">
    <label class="toggle info">Tema <input id="theme" type="checkbox" class="theme-switch"/></label>
    <a id="link-driver" class="btn btn-light" href="driver_panel.php">Motorista</a>
    <button id="btn-logout" class="btn btn-light">Sair</button>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Origem & Destino</h2>
    <div class="info">Toque no mapa para definir o destino. Você também pode digitar lat,lng.</div>
    <label class="label mt8">Origem (lat, lng)</label><input id="origin" class="input" type="text" placeholder="-8.05000, -34.90000"/>
    <label class="label mt8">Destino (lat, lng)</label><input id="destination" class="input" type="text" placeholder="-8.10000, -34.95000"/>
    <div class="row mt12"><button id="btn-route" class="btn btn-brand btn-full">Traçar rota</button><button id="btn-request" class="btn btn-ghost btn-full">Pedir DiMoto</button></div>
    <div id="fare" class="alert mt12 hidden"></div>
  </section>
  <section class="card"><h3>Mapa</h3><div id="map" class="map"></div><div class="info mt8">🧍 = passageiro • 🏍️ = motorista</div></section>
  <section class="toolbar">
    <div class="row"><input id="ride_id" class="input" type="number" placeholder="ride_id para acompanhar"/><button id="btn-poll" class="btn btn-light">Atualizar status</button></div>
    <div id="status" class="info mt8">—</div>
  </section>
</main>
<script src="js/theme.js"></script>
<script type="module">
  import { DiMotoInit } from './js/map.js';
  DiMotoInit();
</script>
<script>
const API=location.origin.replace(/\/public$/,'')+'/api';
(async()=>{try{
  const r=await fetch(API+'/me.php',{credentials:'include'}); const j=await r.json();
  if(!r.ok) throw 0; const isDriver=(j.user.role==='driver'); document.getElementById('link-driver').style.display=isDriver?'inline-flex':'none';
}catch{ location.href='login.php'; }})();
</script>
</body></html>
