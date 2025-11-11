<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DiMoto — Login</title>
<link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<div id="splash" class="splash-wrap">
  <div>
    <div class="logo-bubble">DM</div>
    <div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
  </div>
</div>

<main id="main" style="display:none;">
  <header class="topbar">
    <div class="brand"><div class="badge">DM</div><span>DiMoto</span></div>
  </header>

  <section class="container">
    <div class="card">
      <h2>Entrar</h2>
      <label class="label">E-mail</label>
      <input id="email" class="input" type="email" placeholder="ex: usuario@dimoto.local" value="passageiro@dimoto.local"/>

      <label class="label mt8">Senha</label>
      <input id="password" class="input" type="password" value="user123"/>

      <button id="btn" class="btn btn-brand btn-full mt12">Entrar</button>
      <div id="msg" class="info mt8"></div>

      <div class="mt16" style="text-align:center;">
        <a href="register_passenger.php" style="color:var(--brand)">Sou passageiro novo</a><br>
        <a href="register_driver.php" style="color:var(--brand)">Sou motorista novo</a>
      </div>
    </div>
  </section>
</main>

<script>
const API = '../api'; // Caminho correto para API
const splash = document.getElementById('splash');
const main = document.getElementById('main');

setTimeout(() => { splash.style.display = 'none'; main.style.display = 'block'; }, 1500);

const email = document.getElementById('email');
const password = document.getElementById('password');
const btn = document.getElementById('btn');
const msg = document.getElementById('msg');

btn.onclick = async () => {
  msg.textContent = 'Verificando...';
  msg.className = 'info mt8';
  try {
    const res = await fetch(API + '/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({
        email: email.value.trim(),
        password: password.value
      })
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      msg.textContent = data.error || 'Erro ao fazer login';
      msg.className = 'msg-error';
      return;
    }

    msg.textContent = 'Login efetuado com sucesso!';
    msg.className = 'msg-ok';

    // Verifica papel do usuário
    setTimeout(async () => {
      const ures = await fetch(API + '/me.php', { credentials: 'include' });
      const udata = await ures.json().catch(() => ({}));
      if (udata.user && udata.user.role === 'driver') {
        location.href = 'driver_dashboard.php';
      } else {
        location.href = 'passenger_dashboard.php';
      }
    }, 700);

  } catch (e) {
    msg.textContent = 'Falha na conexão com o servidor.';
    msg.className = 'msg-error';
  }
};
</script>
</body>
</html>
