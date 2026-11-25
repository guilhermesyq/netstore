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
  require_once '../helpers/upload.php';

  $jogoId          = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  $nome            = trim($_POST['nome'] ?? '');
  $descricao       = trim($_POST['descricao'] ?? '');
  $precoRaw        = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
  $categoriasIds   = $_POST['categorias'] ?? [];
  $novasCategorias = trim($_POST['novas_categorias'] ?? '');
  $removerCapa     = ($_POST['remover_capa'] ?? '0') === '1';
  $removerImagens  = $_POST['remover_imagens'] ?? [];
  $ordemImagens    = $_POST['ordem_imagens'] ?? [];

  if (!$jogoId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Jogo inválido.']);
    exit;
  }

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
  $uploadDir = dirname(__DIR__) . '/uploads/jogos/' . $jogoId;
  $publicPrefix = '/netstore/uploads/jogos/' . $jogoId . '/';
  $arquivosSalvos = [];
  $urlsParaApagar = [];

  try {
    $conn->beginTransaction();

    $stmtCheck = $conn->prepare("SELECT id, imagem_url FROM jogos WHERE id = ?");
    $stmtCheck->execute([$jogoId]);
    $jogoAtual = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$jogoAtual) {
      $conn->rollBack();
      http_response_code(404);
      echo json_encode(['ok' => false, 'erro' => 'Jogo não encontrado.']);
      exit;
    }

    $stmt = $conn->prepare("UPDATE jogos SET nome = ?, descricao = ?, preco = ? WHERE id = ?");
    $stmt->execute([
      $nome,
      $descricao !== '' ? $descricao : null,
      $preco,
      $jogoId,
    ]);

    // Nova capa (substitui a atual) ou remoção da capa atual
    $temNovaCapa = isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($temNovaCapa) {
      if (!empty($jogoAtual['imagem_url'])) {
        $urlsParaApagar[] = $jogoAtual['imagem_url'];
      }

      $imagemUrl = salvarImagemJogo(
        $_FILES['imagem_capa']['tmp_name'],
        (int)$_FILES['imagem_capa']['size'],
        (int)$_FILES['imagem_capa']['error'],
        $uploadDir,
        $publicPrefix,
        'capa_' . bin2hex(random_bytes(4)),
        $arquivosSalvos
      );

      $conn->prepare("UPDATE jogos SET imagem_url = ? WHERE id = ?")->execute([$imagemUrl, $jogoId]);
    } elseif ($removerCapa && !empty($jogoAtual['imagem_url'])) {
      $urlsParaApagar[] = $jogoAtual['imagem_url'];
      $conn->prepare("UPDATE jogos SET imagem_url = NULL WHERE id = ?")->execute([$jogoId]);
    }

    // Remover imagens extras marcadas
    $removidasSet = [];
    if (is_array($removerImagens) && !empty($removerImagens)) {
      $stmtBuscaImg = $conn->prepare("SELECT id, url FROM imagens_jogo WHERE id = ? AND jogo_id = ?");
      $stmtDelImg = $conn->prepare("DELETE FROM imagens_jogo WHERE id = ? AND jogo_id = ?");

      foreach ($removerImagens as $imgId) {
        if (!ctype_digit((string)$imgId)) continue;
        $idInt = (int)$imgId;
        $stmtBuscaImg->execute([$idInt, $jogoId]);
        $imgRow = $stmtBuscaImg->fetch(PDO::FETCH_ASSOC);
        if ($imgRow) {
          $stmtDelImg->execute([$idInt, $jogoId]);
          $urlsParaApagar[] = $imgRow['url'];
          $removidasSet[$idInt] = true;
        }
      }
    }

    // Atualizar ordem das imagens extras mantidas
    if (is_array($ordemImagens) && !empty($ordemImagens)) {
      $stmtOrdem = $conn->prepare("UPDATE imagens_jogo SET ordem = ? WHERE id = ? AND jogo_id = ?");
      $novaOrdem = 1;
      foreach ($ordemImagens as $imgId) {
        if (!ctype_digit((string)$imgId)) continue;
        $idInt = (int)$imgId;
        if (isset($removidasSet[$idInt])) continue;
        $stmtOrdem->execute([$novaOrdem, $idInt, $jogoId]);
        $novaOrdem++;
      }
    }

    // Novas imagens extras (opcional — adiciona à galeria existente)
    if (isset($_FILES['imagens_extras']) && is_array($_FILES['imagens_extras']['name'])) {
      $stmtMax = $conn->prepare("SELECT COALESCE(MAX(ordem), 0) FROM imagens_jogo WHERE jogo_id = ?");
      $stmtMax->execute([$jogoId]);
      $ordem = (int)$stmtMax->fetchColumn() + 1;

      $stmtImg = $conn->prepare("INSERT INTO imagens_jogo (jogo_id, url, ordem) VALUES (?, ?, ?)");
      $total = count($_FILES['imagens_extras']['name']);

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
          'extra_' . $ordem . '_' . bin2hex(random_bytes(3)),
          $arquivosSalvos
        );

        $stmtImg->execute([$jogoId, $urlExtra, $ordem]);
        $ordem++;
      }
    }

    // Atualizar categorias
    $conn->prepare("DELETE FROM jogos_categorias WHERE jogo_id = ?")->execute([$jogoId]);
    $stmtCat = $conn->prepare("INSERT IGNORE INTO jogos_categorias (jogo_id, categoria_id) VALUES (?, ?)");

    if (is_array($categoriasIds)) {
      foreach ($categoriasIds as $catId) {
        if (ctype_digit((string)$catId)) {
          $stmtCat->execute([$jogoId, (int)$catId]);
        }
      }
    }

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

    foreach ($urlsParaApagar as $urlAntiga) {
      apagarArquivoImagemJogo($urlAntiga);
    }

    echo json_encode([
      'ok' => true,
      'mensagem' => 'Jogo atualizado com sucesso!',
    ]);
  } catch (RuntimeException $e) {
    if ($conn->inTransaction()) {
      $conn->rollBack();
    }
    foreach ($arquivosSalvos as $arq) {
      @unlink($arq);
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
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao atualizar jogo. Tente novamente.']);
  }
