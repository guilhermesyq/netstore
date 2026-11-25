<?php
  session_start();
  header('Content-Type: application/json; charset=utf-8');

  if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Acesso negado.']);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
    exit;
  }

  require_once '../config/database.php';

  $jogoId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  if (!$jogoId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Jogo inválido.']);
    exit;
  }

  try {
    $conn->beginTransaction();

    $conn->prepare("DELETE FROM jogos_categorias WHERE jogo_id = ?")->execute([$jogoId]);
    $conn->prepare("DELETE FROM imagens_jogo WHERE jogo_id = ?")->execute([$jogoId]);
    $conn->prepare("DELETE FROM carrinho WHERE jogo_id = ?")->execute([$jogoId]);

    $stmt = $conn->prepare("DELETE FROM jogos WHERE id = ?");
    $stmt->execute([$jogoId]);

    if ($stmt->rowCount() === 0) {
      $conn->rollBack();
      http_response_code(404);
      echo json_encode(['ok' => false, 'erro' => 'Jogo não encontrado.']);
      exit;
    }

    $conn->commit();

    // Remover pasta de imagens do jogo (uploads/jogos/<id>/)
    $pastaJogo = dirname(__DIR__) . '/uploads/jogos/' . $jogoId;
    if (is_dir($pastaJogo)) {
      foreach (glob($pastaJogo . '/*') as $arquivo) {
        if (is_file($arquivo)) {
          @unlink($arquivo);
        }
      }
      @rmdir($pastaJogo);
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Jogo excluído com sucesso!']);
  } catch (PDOException $e) {
    if ($conn->inTransaction()) {
      $conn->rollBack();
    }
    if ($e->getCode() == 23000) {
      http_response_code(409);
      echo json_encode(['ok' => false, 'erro' => 'Não é possível excluir um jogo que já foi comprado por usuários.']);
    } else {
      http_response_code(500);
      echo json_encode(['ok' => false, 'erro' => 'Erro ao excluir jogo. Tente novamente.']);
    }
  }
