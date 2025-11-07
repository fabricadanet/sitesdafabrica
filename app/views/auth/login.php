<!-- app/views/auth/login.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Login — Sites da Fábrica</title>
<style>
  body{font-family:sans-serif;background:#f3f4f6;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
  .card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.06);width:320px}
  input{width:100%;padding:8px;margin:8px 0;border:1px solid #ddd;border-radius:4px}
  button{width:100%;padding:10px;background:#111;color:#fff;border:none;border-radius:4px}
  a{color:#111;text-decoration:none}
</style>
</head>
<body>
  <div class="card">
    <h3>Sites da Fábrica</h3>
    <?php if (!empty($error)): ?><div style="color:#b91c1c"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post" action="/login">
      <input type="email" name="email" placeholder="E-mail" required>
      <input type="password" name="password" placeholder="Senha" required>
      <button>Entrar</button>
    </form>
    <p style="margin-top:10px"><a href="/register">Criar conta</a></p>
  </div>
</body>
</html>
