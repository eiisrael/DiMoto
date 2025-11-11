<!doctype html><html lang="pt-BR"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Admin — DiMoto</title>
<link rel="stylesheet" href="../css/style.css"/></head><body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>Admin DiMoto</span></div>
  <button id="logout" class="btn btn-light">Sair</button>
</header>
<main class="container">
  <section class="card">
    <h2>Tarifas</h2>
    <div id="fare" class="info">Carregando…</div>
    <div class="row mt12">
      <div style="flex:1"><label class="label">Base</label><input id="base" class="input" type="number" step="0.01"/></div>
      <div style="flex:1"><label class="label">R$/min</label><input id="per_minute" class="input" type="number" step="0.01"/></div>
    </div>
    <div class="row mt8">
      <div style="flex:1"><label class="label">até 5 km</label><input id="t1" class="input" type="number" step="0.01"/></div>
      <div style="flex:1"><label class="label">5–15 km</label><input id="t2" class="input" type="number" step="0.01"/></div>
      <div style="flex:1"><label class="label">> 15 km</label><input id="t3" class="input" type="number" step="0.01"/></div>
    </div>
    <div class="row mt8">
      <div style="flex:1"><label class="label">Dinâmica</label><input id="surge" class="input" type="number" step="0.1"/></div>
    </div>
    <button id="save" class="btn btn-brand btn-full mt12">Salvar</button>
    <div id="msg" class="info mt8"></div>
  </section>

  <section class="card mt16">
    <h2>Corridas recentes</h2>
    <div id="rides" class="info">Carregando…</div>
  </section>

  <section class="card mt16">
    <h2>Motoristas</h2>
    <div id="drivers" class="info">Carregando…</div>
  </section>
</main>
<script>
const API='../../api'; // caminho fixo e correto

let CSRF=null;
async function getList(){
  const r=await fetch(API+'/admin_list.php',{credentials:'include'});
  const d=await r.json().catch(()=>({})); if(!r.ok){alert(d.error||'Erro'); location.href='login.php'; return;}
  CSRF=d.csrf;
  fare.textContent=`Base R$ ${d.fare.base} • Min R$ ${d.fare.per_minute} • Faixas: ${d.fare.tier1_km}/${d.fare.tier2_km}/${d.fare.tier3_km} • Dinâmica x${d.fare.surge_multiplier}`;
  base.value=d.fare.base; per_minute.value=d.fare.per_minute; t1.value=d.fare.tier1_km; t2.value=d.fare.tier2_km; t3.value=d.fare.tier3_km; surge.value=d.fare.surge_multiplier;

  rides.innerHTML=(d.rides.length?('<ul>'+d.rides.map(r=>`<li>#${r.id} • ${r.status} • R$ ${(r.fare_cents/100).toFixed(2)} • ${r.passenger||'-'} → ${r.driver||'-'}</li>`).join('')+'</ul>'):'Sem corridas');
  drivers.innerHTML=(d.drivers.length?('<ul>'+d.drivers.map(x=>`<li>${x.name} (${x.email}) • ${x.vehicle_plate} ${x.vehicle_model} • ${x.is_online?'online':'offline'} ${x.current_lat?`• ${x.current_lat},${x.current_lng}`:''}</li>`).join('')+'</ul>'):'Sem motoristas');
}
save.onclick=async()=>{
  const payload={base:parseFloat(base.value),per_minute:parseFloat(per_minute.value),tier1_km:parseFloat(t1.value),tier2_km:parseFloat(t2.value),tier3_km:parseFloat(t3.value),surge_multiplier:parseFloat(surge.value)};
  const r=await fetch(API+'/admin_update_fares.php',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify(payload)});
  const d=await r.json().catch(()=>({})); msg.textContent=r.ok?'Salvo!':(d.error||'Erro'); getList();
};
logout.onclick=async()=>{ await fetch(API+'/admin_logout.php',{method:'POST',credentials:'include'}); location.href='login.php'; };
getList();
</script>
</body></html>
