<?php
  session_start();

  $itens = [];
  $total = 0.0;

  if (isset($_SESSION['usuario_id'])) {
    require_once '../config/database.php';

    $stmt = $conn->prepare("
      SELECT j.id, j.nome, j.preco, j.imagem_url
      FROM carrinho c
      INNER JOIN jogos j ON j.id = c.jogo_id
      WHERE c.usuario_id = ?
      ORDER BY j.nome ASC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itens as $item) {
      $total += (float)$item['preco'];
    }
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <link rel="icon" type="image/png" href="/netstore/assets/logosite.png">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-zinc-950 text-zinc-100">
    <?php include '../components/menu.php'; ?>

    <main class="px-6 py-12 lg:px-12">
      <h1 class="mb-6 text-3xl font-bold">Carrinho</h1>

      <?php if (!isset($_SESSION['usuario_id'])): ?>
        <section class="rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-xl font-semibold">Entre na sua conta para ver seu carrinho</h2>
          <a href="/netstore/login" class="mt-6 inline-block rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Fazer login</a>
        </section>
      <?php elseif (empty($itens)): ?>
        <section class="rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-xl font-semibold">Seu carrinho está vazio</h2>
          <a href="/netstore/home" class="mt-6 inline-block rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Explorar jogos</a>
        </section>
      <?php else: ?>
        <div id="carrinho-erro" class="hidden mb-6 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400"></div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
          <section class="space-y-4 lg:col-span-2">
            <?php foreach ($itens as $item):
              $precoFloat = (float)$item['preco'];
              $precoFmt = $precoFloat <= 0 ? 'Grátis' : 'R$ ' . number_format($precoFloat, 2, ',', '.');
            ?>
              <article class="flex flex-col gap-4 rounded-xl border border-zinc-800 bg-zinc-900 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                  <div class="h-16 w-28 shrink-0 overflow-hidden rounded-lg bg-zinc-950">
                    <?php if (!empty($item['imagem_url'])): ?>
                      <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                      <div class="flex h-full w-full items-center justify-center text-xs text-zinc-600">Sem imagem</div>
                    <?php endif; ?>
                  </div>
                  <div>
                    <h2 class="text-lg font-semibold text-zinc-100"><?= htmlspecialchars($item['nome']) ?></h2>
                    <span class="text-sm font-bold text-blue-400"><?= $precoFmt ?></span>
                  </div>
                </div>

                <button
                  type="button"
                  data-remover-jogo="<?= (int)$item['id'] ?>"
                  class="self-end rounded-lg border border-red-500/40 px-3.5 py-2 text-xs font-medium text-red-400 hover:bg-red-500/10 disabled:opacity-50 sm:self-center"
                >
                  Remover
                </button>
              </article>
            <?php endforeach; ?>
          </section>

          <aside class="h-fit rounded-xl border border-zinc-800 bg-zinc-900 p-6">
            <h2 class="text-lg font-bold text-zinc-100">Resumo do pedido</h2>
            <div class="mt-4 flex items-center justify-between text-sm text-zinc-400">
              <span>Itens (<?= count($itens) ?>)</span>
              <span class="font-medium text-zinc-200">R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-zinc-800 pt-4 text-lg font-bold">
              <span>Total</span>
              <span class="text-blue-400">R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>

            <button
              id="finalizar-compra-btn"
              type="button"
              class="mt-6 w-full rounded-lg bg-blue-500 px-5 py-3 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50"
            >
              Finalizar compra
            </button>
          </aside>
        </div>

        <script>
          (() => {
            const erroEl = document.getElementById('carrinho-erro');
            const finalizarBtn = document.getElementById('finalizar-compra-btn');

            const mostrarErro = (msg) => {
              erroEl.textContent = msg;
              erroEl.classList.remove('hidden');
            };

            document.querySelectorAll('[data-remover-jogo]').forEach((btn) => {
              btn.addEventListener('click', async () => {
                erroEl.classList.add('hidden');
                btn.disabled = true;

                try {
                  const body = new FormData();
                  body.append('acao', 'remover');
                  body.append('jogo_id', btn.getAttribute('data-remover-jogo'));

                  const res = await fetch('/netstore/action/carrinho', { method: 'POST', body });
                  const data = await res.json();

                  if (data.ok) {
                    sessionStorage.setItem('netstore_toast', data.mensagem);
                    window.location.reload();
                  } else {
                    mostrarErro(data.erro || 'Erro ao remover item.');
                    btn.disabled = false;
                  }
                } catch {
                  mostrarErro('Erro de conexão. Tente novamente.');
                  btn.disabled = false;
                }
              });
            });

            finalizarBtn?.addEventListener('click', async () => {
              erroEl.classList.add('hidden');
              finalizarBtn.disabled = true;

              try {
                const res = await fetch('/netstore/action/finalizar_compra', { method: 'POST' });
                const data = await res.json();

                if (data.ok) {
                  sessionStorage.setItem('netstore_toast', data.mensagem);
                  window.location.href = '/netstore/biblioteca';
                } else {
                  mostrarErro(data.erro || 'Erro ao finalizar a compra.');
                  finalizarBtn.disabled = false;
                }
              } catch {
                mostrarErro('Erro de conexão. Tente novamente.');
                finalizarBtn.disabled = false;
              }
            });
          })();
        </script>
      <?php endif; ?>
    </main>
  </body>
</html>
