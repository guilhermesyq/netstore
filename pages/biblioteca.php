<?php
  session_start();

  $jogos = [];
  $jogosJson = [];

  if (isset($_SESSION['usuario_id'])) {
    require_once '../config/database.php';

    $stmt = $conn->prepare("
      SELECT j.id, j.nome, j.descricao, j.preco, j.imagem_url, b.adquirido_em,
             GROUP_CONCAT(c.nome ORDER BY c.nome SEPARATOR '||') AS categorias,
             GROUP_CONCAT(c.id ORDER BY c.nome SEPARATOR ',') AS categoria_ids
      FROM biblioteca b
      INNER JOIN jogos j ON j.id = b.jogo_id
      LEFT JOIN jogos_categorias jc ON jc.jogo_id = j.id
      LEFT JOIN categorias c ON c.id = jc.categoria_id
      WHERE b.usuario_id = ?
      GROUP BY j.id, b.adquirido_em
      ORDER BY b.adquirido_em DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $imagensPorJogo = [];
    if (!empty($jogos)) {
      $ids = array_column($jogos, 'id');
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $stmtImgs = $conn->prepare("SELECT id, jogo_id, url FROM imagens_jogo WHERE jogo_id IN ($placeholders) ORDER BY ordem ASC, id ASC");
      $stmtImgs->execute($ids);

      foreach ($stmtImgs->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $imagensPorJogo[$img['jogo_id']][] = [
          'id' => (int)$img['id'],
          'url' => $img['url'],
        ];
      }
    }

    foreach ($jogos as $j) {
      $cats = !empty($j['categorias']) ? explode('||', $j['categorias']) : [];
      $catIds = !empty($j['categoria_ids']) ? array_map('intval', explode(',', $j['categoria_ids'])) : [];
      $extras = $imagensPorJogo[$j['id']] ?? [];
      $imagens = [];
      if (!empty($j['imagem_url'])) {
        $imagens[] = $j['imagem_url'];
      }
      foreach ($extras as $extra) {
        $imagens[] = $extra['url'];
      }

      $precoFloat = (float)$j['preco'];
      $precoFormatado = $precoFloat <= 0
        ? 'Grátis'
        : 'R$ ' . number_format($precoFloat, 2, ',', '.');

      $jogosJson[$j['id']] = [
        'id' => (int)$j['id'],
        'nome' => $j['nome'],
        'descricao' => $j['descricao'] ?? '',
        'preco' => number_format($precoFloat, 2, '.', ''),
        'preco_formatado' => $precoFormatado,
        'categorias' => $cats,
        'categoria_ids' => $catIds,
        'imagem_capa' => $j['imagem_url'] ?? '',
        'imagens_extras' => $extras,
        'imagens' => $imagens,
        'na_biblioteca' => true,
        'no_carrinho' => false,
      ];
    }
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca</title>
    <link rel="icon" type="image/png" href="/netstore/assets/logosite.png">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-zinc-950 text-zinc-100">
    <?php include '../components/menu.php'; ?>

    <main class="px-6 py-12 lg:px-12">
      <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <h1 class="text-3xl font-bold">Biblioteca</h1>
        <?php if (isset($_SESSION['usuario_id']) && !empty($jogos)): ?>
          <label class="block text-sm text-zinc-300 sm:w-72">Pesquisar jogo
            <input id="pesquisar-biblioteca" type="search" placeholder="Nome ou categoria" class="mt-2 block w-full rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-3 focus:border-blue-400 focus:outline-none">
          </label>
        <?php endif; ?>
      </div>

      <?php if (!isset($_SESSION['usuario_id'])): ?>
        <section class="rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-xl font-semibold">Entre na sua conta para ver seus jogos</h2>
          <a href="/netstore/login" class="mt-6 inline-block rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Fazer login</a>
        </section>
      <?php elseif (empty($jogos)): ?>
        <section class="rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-xl font-semibold">Nenhum jogo na biblioteca</h2>
          <a href="/netstore/home" class="mt-6 inline-block rounded-lg bg-blue-500 px-5 py-3 font-medium text-white hover:bg-blue-600">Explorar jogos</a>
        </section>
      <?php else: ?>
        <section id="biblioteca-grid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <?php foreach ($jogos as $j):
            $info = $jogosJson[$j['id']];
            $dataFmt = date('d/m/Y', strtotime($j['adquirido_em']));
          ?>
            <article
              data-jogo-id="<?= $info['id'] ?>"
              data-busca="<?= htmlspecialchars(mb_strtolower($info['nome'] . ' ' . implode(' ', $info['categorias']))) ?>"
              class="group flex cursor-pointer flex-col overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900 transition hover:border-blue-500/60"
            >
              <div class="aspect-video w-full overflow-hidden bg-zinc-950">
                <?php if (!empty($j['imagem_url'])): ?>
                  <img
                    src="<?= htmlspecialchars($j['imagem_url']) ?>"
                    alt="<?= htmlspecialchars($info['nome']) ?>"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                  >
                <?php else: ?>
                  <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Sem imagem</div>
                <?php endif; ?>
              </div>
              <div class="flex flex-1 flex-col p-4">
                <h2 class="text-lg font-semibold text-zinc-100 group-hover:text-blue-400">
                  <?= htmlspecialchars($info['nome']) ?>
                </h2>

                <?php if (!empty($info['categorias'])): ?>
                  <div class="mt-2 flex flex-wrap gap-1.5">
                    <?php foreach ($info['categorias'] as $cat): ?>
                      <span class="rounded-md bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">
                        <?= htmlspecialchars($cat) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <p class="mt-auto pt-4 text-xs text-zinc-400">Adquirido em <?= $dataFmt ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </section>

        <section id="biblioteca-sem-resultados" class="hidden rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-lg font-medium text-zinc-400">Nenhum jogo encontrado para essa busca</h2>
        </section>

        <script>
          window.JOGOS_CATALOGO = <?= json_encode($jogosJson, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

          (() => {
            const inputBusca = document.getElementById('pesquisar-biblioteca');
            const grid = document.getElementById('biblioteca-grid');
            const cards = document.querySelectorAll('#biblioteca-grid [data-jogo-id]');
            const semResultados = document.getElementById('biblioteca-sem-resultados');

            const normalizar = (texto) =>
              (texto || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            const filtrar = () => {
              const termo = normalizar(inputBusca.value.trim());
              let visiveis = 0;

              cards.forEach((card) => {
                const textoCard = normalizar(card.getAttribute('data-busca'));
                const match = termo === '' || textoCard.includes(termo);
                card.classList.toggle('hidden', !match);
                if (match) visiveis++;
              });

              grid.classList.toggle('hidden', visiveis === 0);
              semResultados.classList.toggle('hidden', visiveis > 0);
            };

            inputBusca?.addEventListener('input', filtrar);
            inputBusca?.addEventListener('search', filtrar);
          })();
        </script>

        <?php
          $modoBiblioteca = true;
          include '../components/modal_detalhes_jogo.php';
        ?>
      <?php endif; ?>
    </main>
  </body>
</html>
