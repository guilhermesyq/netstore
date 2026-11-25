<?php
  session_start();
  header('Content-Type: application/json; charset=utf-8');

  if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Faça login para finalizar a compra.']);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
    exit;
  }

  require_once '../config/database.php';

  $userId = (int)$_SESSION['usuario_id'];

  try {
    $conn->beginTransaction();

    $stmtItens = $conn->prepare("
      SELECT j.id, j.preco
      FROM carrinho c
      INNER JOIN jogos j ON j.id = c.jogo_id
      LEFT JOIN biblioteca b ON b.usuario_id = c.usuario_id AND b.jogo_id = c.jogo_id
      WHERE c.usuario_id = ? AND b.jogo_id IS NULL
    ");
    $stmtItens->execute([$userId]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    if (empty($itens)) {
      $conn->rollBack();
      http_response_code(400);
      echo json_encode(['ok' => false, 'erro' => 'Seu carrinho está vazio.']);
      exit;
    }

    $total = 0.0;
    foreach ($itens as $item) {
      $total += (float)$item['preco'];
    }
    $totalFormatado = number_format($total, 2, '.', '');

    $stmtPedido = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
    $stmtPedido->execute([$userId, $totalFormatado]);
    $pedidoId = (int)$conn->lastInsertId();

    $stmtItemPedido = $conn->prepare("INSERT INTO itens_pedido (pedido_id, jogo_id, preco_unitario) VALUES (?, ?, ?)");
    $stmtBib = $conn->prepare("INSERT IGNORE INTO biblioteca (usuario_id, jogo_id) VALUES (?, ?)");

    foreach ($itens as $item) {
      $stmtItemPedido->execute([$pedidoId, (int)$item['id'], $item['preco']]);
      $stmtBib->execute([$userId, (int)$item['id']]);
    }

    $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ?")->execute([$userId]);

    $conn->commit();

    echo json_encode([
      'ok' => true,
      'pedido_id' => $pedidoId,
      'mensagem' => 'Compra realizada com sucesso!',
    ]);
  } catch (PDOException $e) {
    if ($conn->inTransaction()) {
      $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao finalizar a compra. Tente novamente.']);
  }
