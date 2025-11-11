<!doctype html><html lang="pt-BR"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Cadastro Motorista</title>
<link rel="stylesheet" href="css/style.css"/></head><body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>DiMoto</span></div>
  <a class="btn btn-light" href="login.php">Entrar</a>
</header>
<main class="container">
  <section class="card">
    <h2>Criar conta — Motorista (MOTO)</h2>
    <div class="info">Envie foto e CNH. Tipo de veículo: <b>MOTO</b>.</div>
    <label class="label mt8">Nome</label><input id="d_name" class="input" type="text"/>
    <label class="label mt8">E-mail</label><input id="d_email" class="input" type="email"/>
    <label class="label mt8">Telefone</label><input id="d_phone" class="input" type="text"/>
    <label class="label mt8">Senha</label><input id="d_password" class="input" type="password"/>
    <div class="row mt8"><div style="flex:1">
      <label class="label">Placa</label><input id="d_plate" class="input" type="text" placeholder="ABC1D23"/></div><div style="flex:1">
      <label class="label">Modelo</label><input id="d_model" class="input" type="text" placeholder="CG 160"/></div></div>
    <label class="label mt8">CNH (número)</label><input id="d_cnh" class="input" type="text"/>
    <label class="label mt8">Foto do motorista</label><input id="photo" class="input" type="file" accept="image/*"/>
    <label class="label mt8">Foto da CNH</label><input id="cnh_image" class="input" type="file" accept="image/*"/>
    <button id="btn" class="btn btn-brand btn-full mt12">Cadastrar motorista</button>
    <div id="msg" class="info mt8"></div>
  </section>
</main>
<script>
async function postMultipart(url,fd){const r=await fetch(url,{method:'POST',credentials:'include',body:fd});const d=await r.json().catch(()=>({}));if(!r.ok)throw new Error(d.error||'Erro');return d;}
btn.onclick=async()=>{
  const fd=new FormData(); fd.append('name',d_name.value.trim()); fd.append('email',d_email.value.trim());
  fd.append('phone',d_phone.value.trim()); fd.append('password',d_password.value);
  fd.append('vehicle_plate',d_plate.value.trim().toUpperCase()); fd.append('vehicle_model',d_model.value.trim()); fd.append('cnh',d_cnh.value.trim());
  if(photo.files[0]) fd.append('photo',photo.files[0]); if(cnh_image.files[0]) fd.append('cnh_image',cnh_image.files[0]);
  try{await postMultipart('../api/register_driver_upload.php',fd); msg.textContent='Motorista cadastrado! Faça login.'; setTimeout(()=>location.href='login.php',900);}catch(e){msg.textContent=e.message;}
};
</script>
</body></html>
