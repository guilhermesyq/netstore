<?php
  session_start();
  require_once '../config/database.php';

  $stmt = $conn->query("
    SELECT j.id, j.nome, j.descricao, j.preco, j.imagem_url,
           GROUP_CONCAT(c.nome ORDER BY c.nome SEPARATOR '||') AS categorias,
           GROUP_CONCAT(c.id ORDER BY c.nome SEPARATOR ',') AS categoria_ids
    FROM jogos j
    LEFT JOIN jogos_categorias jc ON jc.jogo_id = j.id
    LEFT JOIN categorias c ON c.id = jc.categoria_id
    GROUP BY j.id
    ORDER BY j.criado_em DESC
  ");
  $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stmtImgs = $conn->query("SELECT id, jogo_id, url FROM imagens_jogo ORDER BY ordem ASC, id ASC");
  $imagensPorJogo = [];
  foreach ($stmtImgs->fetchAll(PDO::FETCH_ASSOC) as $img) {
    $imagensPorJogo[$img['jogo_id']][] = [
      'id' => (int)$img['id'],
      'url' => $img['url'],
    ];
  }

  $naBiblioteca = [];
  $noCarrinho = [];
  if (isset($_SESSION['usuario_id'])) {
    $stmtBib = $conn->prepare("SELECT jogo_id FROM biblioteca WHERE usuario_id = ?");
    $stmtBib->execute([$_SESSION['usuario_id']]);
    $naBiblioteca = array_flip($stmtBib->fetchAll(PDO::FETCH_COLUMN));

    $stmtCar = $conn->prepare("SELECT jogo_id FROM carrinho WHERE usuario_id = ?");
    $stmtCar->execute([$_SESSION['usuario_id']]);
    $noCarrinho = array_flip($stmtCar->fetchAll(PDO::FETCH_COLUMN));
  }

  $jogosJson = [];
  $todasCategorias = [];
  foreach ($jogos as $j) {
    $cats = !empty($j['categorias']) ? explode('||', $j['categorias']) : [];
    foreach ($cats as $c) {
      $todasCategorias[$c] = true;
    }
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
      'na_biblioteca' => isset($naBiblioteca[$j['id']]),
      'no_carrinho' => isset($noCarrinho[$j['id']]),
    ];
  }

  $categoriasFiltro = array_keys($todasCategorias);
  sort($categoriasFiltro, SORT_LOCALE_STRING);
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetStore</title>
    <link rel="icon" type="image/png" href="/netstore/assets/logosite.png">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-zinc-950 text-zinc-100">
    <?php include '../components/menu.php'; ?>

    <main class="px-6 py-12 lg:px-12">
      <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
        <div>
          <h1 class="text-3xl font-bold sm:text-4xl">Catálogo de jogos</h1>
          <p class="mt-3 text-zinc-400">Confira os jogos disponíveis na NetStore.</p>
          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
          <button id="open-jogo-modal" type="button" class="mt-5 rounded-lg bg-blue-500 px-5 py-3 text-sm font-medium text-white hover:bg-blue-600">Adicionar jogo</button>
          <?php include '../components/modal_jogo.php'; ?>
          <?php endif; ?>
        </div>
        <label class="block text-sm text-zinc-300 sm:w-72">Pesquisar jogo
          <input id="pesquisar-jogo" type="search" placeholder="Nome ou categoria" class="mt-2 block w-full rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-3 focus:border-blue-400 focus:outline-none">
        </label>
      </div>

      <?php if (empty($jogos)): ?>
        <section aria-label="GameList" class="mt-8 rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-lg font-medium">O catálogo está vazio</h2>
        </section>
      <?php else: ?>
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
          <button
            id="toggle-filtros-btn"
            type="button"
            aria-expanded="false"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-200 hover:border-zinc-600 hover:text-white"
          >
            <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
            </svg>
            <span>Filtros</span>
            <span id="filtros-badge" class="hidden rounded-full bg-blue-500 px-2 py-0.5 text-xs font-semibold text-white">0</span>
            <svg id="filtros-arrow" class="h-4 w-4 text-zinc-400 transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>

          <label class="flex shrink-0 items-center gap-2 text-sm text-zinc-400">
            <span>Ordenar:</span>
            <select id="ordenar-jogos" class="rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 focus:border-blue-400 focus:outline-none">
              <option value="recentes">Mais recentes</option>
              <option value="menor_preco">Menor preço</option>
              <option value="maior_preco">Maior preço</option>
              <option value="nome_az">Nome (A–Z)</option>
            </select>
          </label>
        </div>

        <div id="painel-filtros" class="hidden mt-4 rounded-xl border border-zinc-800 bg-zinc-900 p-5">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="md:col-span-2">
              <span class="block text-sm font-medium text-zinc-300">Categorias</span>
              <div id="filtro-categorias" class="mt-2.5 flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  data-cat-filtro=""
                  class="rounded-lg border border-blue-500 bg-blue-500/15 px-3 py-1.5 text-xs font-medium text-blue-400"
                >
                  Todas
                </button>
                <?php foreach ($categoriasFiltro as $cat): ?>
                  <button
                    type="button"
                    data-cat-filtro="<?= htmlspecialchars($cat) ?>"
                    class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-xs font-medium text-zinc-300 hover:border-zinc-500 hover:text-white"
                  >
                    <?= htmlspecialchars($cat) ?>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>

            <div>
              <span class="block text-sm font-medium text-zinc-300">Preço (R$)</span>
              <div class="mt-2.5 grid grid-cols-2 gap-3">
                <label class="block text-xs text-zinc-400">Mínimo
                  <input id="filtro-preco-min" type="number" min="0" step="0.01" placeholder="0,00" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-blue-400 focus:outline-none">
                </label>
                <label class="block text-xs text-zinc-400">Máximo
                  <input id="filtro-preco-max" type="number" min="0" step="0.01" placeholder="Sem limite" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-blue-400 focus:outline-none">
                </label>
              </div>
            </div>
          </div>

          <div class="mt-5 flex justify-end border-t border-zinc-800 pt-4">
            <button
              id="limpar-filtros-btn"
              type="button"
              class="rounded-lg border border-zinc-700 px-3.5 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-800 hover:text-white"
            >
              Limpar filtros
            </button>
          </div>
        </div>

        <section id="catalogo-grid" aria-label="GameList" class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <?php foreach ($jogos as $idx => $j):
            $info = $jogosJson[$j['id']];
          ?>
            <article
              data-jogo-id="<?= $info['id'] ?>"
              data-busca="<?= htmlspecialchars(mb_strtolower($info['nome'] . ' ' . implode(' ', $info['categorias']))) ?>"
              data-categorias="<?= htmlspecialchars(implode('||', $info['categorias'])) ?>"
              data-preco="<?= htmlspecialchars($info['preco']) ?>"
              data-nome="<?= htmlspecialchars(mb_strtolower($info['nome'])) ?>"
              data-ordem="<?= $idx ?>"
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
                  <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">
                    Sem imagem
                  </div>
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

                <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                  <span class="text-base font-bold text-blue-400">
                    <?= htmlspecialchars($info['preco_formatado']) ?>
                  </span>
                  <?php if ($info['na_biblioteca']): ?>
                    <span class="rounded-md bg-emerald-500/15 px-2 py-0.5 text-xs font-medium text-emerald-400">Na biblioteca</span>
                  <?php elseif ($info['no_carrinho']): ?>
                    <span class="rounded-md bg-blue-500/15 px-2 py-0.5 text-xs font-medium text-blue-400">No carrinho</span>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </section>

        <section id="sem-resultados" class="hidden mt-8 rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-20 text-center">
          <h2 class="text-lg font-medium text-zinc-400">Nenhum jogo encontrado para essa busca</h2>
        </section>

        <script>
          window.JOGOS_CATALOGO = <?= json_encode($jogosJson, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

          (() => {
            const inputBusca = document.getElementById('pesquisar-jogo');
            const selectOrdem = document.getElementById('ordenar-jogos');
            const toggleFiltrosBtn = document.getElementById('toggle-filtros-btn');
            const painelFiltros = document.getElementById('painel-filtros');
            const filtrosArrow = document.getElementById('filtros-arrow');
            const filtrosBadge = document.getElementById('filtros-badge');
            const inputPrecoMin = document.getElementById('filtro-preco-min');
            const inputPrecoMax = document.getElementById('filtro-preco-max');
            const limparFiltrosBtn = document.getElementById('limpar-filtros-btn');
            const botoesCat = document.querySelectorAll('#filtro-categorias [data-cat-filtro]');
            const grid = document.getElementById('catalogo-grid');
            const cards = Array.from(document.querySelectorAll('#catalogo-grid [data-jogo-id]'));
            const semResultados = document.getElementById('sem-resultados');

            const categoriasSelecionadas = new Set();

            const normalizar = (texto) =>
              (texto || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            const atualizarEstilosCategorias = () => {
              botoesCat.forEach((btn) => {
                const cat = btn.getAttribute('data-cat-filtro');
                const ativo = cat === ''
                  ? categoriasSelecionadas.size === 0
                  : categoriasSelecionadas.has(cat);

                btn.className = ativo
                  ? 'rounded-lg border border-blue-500 bg-blue-500/15 px-3 py-1.5 text-xs font-medium text-blue-400'
                  : 'rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-xs font-medium text-zinc-300 hover:border-zinc-500 hover:text-white';
              });
            };

            const atualizarBadgeFiltros = () => {
              let ativos = categoriasSelecionadas.size;
              if (inputPrecoMin.value.trim() !== '') ativos++;
              if (inputPrecoMax.value.trim() !== '') ativos++;

              filtrosBadge.textContent = String(ativos);
              filtrosBadge.classList.toggle('hidden', ativos === 0);
            };

            const aplicarFiltrosEOrdem = () => {
              const termo = normalizar(inputBusca.value.trim());
              const ordem = selectOrdem.value;
              const minRaw = inputPrecoMin.value.trim();
              const maxRaw = inputPrecoMax.value.trim();
              const precoMin = minRaw !== '' ? parseFloat(minRaw) : null;
              const precoMax = maxRaw !== '' ? parseFloat(maxRaw) : null;

              const ordenados = [...cards].sort((a, b) => {
                if (ordem === 'menor_preco') {
                  return parseFloat(a.dataset.preco) - parseFloat(b.dataset.preco);
                }
                if (ordem === 'maior_preco') {
                  return parseFloat(b.dataset.preco) - parseFloat(a.dataset.preco);
                }
                if (ordem === 'nome_az') {
                  return normalizar(a.dataset.nome).localeCompare(normalizar(b.dataset.nome));
                }
                return parseInt(a.dataset.ordem, 10) - parseInt(b.dataset.ordem, 10);
              });

              let visiveis = 0;
              ordenados.forEach((card) => {
                const textoCard = normalizar(card.dataset.busca);
                const catsCard = card.dataset.categorias ? card.dataset.categorias.split('||') : [];
                const precoCard = parseFloat(card.dataset.preco);

                const matchBusca = termo === '' || textoCard.includes(termo);
                const matchCat =
                  categoriasSelecionadas.size === 0 ||
                  [...categoriasSelecionadas].every((c) => catsCard.includes(c));
                const matchMin = precoMin === null || isNaN(precoMin) || precoCard >= precoMin;
                const matchMax = precoMax === null || isNaN(precoMax) || precoCard <= precoMax;
                const visivel = matchBusca && matchCat && matchMin && matchMax;

                card.classList.toggle('hidden', !visivel);
                grid.appendChild(card);
                if (visivel) visiveis++;
              });

              grid.classList.toggle('hidden', visiveis === 0);
              semResultados.classList.toggle('hidden', visiveis > 0);
              atualizarBadgeFiltros();
            };

            toggleFiltrosBtn?.addEventListener('click', () => {
              const isHidden = painelFiltros.classList.toggle('hidden');
              filtrosArrow.classList.toggle('rotate-180', !isHidden);
              toggleFiltrosBtn.setAttribute('aria-expanded', String(!isHidden));
            });

            botoesCat.forEach((btn) => {
              btn.addEventListener('click', () => {
                const cat = btn.getAttribute('data-cat-filtro');
                if (cat === '') {
                  categoriasSelecionadas.clear();
                } else if (categoriasSelecionadas.has(cat)) {
                  categoriasSelecionadas.delete(cat);
                } else {
                  categoriasSelecionadas.add(cat);
                }
                atualizarEstilosCategorias();
                aplicarFiltrosEOrdem();
              });
            });

            limparFiltrosBtn?.addEventListener('click', () => {
              categoriasSelecionadas.clear();
              inputPrecoMin.value = '';
              inputPrecoMax.value = '';
              atualizarEstilosCategorias();
              aplicarFiltrosEOrdem();
            });

            inputBusca?.addEventListener('input', aplicarFiltrosEOrdem);
            inputBusca?.addEventListener('search', aplicarFiltrosEOrdem);
            inputPrecoMin?.addEventListener('input', aplicarFiltrosEOrdem);
            inputPrecoMax?.addEventListener('input', aplicarFiltrosEOrdem);
            selectOrdem?.addEventListener('change', aplicarFiltrosEOrdem);
          })();
        </script>

        <?php include '../components/modal_detalhes_jogo.php'; ?>
      <?php endif; ?>
    </main>
  </body>
</html>
