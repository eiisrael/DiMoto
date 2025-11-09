const API = location.origin.replace(/\/public$/,'') + '/api';
let CSRF = null;

const $ = s => document.querySelector(s);
const bind = (id, ev, fn) => document.getElementById(id).addEventListener(ev, fn);

function setActive(tab){
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('show'));
  if(tab==='passenger'){ $('#tab-passenger').classList.add('active'); $('#passenger').classList.add('show'); }
  if(tab==='driver'){ $('#tab-driver').classList.add('active'); $('#driver').classList.add('show'); }
  if(tab==='auth'){ $('#tab-auth').classList.add('active'); $('#auth').classList.add('show'); }
}
bind('tab-passenger','click',()=>setActive('passenger'));
bind('tab-driver','click',()=>setActive('driver'));
bind('tab-auth','click',()=>setActive('auth'));

async function req(path, opts={}){
  const headers = {'Content-Type':'application/json'};
  if (opts.method && opts.method !== 'GET' && CSRF) headers['X-CSRF-Token'] = CSRF;
  const res = await fetch(API + path, {...opts, headers, credentials:'include'});
  const data = await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(data.error || 'Erro');
  return data;
}

async function refreshMe(){
  try{
    const me = await req('/me.php');
    $('#me-box').textContent = `Logado como ${me.user.name} (${me.user.role})`;
    // atualiza CSRF
    const c = await req('/csrf.php',{method:'POST',body:'{}'});
    CSRF = c.csrf;
  }catch(e){
    $('#me-box').textContent = 'Não autenticado';
  }
}
refreshMe();

// Login / Logout
bind('btn-login','click', async ()=>{
  try{
    const email = $('#l_email').value.trim();
    const password = $('#l_password').value;
    const r = await req('/login.php',{method:'POST',body:JSON.stringify({email,password})});
    CSRF = r.csrf;
    alert('Login ok!');
    refreshMe();
  }catch(e){ alert(e.message); }
});
bind('btn-logout','click', async ()=>{
  try{
    await req('/logout.php',{method:'POST',body:'{}'});
    CSRF = null;
    alert('Saída efetuada');
    refreshMe();
  }catch(e){ alert(e.message); }
});

// Cadastro
bind('btn-register-passenger','click', async ()=>{
  try{
    const payload = {
      role:'passenger',
      name: $('#r_name').value, email: $('#r_email').value, phone: $('#r_phone').value,
      password: $('#r_password').value
    };
    await req('/register.php',{method:'POST',body:JSON.stringify(payload)});
    alert('Passageiro cadastrado! Faça login.');
  }catch(e){ alert(e.message); }
});

bind('btn-register-driver','click', async ()=>{
  try{
    const payload = {
      role:'driver',
      name: $('#d_name').value, email: $('#d_email').value, phone: $('#d_phone').value,
      password: $('#d_password').value,
      cnh: $('#d_cnh').value, vehicle_plate: $('#d_plate').value, vehicle_model: $('#d_model').value
    };
    await req('/register.php',{method:'POST',body:JSON.stringify(payload)});
    alert('Motorista cadastrado! Faça login.');
  }catch(e){ alert(e.message); }
});

// Passageiro: solicitar
bind('btn-request','click', async ()=>{
  try{
    const payload = {
      pickup_lat: parseFloat($('#p_pickup_lat').value),
      pickup_lng: parseFloat($('#p_pickup_lng').value),
      drop_lat: parseFloat($('#p_drop_lat').value),
      drop_lng: parseFloat($('#p_drop_lng').value)
    };
    const r = await req('/request_ride.php',{method:'POST',body:JSON.stringify(payload)});
    $('#ride-id').value = r.ride_id;
    alert('Corrida criada! Aguarde um motorista aceitar.');
  }catch(e){ alert(e.message); }
});

bind('btn-poll','click', async ()=>{
  try{
    const id = parseInt($('#ride-id').value);
    const r = await req('/ride_status.php?ride_id='+id);
    const d = r.ride;
    $('#ride-status').textContent = `Status: ${d.status}` + (d.driver_name ? ` | Motorista: ${d.driver_name}` : '');
  }catch(e){ $('#ride-status').textContent='Erro: '+e.message; }
});

// Motorista: online, localização, aceitar, finalizar
bind('drv-online','change', async (ev)=>{
  try{
    await req('/driver_set_online.php',{method:'POST',body:JSON.stringify({is_online: ev.target.checked ? 1 : 0})});
  }catch(e){ alert(e.message); ev.target.checked = !ev.target.checked; }
});

bind('btn-update-loc','click', async ()=>{
  try{
    const lat = parseFloat($('#d_lat').value);
    const lng = parseFloat($('#d_lng').value);
    await req('/driver_update_location.php',{method:'POST',body:JSON.stringify({lat,lng})});
    alert('Localização atualizada.');
  }catch(e){ alert(e.message); }
});

bind('btn-accept','click', async ()=>{
  try{
    const ride_id = parseInt($('#accept_ride_id').value);
    await req('/driver_accept_ride.php',{method:'POST',body:JSON.stringify({ride_id})});
    alert('Corrida aceita! Vá até o passageiro.');
  }catch(e){ alert(e.message); }
});

bind('btn-complete','click', async ()=>{
  try{
    const ride_id = parseInt($('#complete_ride_id').value);
    const fare_cents = parseInt($('#fare_cents').value);
    await req('/ride_complete.php',{method:'POST',body:JSON.stringify({ride_id,fare_cents})});
    alert('Corrida finalizada e pagamento registrado (cash placeholder).');
  }catch(e){ alert(e.message); }
});
