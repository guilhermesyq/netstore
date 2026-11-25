<?php
  session_start();
  header('Content-Type: application/json; charset=utf-8');

  if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Faça login para usar o carrinho.']);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
    exit;
  }

  require_once '../config/database.php';

  $acao   = $_POST['acao'] ?? 'adicionar';
  $jogoId = filter_input(INPUT_POST, 'jogo_id', FILTER_VALIDATE_INT);
  $userId = (int)$_SESSION['usuario_id'];

  if (!$jogoId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Jogo inválido.']);
    exit;
  }

  try {
    if ($acao === 'remover') {
      $stmt = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ? AND jogo_id = ?");
      $stmt->execute([$userId, $jogoId]);

      echo json_encode(['ok' => true, 'mensagem' => 'Jogo removido do carrinho.']);
      exit;
    }

    if ($acao === 'adicionar') {
      $stmtJogo = $conn->prepare("SELECT id FROM jogos WHERE id = ?");
      $stmtJogo->execute([$jogoId]);
      if (!$stmtJogo->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'erro' => 'Jogo não encontrado.']);
        exit;
      }

      $stmtBib = $conn->prepare("SELECT 1 FROM biblioteca WHERE usuario_id = ? AND jogo_id = ?");
      $stmtBib->execute([$userId, $jogoId]);
      if ($stmtBib->fetch()) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'Você já possui este jogo na sua biblioteca.']);
        exit;
      }

      $stmtCar = $conn->prepare("SELECT 1 FROM carrinho WHERE usuario_id = ? AND jogo_id = ?");
      $stmtCar->execute([$userId, $jogoId]);
      if ($stmtCar->fetch()) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'Este jogo já está no seu carrinho.']);
        exit;
      }

      $stmtAdd = $conn->prepare("INSERT INTO carrinho (usuario_id, jogo_id) VALUES (?, ?)");
      $stmtAdd->execute([$userId, $jogoId]);

      echo json_encode(['ok' => true, 'mensagem' => 'Jogo adicionado ao carrinho!']);
      exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Ação inválida.']);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao atualizar o carrinho. Tente novamente.']);
  }
