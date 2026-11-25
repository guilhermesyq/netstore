<?php
  session_start();

  if (isset($_SESSION['usuario_id'])) {
    header("Location: /netstore/home");
    exit;
  }

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once '../config/database.php';

    $usuario = trim($_POST['usuario'] ?? '');

    $stmt = $conn->prepare("SELECT id, nome, senha_hash, is_admin FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['senha'] ?? '', $user['senha_hash'])) {
      $_SESSION['usuario_id'] = $user['id'];
      $_SESSION['usuario_nome'] = $user['nome'];
      $_SESSION['is_admin'] = $user['is_admin'];

      header("Location: /netstore/home");
      exit;
    } else {
      $_SESSION['erro'] = "Usuário ou senha inválidos.";
      $_SESSION['old_usuario'] = $usuario;

      header("Location: /netstore/login");
      exit;
    }
  }

  $erro = $_SESSION['erro'] ?? '';
  $usuario = $_SESSION['old_usuario'] ?? '';
  unset($_SESSION['erro'], $_SESSION['old_usuario']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="/netstore/assets/logosite.png">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-zinc-950 text-zinc-100">
    <?php include '../components/menu.php'; ?>

    <main class="mx-auto max-w-md px-6 py-12 sm:py-16">
      <section class="rounded-xl border border-zinc-800 bg-zinc-900 p-6 sm:p-8">
        <h1 class="mb-6 text-3xl font-bold">Login</h1>

        <form method="POST" class="space-y-5">
          <div>
            <label for="usuario" class="text-sm text-zinc-300">Usuário</label>
            <input id="usuario" name="usuario" type="text" required value="<?= htmlspecialchars($usuario) ?>" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </div>
          <div>
            <label for="senha" class="text-sm text-zinc-300">Senha</label>
            <input id="senha" name="senha" type="password" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </div>
          <button type="submit" class="w-full rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Entrar</button>
        </form>

        <?php if (!empty($erro)): ?>
          <div class="mt-5 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            <?= htmlspecialchars($erro) ?>
          </div>
        <?php endif; ?>

        <p class="mt-6 text-center text-sm text-zinc-400">Ainda não tem conta? <a href="/netstore/cadastro" class="text-blue-400 hover:text-blue-300">Criar conta</a></p>
      </section>
    </main>
  </body>
</html>
