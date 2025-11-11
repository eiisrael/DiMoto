// DiMoto (Leaflet + OSRM, fallback Haversine)
const API = location.origin.replace(/\/public$/, '') + '/api';

let CSRF=null, ME=null;
let map, routeLayer, passengerMarker, driverMarker;
let originLatLng=null, destLatLng=null;

async function req(path, opts={}){
  const headers={'Content-Type':'application/json'};
  if (opts.method && opts.method!=='GET' && CSRF) headers['X-CSRF-Token']=CSRF;
  const res=await fetch(API+path,{...opts,headers,credentials:'include'});
  const data=await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(data.error||'Erro');
  return data;
}
function setStatus(t){ const s=document.getElementById('status'); if(s) s.textContent=t; }
function showFare(text){ const el=document.getElementById('fare'); el.classList.remove('hidden'); el.innerHTML=text; }

function setPassenger(latlng){
  if (passengerMarker) map.removeLayer(passengerMarker);
  passengerMarker = L.marker(latlng, {title:'Passageiro', icon:L.divIcon({html:'🧍',className:'',iconSize:[24,24]})}).addTo(map);
}
function setDriver(latlng){
  if (driverMarker) map.removeLayer(driverMarker);
  driverMarker = L.marker(latlng, {title:'Motorista', icon:L.divIcon({html:'🏍️',className:'',iconSize:[24,24]})}).addTo(map);
}
function clearRoute(){ if(routeLayer){ map.removeLayer(routeLayer); routeLayer=null; } }

function haversineDistanceKm(a, b){
  const R=6371; const toRad=(x)=>x*Math.PI/180;
  const dLat=toRad(b.lat-a.lat); const dLng=toRad(b.lng-a.lng);
  const s1=Math.sin(dLat/2)**2 + Math.cos(toRad(a.lat))*Math.cos(toRad(b.lat))*Math.sin(dLng/2)**2;
  return 2*R*Math.asin(Math.sqrt(s1));
}

async function routeAndFare(){
  if(!originLatLng||!destLatLng){ setStatus('Defina origem e destino.'); return; }
  clearRoute();

  // Tenta OSRM
  const url=`https://router.project-osrm.org/route/v1/driving/${originLatLng.lng},${originLatLng.lat};${destLatLng.lng},${destLatLng.lat}?overview=full&geometries=geojson`;
  let km=null, min=null, coords=null, viaOSRM=false;
  try{
    const r=await fetch(url); const j=await r.json();
    if(j.routes && j.routes.length){
      const route=j.routes[0];
      km = route.distance/1000;
      min = route.duration/60;
      coords = route.geometry.coordinates.map(([x,y])=>[y,x]);
      viaOSRM=true;
    }
  }catch{}

  // Fallback Haversine (linha reta)
  if(km===null){
    km = haversineDistanceKm(originLatLng, destLatLng);
    // estimativa de tempo média (30 km/h) => 2 min/km
    min = km * 2;
    coords = [[originLatLng.lat, originLatLng.lng],[destLatLng.lat, destLatLng.lng]];
  }

  routeLayer = L.polyline(coords, {weight:6}).addTo(map);
  map.fitBounds(routeLayer.getBounds(), {padding:[20,20]});

  // Estimar tarifa (backend com faixas + minuto + base + dinâmica)
  const fareRes = await req('/estimate_fare.php',{method:'POST',body:JSON.stringify({distance_km:km, duration_min:min})});
  const f = fareRes.fare;

  showFare(`
    <b>Estimativa</b><br/>
    Distância: ${km.toFixed(2)} km • Tempo: ${Math.round(min)} min ${viaOSRM?'(OSRM)':'(estimado)'}<br/>
    Total: <b>R$ ${f.total.toFixed(2)}</b>
  `);
}

async function requestRide(){
  if(!ME||ME.role!=='passenger'){ setStatus('Entre como passageiro para solicitar.'); return; }
  if(!originLatLng||!destLatLng){ setStatus('Informe origem e destino.'); return; }
  const r=await req('/request_ride.php',{method:'POST',body:JSON.stringify({
    pickup_lat:originLatLng.lat,pickup_lng:originLatLng.lng,drop_lat:destLatLng.lat,drop_lng:destLatLng.lng
  })});
  document.getElementById('ride_id').value=r.ride_id; setStatus('Corrida criada! Aguarde um motorista aceitar.');
}

async function pollRide(){
  const id=parseInt(document.getElementById('ride_id').value); if(!id) return;
  const r=await req('/ride_status.php?ride_id='+id); const d=r.ride;
  setStatus('Status: '+d.status+(d.driver_name?(' • Motorista '+d.driver_name):''));
  if (ME.role==='passenger' && d.current_lat && d.current_lng){
    setDriver({lat:parseFloat(d.current_lat),lng:parseFloat(d.current_lng)});
  }
}

async function initAuth(){
  try{
    const me=await req('/me.php'); ME=me.user;
    const c=await req('/csrf.php',{method:'POST',body:'{}'}); CSRF=c.csrf;
  }catch{ location.href='login.php'; }
}

export async function DiMotoInit(){
  await initAuth();

  map = L.map('map',{zoomControl:true});
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(map);

  // origem por geolocalização
  if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition((pos)=>{
      originLatLng={lat:pos.coords.latitude,lng:pos.coords.longitude};
      map.setView([originLatLng.lat,originLatLng.lng], 14);
      setPassenger(originLatLng);
      (document.getElementById('origin')).value = `${originLatLng.lat.toFixed(5)}, ${originLatLng.lng.toFixed(5)}`;
    },()=>{
      originLatLng={lat:-8.05,lng:-34.9}; // Recife fallback
      map.setView([originLatLng.lat,originLatLng.lng], 13);
      setPassenger(originLatLng);
      (document.getElementById('origin')).value = `${originLatLng.lat.toFixed(5)}, ${originLatLng.lng.toFixed(5)}`;
    },{enableHighAccuracy:true,timeout:8000});
  }else{
    originLatLng={lat:-8.05,lng:-34.9};
    map.setView([originLatLng.lat,originLatLng.lng], 13);
    setPassenger(originLatLng);
  }

  // Clique no mapa define destino
  map.on('click',(e)=>{
    destLatLng={lat:e.latlng.lat,lng:e.latlng.lng};
    (document.getElementById('destination')).value = `${destLatLng.lat.toFixed(5)}, ${destLatLng.lng.toFixed(5)}`;
    routeAndFare();
  });

  document.getElementById('btn-route').onclick = routeAndFare;
  document.getElementById('btn-request').onclick = requestRide;
  document.getElementById('btn-poll').onclick = pollRide;

  document.getElementById('btn-logout').onclick = async ()=>{
    try{ await req('/logout.php',{method:'POST',body:'{}'}); location.href='login.php'; }catch(e){ alert(e.message); }
  };

  // Inputs manuais (lat,lng)
  document.getElementById('origin').addEventListener('change', ()=>{
    const v=origin.value.split(',').map(x=>parseFloat(x.trim())); if(v.length===2 && !isNaN(v[0]) && !isNaN(v[1])){
      originLatLng={lat:v[0],lng:v[1]}; setPassenger(originLatLng); map.setView([v[0],v[1]], 14);
    }
  });
  document.getElementById('destination').addEventListener('change', ()=>{
    const v=destination.value.split(',').map(x=>parseFloat(x.trim())); if(v.length===2 && !isNaN(v[0]) && !isNaN(v[1])){
      destLatLng={lat:v[0],lng:v[1]}; routeAndFare();
    }
  });
}
window.DiMotoInit = DiMotoInit;
