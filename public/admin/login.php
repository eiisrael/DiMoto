<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Admin — DiMoto</title>
<link rel="stylesheet" href="../css/style.css"/>
<style>
  .login-wrap{height:100vh;display:grid;place-items:center;}
  .card h2{text-align:center;margin-bottom:1rem;}
  .msg-error{color:#ef4444;margin-top:8px;font-weight:600;}
  .msg-ok{color:#22c55e;margin-top:8px;font-weight:600;}
  .info { color: var(--muted); }
</style>
</head>

<body>
<header class="topbar">
  <div class="brand"><div class="badge">DM</div><span>Admin DiMoto</span></div>
</header>

<main class="login-wrap">
  <section class="card" style="max-width:420px;width:100%;">
    <h2>Login Admin</h2>
    <label class="label">E-mail</label>
    <input id="email" class="input" type="email" value="admin@dimoto.local"/>
    <label class="label mt8">Senha</label>
    <input id="password" class="input" type="password" value="admin123"/>

    <button id="btn" class="btn btn-brand btn-full mt12">Entrar</button>
    <div id="msg" class="info mt8">Use as credenciais criadas no SQL.</div>
  </section>
</main>

<script>
/*
  Observação: caminho relativo para /api/ a partir de /public/admin/
  - ../../api -> sobe de public/admin -> public -> DiMoto -> api
*/
const API = '../../api';

const btn = document.getElementById('btn');
const msg = document.getElementById('msg');
const inputEmail = document.getElementById('email');
const inputPass = document.getElementById('password');

btn.onclick = async () => {
  msg.textContent = 'Verificando...';
  msg.className = 'info mt8';
  try {
    const res = await fetch(API + '/admin_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include', // ESSENCIAL para que o cookie seja enviado/gravado
      body: JSON.stringify({
        email: inputEmail.value.trim(),
        password: inputPass.value
      })
    });

    const data = await res.json().catch(()=>({}));

    if (!res.ok) {
      // Mensagem amigável
      msg.textContent = data.error || 'Erro ao fazer login';
      msg.className = 'msg-error';
      return;
    }

    msg.textContent = 'Login efetuado com sucesso!';
    msg.className = 'msg-ok';

    // redireciona ao painel admin
    setTimeout(()=> location.href = 'index.php', 600);

  } catch (e) {
    msg.textContent = 'Falha na conexão com o servidor.';
    msg.className = 'msg-error';
  }
};
</script>
</body>
</html>
