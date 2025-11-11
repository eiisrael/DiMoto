<!doctype html><html lang="pt-BR"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Motorista</title>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head><body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>DiMoto — Motorista</span></div>
  <a class="btn btn-light" href="main.php">Passageiro</a>
</header>
<main class="container">
  <section class="card">
    <h2>Status</h2>
    <div class="row">
      <button id="btn-on" class="btn btn-brand btn-full">Ficar Online</button>
      <button id="btn-off" class="btn btn-ghost btn-full">Ficar Offline</button>
    </div>
    <div class="info mt8">Localização é atualizada a cada 10s quando online.</div>
    <div id="msg" class="info mt8">—</div>
  </section>
  <section class="card"><h3>Mapa</h3><div id="map" class="map"></div></section>
</main>
<script>
const API=location.origin.replace(/\/public$/,'')+'/api';
let CSRF=null, timer=null, map, meMarker;

async function req(p,o={}){const h={'Content-Type':'application/json'}; if(o.method&&o.method!=='GET'&&CSRF)h['X-CSRF-Token']=CSRF;
  const r=await fetch(API+p,{...o,headers:h,credentials:'include'}); const d=await r.json().catch(()=>({})); if(!r.ok)throw new Error(d.error||'Erro'); return d;}
(async()=>{ try{const me=await req('/me.php'); if(me.user.role!=='driver') location.href='main.php'; const c=await req('/csrf.php',{method:'POST',body:'{}'}); CSRF=c.csrf; }catch{location.href='login.php';} })();

function setMsg(t){document.getElementById('msg').textContent=t;}

function startUpdate(){
  if(timer) clearInterval(timer);
  timer=setInterval(async()=>{
    if(!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(async pos=>{
      const {latitude,longitude}=pos.coords;
      await req('/driver_update_location.php',{method:'POST',body:JSON.stringify({lat:latitude,lng:longitude})});
      if(!map){ map=L.map('map'); L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(map); map.setView([latitude,longitude],14); }
      if(meMarker){ map.removeLayer(meMarker); }
      meMarker=L.marker([latitude,longitude],{icon:L.divIcon({html:'🏍️',className:'',iconSize:[24,24]})}).addTo(map);
    });
  },10000);
}

document.getElementById('btn-on').onclick=async()=>{ try{await req('/driver_set_online.php',{method:'POST',body:JSON.stringify({is_online:1})}); setMsg('Online.'); startUpdate(); }catch(e){setMsg(e.message);} };
document.getElementById('btn-off').onclick=async()=>{ try{await req('/driver_set_online.php',{method:'POST',body:JSON.stringify({is_online:0})}); if(timer)clearInterval(timer); setMsg('Offline.'); }catch(e){setMsg(e.message);} };
</script>
</body></html>
