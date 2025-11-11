<?php
declare(strict_types=1);

// test_hash.php
// Gera um hash bcrypt para a senha informada.
// Uso (CLI): php test_hash.php yourPassword
// Uso (web): http://localhost/DiMoto/api/test_hash.php?p=yourPassword
// Ou abra no navegador e use o formulário.

// Segurança: delete este arquivo após gerar o(s) hash(es).

function safe_echo($s){
    echo htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}

if (PHP_SAPI === 'cli') {
    // CLI mode
    $pwd = $argv[1] ?? null;
    if (!$pwd) {
        fwrite(STDERR, "Uso: php test_hash.php <senha>\n");
        exit(1);
    }
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    fwrite(STDOUT, $hash . PHP_EOL);
    exit(0);
}

// Web mode
$pwd = $_GET['p'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['p'])) {
    $pwd = $_POST['p'];
}

if ($pwd) {
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    header('Content-Type: text/plain; charset=utf-8');
    safe_echo($hash);
    exit;
}

// show simple form
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gerador de hash (test_hash)</title>
<style>
  body{font-family:system-ui,Segoe UI,Roboto,Arial;background:#111;color:#eee;padding:20px}
  .box{max-width:520px;background:#161616;border:1px solid #222;padding:18px;border-radius:8px}
  input[type=text]{width:100%;padding:10px;border-radius:6px;border:1px solid #333;background:#0d0d0d;color:#fff}
  button{margin-top:10px;padding:10px;border-radius:6px;background:#ff7a00;border:none;color:#000;font-weight:700;cursor:pointer}
  pre{background:#000;padding:12px;border-radius:6px;overflow:auto;color:#9ae}
  small{color:#888}
</style>
</head>
<body>
  <div class="box">
    <h2>Gerador de hash (test_hash)</h2>
    <p>Informe a senha que quer transformar em hash <small>(bcrypt via password_hash)</small> — arquivo para uso local. <strong>Apague depois de usar.</strong></p>

    <form method="post">
      <label>Senha:</label>
      <input type="text" name="p" autocomplete="off" required placeholder="ex: user123"/>
      <button type="submit">Gerar hash</button>
    </form>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($hash)): ?>
    <h3>Hash gerado:</h3>
    <pre><?php safe_echo($hash); ?></pre>
    <p>Exemplo SQL (cole no phpMyAdmin):</p>
    <pre>UPDATE users SET password_hash = '<?php safe_echo($hash); ?>' WHERE email = 'usuario@dimoto.local';</pre>
<?php endif; ?>

    <hr>
    <p><small>Uso CLI: <code>php test_hash.php user123</code></small></p>
  </div>
</body>
</html>
