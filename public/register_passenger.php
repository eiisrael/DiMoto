<!doctype html><html lang="pt-BR"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Cadastro Passageiro</title>
<link rel="stylesheet" href="css/style.css"/></head><body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>DiMoto</span></div>
  <a class="btn btn-light" href="login.php">Entrar</a>
</header>
<main class="container">
  <section class="card">
    <h2>Criar conta — Passageiro</h2>
    <label class="label">Nome</label><input id="name" class="input" type="text"/>
    <label class="label mt8">E-mail</label><input id="email" class="input" type="email"/>
    <label class="label mt8">Telefone</label><input id="phone" class="input" type="text"/>
    <label class="label mt8">Senha</label><input id="password" class="input" type="password"/>
    <button id="btn" class="btn btn-brand btn-full mt12">Cadastrar</button>
    <div id="msg" class="info mt8"></div>
  </section>
</main>
<script>
const API=location.origin.replace(/\/public$/,'')+'/api';
async function req(p,o={}){const r=await fetch(API+p,{...o,headers:{'Content-Type':'application/json'},credentials:'include'});const d=await r.json().catch(()=>({}));if(!r.ok)throw new Error(d.error||'Erro');return d;}
btn.onclick=async()=>{
  const payload={role:'passenger',name:name.value.trim(),email:email.value.trim(),phone:phone.value.trim(),password:password.value};
  try{await req('/register.php',{method:'POST',body:JSON.stringify(payload)}); msg.textContent='Cadastro ok! Faça login.'; setTimeout(()=>location.href='login.php',900);}catch(e){msg.textContent=e.message;}
};
</script>
</body></html>
