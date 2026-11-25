<?php
  session_start();

  if (isset($_SESSION['usuario_id'])) {
    header("Location: /netstore/home");
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/database.php';
    
    $nome  = trim($_POST['nome'] ?? '');
    $user  = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try {
      $stmt = $conn->prepare("INSERT INTO usuarios(nome, usuario, email, senha_hash, is_admin) VALUES (?, ?, ?, ?, false)");
      $stmt->execute([$nome, $user, $email, $senhaHash]);

      $_SESSION['toast_sucesso'] = 'Cadastro realizado com sucesso! Faça login para continuar.';
      header("Location: /netstore/login");
      exit;
    } catch (PDOException $e) {
      $erro = ($e->getCode() == 23000)
        ? "Usuário ou e-mail já cadastrado."
        : "Erro ao cadastrar. Tente novamente.";

      $_SESSION['flash'] = [
        'erro' => $erro,
        'old'  => ['nome' => $nome, 'usuario' => $user, 'email' => $email],
      ];

      header("Location: /netstore/cadastro");
      exit;
    }
  }

  $flash = $_SESSION['flash'] ?? [];
  unset($_SESSION['flash']);

  $erro  = $flash['erro'] ?? '';
  $nome  = $flash['old']['nome'] ?? '';
  $user  = $flash['old']['usuario'] ?? '';
  $email = $flash['old']['email'] ?? '';

?>

<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="image/png" href="/netstore/assets/logosite.png">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-zinc-950 text-zinc-100">
    <?php include '../components/menu.php'; ?>

    <main class="mx-auto max-w-md px-6 py-12 sm:py-16">
      <section class="rounded-xl border border-zinc-800 bg-zinc-900 p-6 sm:p-8">
        <h1 class="mb-6 text-3xl font-bold">Cadastro</h1>
        <form method="POST" class="space-y-5">
          <div>
            <label for="nome" class="text-sm text-zinc-300">Nome</label>
            <input id="nome" name="nome" type="text" value="<?= htmlspecialchars($nome) ?>" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </div>
          <div>
            <label for="usuario" class="text-sm text-zinc-300">Usuário</label>
            <input id="usuario" name="usuario" type="text" value="<?= htmlspecialchars($user) ?>" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </div>
          <div>
            <label for="email" class="text-sm text-zinc-300">E-mail</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars($email) ?>" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </div>
          <div>
            <label for="senha" class="text-sm text-zinc-300">Senha</label>
            <input id="senha" name="senha" type="password" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </div>
          <button type="submit" class="w-full rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Criar conta</button>
        </form>
        <?php if (!empty($erro)): ?>
          <div class="mt-5 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            <?= htmlspecialchars($erro) ?>
          </div>
        <?php endif; ?>
        <p class="mt-6 text-center text-sm text-zinc-400">Já tem conta? <a href="/netstore/login" class="text-blue-400 hover:text-blue-300">Entrar</a></p>
      </section>
    </main>
  </body>
</html>
