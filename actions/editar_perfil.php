<?php
  session_start();
  header('Content-Type: application/json; charset=utf-8');

  if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Não autenticado.']);
    exit;
  }

  require_once '../config/database.php';

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT nome, usuario, email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
      http_response_code(404);
      echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado.']);
      exit;
    }

    echo json_encode(['ok' => true, 'dados' => $dados]);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome       = trim($_POST['nome'] ?? '');
    $user       = trim($_POST['usuario'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $senha      = $_POST['senha'] ?? '';
    $senhaAtual = $_POST['senha_atual'] ?? '';

    if ($nome === '' || $user === '' || $email === '') {
      http_response_code(400);
      echo json_encode(['ok' => false, 'erro' => 'Preencha nome, usuário e e-mail.']);
      exit;
    }

    if ($senhaAtual === '') {
      http_response_code(400);
      echo json_encode(['ok' => false, 'erro' => 'Informe sua senha atual para confirmar as alterações.']);
      exit;
    }

    $stmtCheck = $conn->prepare("SELECT senha_hash FROM usuarios WHERE id = ?");
    $stmtCheck->execute([$_SESSION['usuario_id']]);
    $atual = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$atual || !password_verify($senhaAtual, $atual['senha_hash'])) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'erro' => 'Senha atual incorreta.']);
      exit;
    }

    try {
      if ($senha !== '') {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, usuario = ?, email = ?, senha_hash = ? WHERE id = ?");
        $stmt->execute([$nome, $user, $email, $senhaHash, $_SESSION['usuario_id']]);
      } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, usuario = ?, email = ? WHERE id = ?");
        $stmt->execute([$nome, $user, $email, $_SESSION['usuario_id']]);
      }

      $_SESSION['usuario_nome'] = $nome;

      echo json_encode([
        'ok' => true,
        'nome' => $nome,
        'mensagem' => 'Perfil atualizado com sucesso!'
      ]);
    } catch (PDOException $e) {
      http_response_code(400);
      $erro = ($e->getCode() == 23000)
        ? 'Usuário ou e-mail já está em uso.'
        : 'Erro ao atualizar perfil. Tente novamente.';
      echo json_encode(['ok' => false, 'erro' => $erro]);
    }
    exit;
  }

  http_response_code(405);
  echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
