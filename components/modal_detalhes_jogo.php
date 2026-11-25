<div
  id="detalhes-jogo-modal"
  class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/75 px-4 py-6"
  role="dialog"
  aria-modal="true"
  aria-labelledby="detalhes-jogo-titulo"
>
  <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl">
    <div class="mb-4 flex items-start justify-between gap-4">
      <div>
        <h2 id="detalhes-jogo-titulo" class="text-2xl font-bold text-zinc-100"></h2>
        <div id="detalhes-jogo-categorias" class="mt-2 flex flex-wrap gap-2"></div>
      </div>
      <button
        id="close-detalhes-modal"
        type="button"
        aria-label="Fechar modal"
        class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-800 hover:text-white"
      >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
        </svg>
      </button>
    </div>

    <div id="detalhes-galeria" class="mb-5">
      <div class="relative overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
        <img id="detalhes-imagem-principal" src="" alt="" class="aspect-video w-full object-cover">
        <div id="detalhes-sem-imagem" class="hidden aspect-video w-full items-center justify-center text-sm text-zinc-500">
          Sem imagem disponível
        </div>

        <button
          id="detalhes-img-prev"
          type="button"
          aria-label="Imagem anterior"
          class="hidden absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-black/60 p-2 text-zinc-200 hover:bg-black/80 hover:text-white"
        >
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
          </svg>
        </button>

        <button
          id="detalhes-img-next"
          type="button"
          aria-label="Próxima imagem"
          class="hidden absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-black/60 p-2 text-zinc-200 hover:bg-black/80 hover:text-white"
        >
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <div id="detalhes-miniaturas" class="mt-3 flex gap-2 overflow-x-auto pb-1"></div>
    </div>

    <div class="mb-6">
      <h3 class="text-sm font-medium text-zinc-400">Sobre o jogo</h3>
      <p id="detalhes-jogo-descricao" class="mt-2 whitespace-pre-line text-sm leading-relaxed text-zinc-200"></p>
    </div>

    <div id="detalhes-feedback" class="hidden mb-4 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-2.5 text-sm text-red-400"></div>

    <div class="flex flex-wrap items-center <?= empty($modoBiblioteca) ? 'justify-between' : 'justify-end' ?> gap-4 border-t border-zinc-800 pt-4">
      <?php if (empty($modoBiblioteca)): ?>
        <span id="detalhes-jogo-preco" class="text-2xl font-bold text-blue-400"></span>
      <?php endif; ?>
      <div class="flex flex-wrap items-center gap-3">
        <?php if (empty($modoBiblioteca)): ?>
          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <button
              id="editar-jogo-btn"
              type="button"
              class="rounded-lg border border-blue-500/50 px-4 py-2.5 text-sm font-medium text-blue-400 hover:bg-blue-500/10"
            >
              Editar
            </button>
            <button
              id="excluir-jogo-btn"
              type="button"
              class="rounded-lg border border-red-500/50 px-4 py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/10 disabled:opacity-50"
            >
              Excluir
            </button>
          <?php endif; ?>
          <?php if (isset($_SESSION['usuario_id'])): ?>
            <button
              id="detalhes-add-carrinho-btn"
              type="button"
              class="rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50"
            >
              Adicionar ao carrinho
            </button>
            <a
              id="detalhes-ver-carrinho-link"
              href="/netstore/carrinho"
              class="hidden rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-600"
            >
              Ver no carrinho
            </a>
            <a
              id="detalhes-ver-biblioteca-link"
              href="/netstore/biblioteca"
              class="hidden rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-500"
            >
              Na biblioteca
            </a>
          <?php else: ?>
            <a
              href="/netstore/login"
              class="rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-600"
            >
              Entrar para comprar
            </a>
          <?php endif; ?>
        <?php endif; ?>
        <button
          id="fechar-detalhes-btn"
          type="button"
          class="rounded-lg border border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800 hover:text-white"
        >
          Fechar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    const modal = document.getElementById('detalhes-jogo-modal');
    const closeBtn = document.getElementById('close-detalhes-modal');
    const fecharBtn = document.getElementById('fechar-detalhes-btn');
    const editarBtn = document.getElementById('editar-jogo-btn');
    const excluirBtn = document.getElementById('excluir-jogo-btn');
    const addCarrinhoBtn = document.getElementById('detalhes-add-carrinho-btn');
    const verCarrinhoLink = document.getElementById('detalhes-ver-carrinho-link');
    const verBibliotecaLink = document.getElementById('detalhes-ver-biblioteca-link');
    const feedback = document.getElementById('detalhes-feedback');

    const titulo = document.getElementById('detalhes-jogo-titulo');
    const categoriasEl = document.getElementById('detalhes-jogo-categorias');
    const descricaoEl = document.getElementById('detalhes-jogo-descricao');
    const precoEl = document.getElementById('detalhes-jogo-preco');

    const imgPrincipal = document.getElementById('detalhes-imagem-principal');
    const semImagem = document.getElementById('detalhes-sem-imagem');
    const btnPrev = document.getElementById('detalhes-img-prev');
    const btnNext = document.getElementById('detalhes-img-next');
    const miniaturas = document.getElementById('detalhes-miniaturas');

    let jogoAtual = null;
    let imagensAtuais = [];
    let indiceAtual = 0;

    const atualizarGaleria = () => {
      if (!imagensAtuais.length) {
        imgPrincipal.classList.add('hidden');
        semImagem.classList.remove('hidden');
        semImagem.classList.add('flex');
        btnPrev.classList.add('hidden');
        btnNext.classList.add('hidden');
        miniaturas.innerHTML = '';
        return;
      }

      semImagem.classList.add('hidden');
      semImagem.classList.remove('flex');
      imgPrincipal.classList.remove('hidden');
      imgPrincipal.src = imagensAtuais[indiceAtual];
      imgPrincipal.alt = titulo.textContent;

      const temVarias = imagensAtuais.length > 1;
      btnPrev.classList.toggle('hidden', !temVarias);
      btnNext.classList.toggle('hidden', !temVarias);

      miniaturas.innerHTML = '';
      if (temVarias) {
        imagensAtuais.forEach((url, idx) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = `shrink-0 overflow-hidden rounded-md border-2 ${
            idx === indiceAtual ? 'border-blue-400' : 'border-zinc-800 opacity-60 hover:opacity-100'
          }`;

          const thumb = document.createElement('img');
          thumb.src = url;
          thumb.alt = '';
          thumb.className = 'h-14 w-24 object-cover';

          btn.appendChild(thumb);
          btn.addEventListener('click', () => {
            indiceAtual = idx;
            atualizarGaleria();
          });
          miniaturas.appendChild(btn);
        });
      }
    };

    const atualizarBotaoCompra = (jogo) => {
      if (!addCarrinhoBtn) return;
      addCarrinhoBtn.classList.add('hidden');
      verCarrinhoLink.classList.add('hidden');
      verBibliotecaLink.classList.add('hidden');

      if (jogo.na_biblioteca) {
        verBibliotecaLink.classList.remove('hidden');
      } else if (jogo.no_carrinho) {
        verCarrinhoLink.classList.remove('hidden');
      } else {
        addCarrinhoBtn.classList.remove('hidden');
      }
    };

    const abrirModalJogo = (jogo) => {
      jogoAtual = jogo;
      feedback.classList.add('hidden');
      feedback.textContent = '';

      titulo.textContent = jogo.nome;
      descricaoEl.textContent = jogo.descricao || 'Sem descrição disponível.';
      if (precoEl) precoEl.textContent = jogo.preco_formatado;

      categoriasEl.innerHTML = '';
      jogo.categorias.forEach((cat) => {
        const badge = document.createElement('span');
        badge.className = 'rounded-md bg-zinc-800 px-2.5 py-0.5 text-xs font-medium text-zinc-300';
        badge.textContent = cat;
        categoriasEl.appendChild(badge);
      });

      imagensAtuais = jogo.imagens;
      indiceAtual = 0;
      atualizarGaleria();
      atualizarBotaoCompra(jogo);

      modal.classList.remove('hidden');
    };

    const closeModal = () => {
      modal.classList.add('hidden');
    };

    addCarrinhoBtn?.addEventListener('click', async () => {
      if (!jogoAtual) return;
      feedback.classList.add('hidden');
      addCarrinhoBtn.disabled = true;

      try {
        const body = new FormData();
        body.append('acao', 'adicionar');
        body.append('jogo_id', jogoAtual.id);

        const res = await fetch('/netstore/action/carrinho', {
          method: 'POST',
          body,
        });
        const data = await res.json();

        if (data.ok) {
          jogoAtual.no_carrinho = true;
          atualizarBotaoCompra(jogoAtual);
          closeModal();
          sessionStorage.setItem('netstore_toast', data.mensagem);
          window.location.reload();
        } else {
          feedback.textContent = data.erro || 'Erro ao adicionar ao carrinho.';
          feedback.classList.remove('hidden');
        }
      } catch {
        feedback.textContent = 'Erro de conexão. Tente novamente.';
        feedback.classList.remove('hidden');
      } finally {
        addCarrinhoBtn.disabled = false;
      }
    });

    editarBtn?.addEventListener('click', () => {
      if (!jogoAtual || typeof window.abrirModalEditarJogo !== 'function') return;
      const jogoParaEditar = jogoAtual;
      closeModal();
      window.abrirModalEditarJogo(jogoParaEditar);
    });

    excluirBtn?.addEventListener('click', async () => {
      if (!jogoAtual) return;
      if (!confirm(`Tem certeza que deseja excluir "${jogoAtual.nome}"?`)) return;

      feedback.classList.add('hidden');
      excluirBtn.disabled = true;

      try {
        const body = new FormData();
        body.append('id', jogoAtual.id);

        const res = await fetch('/netstore/action/excluir_jogo', {
          method: 'POST',
          body,
        });
        const data = await res.json();

        if (data.ok) {
          closeModal();
          sessionStorage.setItem('netstore_toast', data.mensagem || 'Jogo excluído com sucesso!');
          window.location.reload();
        } else {
          feedback.textContent = data.erro || 'Erro ao excluir jogo.';
          feedback.classList.remove('hidden');
        }
      } catch {
        feedback.textContent = 'Erro de conexão. Tente novamente.';
        feedback.classList.remove('hidden');
      } finally {
        excluirBtn.disabled = false;
      }
    });

    document.querySelectorAll('[data-jogo-id]').forEach((card) => {
      card.addEventListener('click', () => {
        const id = card.getAttribute('data-jogo-id');
        if (window.JOGOS_CATALOGO && window.JOGOS_CATALOGO[id]) {
          abrirModalJogo(window.JOGOS_CATALOGO[id]);
        }
      });
    });

    btnPrev.addEventListener('click', () => {
      if (!imagensAtuais.length) return;
      indiceAtual = (indiceAtual - 1 + imagensAtuais.length) % imagensAtuais.length;
      atualizarGaleria();
    });

    btnNext.addEventListener('click', () => {
      if (!imagensAtuais.length) return;
      indiceAtual = (indiceAtual + 1) % imagensAtuais.length;
      atualizarGaleria();
    });

    closeBtn.addEventListener('click', closeModal);
    fecharBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
      if (modal.classList.contains('hidden')) return;
      if (e.key === 'Escape') closeModal();
      if (e.key === 'ArrowLeft' && imagensAtuais.length > 1) btnPrev.click();
      if (e.key === 'ArrowRight' && imagensAtuais.length > 1) btnNext.click();
    });
  })();
</script>
