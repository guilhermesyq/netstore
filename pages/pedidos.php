<?php
  session_start();

  if (!isset($_SESSION['usuario_id'])) {
    header("Location: /netstore/login");
    exit;
  }

  require_once '../config/database.php';

  $stmt = $conn->prepare("
    SELECT id, total, data_pedido
    FROM pedidos
    WHERE usuario_id = ?
    ORDER BY data_pedido DESC
  ");
  $stmt->execute([$_SESSION['usuario_id']]);
  $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $itensPorPedido = [];
  if (!empty($pedidos)) {
    $ids = array_column($pedidos, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmtItens = $conn->prepare("
      SELECT ip.pedido_id, ip.preco_unitario, j.nome, j.imagem_url
      FROM itens_pedido ip
      INNER JOIN jogos j ON j.id = ip.jogo_id
      WHERE ip.pedido_id IN ($placeholders)
    ");
    $stmtItens->execute($ids);

    foreach ($stmtItens->fetchAll(PDO::FETCH_ASSOC) as $item) {
      $itensPorPedido[$item['pedido_id']][] = $item;
    }
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <link rel="icon" type="image/png" href="/netstore/assets/logosite.png">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-zinc-950 text-zinc-100">
    <?php include '../components/menu.php'; ?>

    <main class="px-6 py-12 lg:px-12">
      <h1 class="mb-6 text-3xl font-bold">Pedidos</h1>

      <?php if (empty($pedidos)): ?>
        <section class="rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-xl font-semibold">Nenhum pedido encontrado</h2>
          <a href="/netstore/home" class="mt-6 inline-block rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Explorar jogos</a>
        </section>
      <?php else: ?>
        <div class="space-y-6">
          <?php
            $totalPedidos = count($pedidos);
            foreach ($pedidos as $index => $pedido):
              $numeroPedido = $totalPedidos - $index;
              $dataFmt = date('d/m/Y H:i', strtotime($pedido['data_pedido']));
              $itens = $itensPorPedido[$pedido['id']] ?? [];
          ?>
            <section class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
              <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-4">
                <div>
                  <h2 class="text-lg font-bold text-zinc-100">Pedido #<?= $numeroPedido ?></h2>
                  <p class="text-xs text-zinc-400"><?= $dataFmt ?></p>
                </div>
                <span class="text-lg font-bold text-blue-400">
                  R$ <?= number_format((float)$pedido['total'], 2, ',', '.') ?>
                </span>
              </div>

              <div class="mt-4 divide-y divide-zinc-800">
                <?php foreach ($itens as $item):
                  $precoFloat = (float)$item['preco_unitario'];
                  $precoFmt = $precoFloat <= 0 ? 'Grátis' : 'R$ ' . number_format($precoFloat, 2, ',', '.');
                ?>
                  <div class="flex items-center justify-between gap-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="h-12 w-20 shrink-0 overflow-hidden rounded-md bg-zinc-950">
                        <?php if (!empty($item['imagem_url'])): ?>
                          <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                          <div class="flex h-full w-full items-center justify-center text-[10px] text-zinc-600">Sem imagem</div>
                        <?php endif; ?>
                      </div>
                      <span class="font-medium text-zinc-200"><?= htmlspecialchars($item['nome']) ?></span>
                    </div>
                    <span class="text-sm font-semibold text-zinc-300"><?= $precoFmt ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </body>
</html>
