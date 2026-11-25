<?php
  session_start();
  header('Content-Type: application/json; charset=utf-8');

  if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Acesso negado.']);
    exit;
  }

  require_once '../config/database.php';
  require_once '../helpers/upload.php';

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'categorias' => $categorias]);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim($_POST['nome'] ?? '');
    $descricao       = trim($_POST['descricao'] ?? '');
    $precoRaw        = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $categoriasIds   = $_POST['categorias'] ?? [];
    $novasCategorias = trim($_POST['novas_categorias'] ?? '');

    if ($nome === '') {
      http_response_code(400);
      echo json_encode(['ok' => false, 'erro' => 'Informe o nome do jogo.']);
      exit;
    }

    if (!is_numeric($precoRaw) || (float)$precoRaw < 0) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'erro' => 'Informe um preço válido.']);
      exit;
    }

    $preco = number_format((float)$precoRaw, 2, '.', '');
    $uploadDir = null;
    $arquivosSalvos = [];

    try {
      $conn->beginTransaction();

      $stmt = $conn->prepare(
        "INSERT INTO jogos (nome, descricao, preco, imagem_url, adicionado_por) VALUES (?, ?, ?, NULL, ?)"
      );
      $stmt->execute([
        $nome,
        $descricao !== '' ? $descricao : null,
        $preco,
        $_SESSION['usuario_id'],
      ]);

      $jogoId = (int)$conn->lastInsertId();
      $uploadDir = dirname(__DIR__) . '/uploads/jogos/' . $jogoId;
      $publicPrefix = '/netstore/uploads/jogos/' . $jogoId . '/';

      // Upload da capa (opcional)
      if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imagemUrl = salvarImagemJogo(
          $_FILES['imagem_capa']['tmp_name'],
          (int)$_FILES['imagem_capa']['size'],
          (int)$_FILES['imagem_capa']['error'],
          $uploadDir,
          $publicPrefix,
          'capa',
          $arquivosSalvos
        );

        $stmtCapa = $conn->prepare("UPDATE jogos SET imagem_url = ? WHERE id = ?");
        $stmtCapa->execute([$imagemUrl, $jogoId]);
      }

      // Upload das imagens extras (opcional, múltiplas)
      if (isset($_FILES['imagens_extras']) && is_array($_FILES['imagens_extras']['name'])) {
        $stmtImg = $conn->prepare("INSERT INTO imagens_jogo (jogo_id, url, ordem) VALUES (?, ?, ?)");
        $total = count($_FILES['imagens_extras']['name']);
        $ordem = 1;

        for ($i = 0; $i < $total; $i++) {
          if ($_FILES['imagens_extras']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
          }

          $urlExtra = salvarImagemJogo(
            $_FILES['imagens_extras']['tmp_name'][$i],
            (int)$_FILES['imagens_extras']['size'][$i],
            (int)$_FILES['imagens_extras']['error'][$i],
            $uploadDir,
            $publicPrefix,
            'extra_' . $ordem,
            $arquivosSalvos
          );

          $stmtImg->execute([$jogoId, $urlExtra, $ordem]);
          $ordem++;
        }
      }

      // Vincular categorias selecionadas
      $stmtCat = $conn->prepare("INSERT IGNORE INTO jogos_categorias (jogo_id, categoria_id) VALUES (?, ?)");
      if (is_array($categoriasIds)) {
        foreach ($categoriasIds as $catId) {
          if (ctype_digit((string)$catId)) {
            $stmtCat->execute([$jogoId, (int)$catId]);
          }
        }
      }

      // Criar e vincular novas categorias (separadas por vírgula)
      if ($novasCategorias !== '') {
        $stmtNovaCat = $conn->prepare("INSERT IGNORE INTO categorias (nome) VALUES (?)");
        $stmtBuscaCat = $conn->prepare("SELECT id FROM categorias WHERE nome = ?");

        foreach (explode(',', $novasCategorias) as $catNome) {
          $catNome = trim($catNome);
          if ($catNome === '') continue;

          $stmtNovaCat->execute([$catNome]);
          $stmtBuscaCat->execute([$catNome]);
          $catRow = $stmtBuscaCat->fetch(PDO::FETCH_ASSOC);
          if ($catRow) {
            $stmtCat->execute([$jogoId, (int)$catRow['id']]);
          }
        }
      }

      $conn->commit();

      echo json_encode([
        'ok' => true,
        'id' => $jogoId,
        'mensagem' => 'Jogo adicionado com sucesso!',
      ]);
    } catch (RuntimeException $e) {
      if ($conn->inTransaction()) {
        $conn->rollBack();
      }
      foreach ($arquivosSalvos as $arq) {
        @unlink($arq);
      }
      if ($uploadDir && is_dir($uploadDir)) {
        @rmdir($uploadDir);
      }
      http_response_code(400);
      echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
    } catch (PDOException $e) {
      if ($conn->inTransaction()) {
        $conn->rollBack();
      }
      foreach ($arquivosSalvos as $arq) {
        @unlink($arq);
      }
      if ($uploadDir && is_dir($uploadDir)) {
        @rmdir($uploadDir);
      }
      http_response_code(500);
      echo json_encode(['ok' => false, 'erro' => 'Erro ao adicionar jogo. Tente novamente.']);
    }
    exit;
  }

  http_response_code(405);
  echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
