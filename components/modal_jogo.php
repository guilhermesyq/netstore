<div
  id="jogo-modal"
  class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 py-6"
  role="dialog"
  aria-modal="true"
  aria-labelledby="jogo-modal-titulo"
>
  <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl">
    <div class="mb-5 flex items-center justify-between">
      <h2 id="jogo-modal-titulo" class="text-xl font-bold text-zinc-100">Adicionar jogo</h2>
      <button
        id="close-jogo-modal"
        type="button"
        aria-label="Fechar modal"
        class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-800 hover:text-white"
      >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
        </svg>
      </button>
    </div>

    <form id="jogo-form" class="space-y-4">
      <input id="jogo-id" name="id" type="hidden" value="">
      <div>
        <label for="jogo-nome" class="text-sm text-zinc-300">Nome do jogo</label>
        <input id="jogo-nome" name="nome" type="text" maxlength="150" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 focus:border-blue-400 focus:outline-none">
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label for="jogo-preco" class="text-sm text-zinc-300">Preço (R$)</label>
          <input id="jogo-preco" name="preco" type="number" step="0.01" min="0" value="0.00" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 focus:border-blue-400 focus:outline-none">
        </div>
        <div>
          <label for="jogo-imagem" class="text-sm text-zinc-300">Imagem de capa <span class="text-xs text-zinc-500">(opcional)</span></label>
          <input id="jogo-imagem" name="imagem_capa" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-300 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-800 file:px-3 file:py-1 file:text-xs file:font-medium file:text-zinc-200 hover:file:bg-zinc-700">
        </div>
      </div>

      <div id="jogo-capa-atual-container" class="hidden">
        <span class="text-xs text-zinc-400">Capa atual</span>
        <div class="mt-1.5 flex items-center gap-3">
          <img id="jogo-capa-atual-img" src="" alt="Capa atual" class="h-16 w-28 rounded-lg border border-zinc-700 object-cover">
          <button
            id="jogo-remover-capa-btn"
            type="button"
            class="rounded-lg border border-red-500/40 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/10"
          >
            Remover capa
          </button>
          <input id="jogo-remover-capa-input" name="remover_capa" type="hidden" value="0">
        </div>
      </div>

      <div>
        <label for="jogo-descricao" class="text-sm text-zinc-300">Descrição <span class="text-xs text-zinc-500">(opcional)</span></label>
        <textarea id="jogo-descricao" name="descricao" rows="3" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 focus:border-blue-400 focus:outline-none"></textarea>
      </div>

      <div>
        <span class="text-sm text-zinc-300">Categorias</span>
        <div id="jogo-categorias-lista" class="mt-2 flex flex-wrap gap-2 text-sm text-zinc-400"></div>
        <input
          id="jogo-novas-categorias"
          name="novas_categorias"
          type="text"
          placeholder="Novas categorias separadas por vírgula (ex: Ação, RPG)"
          class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-blue-400 focus:outline-none"
        >
      </div>

      <div>
        <label for="jogo-imagens-extras" class="text-sm text-zinc-300">Imagens extras <span class="text-xs text-zinc-500">(opcional, pode selecionar várias)</span></label>
        <input id="jogo-imagens-extras" name="imagens_extras[]" type="file" accept="image/png,image/jpeg,image/webp,image/gif" multiple class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-300 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-800 file:px-3 file:py-1 file:text-xs file:font-medium file:text-zinc-200 hover:file:bg-zinc-700">
      </div>

      <div id="jogo-extras-atuais-container" class="hidden">
        <span class="text-xs text-zinc-400">Imagens extras atuais (arraste para reordenar ou clique no × para remover)</span>
        <div id="jogo-extras-atuais-lista" class="mt-2 flex flex-wrap gap-2"></div>
        <div id="jogo-remover-extras-inputs"></div>
        <div id="jogo-ordem-extras-inputs"></div>
      </div>

      <div id="jogo-feedback" class="hidden rounded-lg px-4 py-2.5 text-sm"></div>

      <div class="flex justify-end gap-3 pt-2">
        <button
          id="cancel-jogo-modal"
          type="button"
          class="rounded-lg border border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800 hover:text-white"
        >
          Cancelar
        </button>
        <button
          id="save-jogo-btn"
          type="submit"
          class="rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50"
        >
          Adicionar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  (() => {
    const modal = document.getElementById('jogo-modal');
    const modalTitulo = document.getElementById('jogo-modal-titulo');
    const openBtn = document.getElementById('open-jogo-modal');
    const closeBtn = document.getElementById('close-jogo-modal');
    const cancelBtn = document.getElementById('cancel-jogo-modal');
    const form = document.getElementById('jogo-form');
    const saveBtn = document.getElementById('save-jogo-btn');
    const feedback = document.getElementById('jogo-feedback');
    const categoriasLista = document.getElementById('jogo-categorias-lista');

    const inputId = document.getElementById('jogo-id');
    const inputNome = document.getElementById('jogo-nome');
    const inputPreco = document.getElementById('jogo-preco');
    const inputDescricao = document.getElementById('jogo-descricao');

    const capaAtualContainer = document.getElementById('jogo-capa-atual-container');
    const capaAtualImg = document.getElementById('jogo-capa-atual-img');
    const removerCapaBtn = document.getElementById('jogo-remover-capa-btn');
    const removerCapaInput = document.getElementById('jogo-remover-capa-input');

    const extrasAtuaisContainer = document.getElementById('jogo-extras-atuais-container');
    const extrasAtuaisLista = document.getElementById('jogo-extras-atuais-lista');
    const removerExtrasInputs = document.getElementById('jogo-remover-extras-inputs');
    const ordemExtrasInputs = document.getElementById('jogo-ordem-extras-inputs');

    let modoEdicao = false;
    let extrasParaRemover = new Set();
    let draggedExtra = null;

    const limparImagensAtuais = () => {
      capaAtualContainer.classList.add('hidden');
      capaAtualImg.src = '';
      capaAtualImg.classList.remove('opacity-30', 'border-red-500');
      removerCapaInput.value = '0';
      removerCapaBtn.textContent = 'Remover capa';

      extrasAtuaisContainer.classList.add('hidden');
      extrasAtuaisLista.innerHTML = '';
      removerExtrasInputs.innerHTML = '';
      ordemExtrasInputs.innerHTML = '';
      extrasParaRemover.clear();
      draggedExtra = null;
    };

    const sincronizarOrdemExtras = () => {
      ordemExtrasInputs.innerHTML = '';
      extrasAtuaisLista.querySelectorAll('[data-extra-id]').forEach((el) => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'ordem_imagens[]';
        hidden.value = el.getAttribute('data-extra-id');
        ordemExtrasInputs.appendChild(hidden);
      });
    };

    const sincronizarRemoverExtras = () => {
      removerExtrasInputs.innerHTML = '';
      extrasParaRemover.forEach((id) => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'remover_imagens[]';
        hidden.value = id;
        removerExtrasInputs.appendChild(hidden);
      });
    };

    removerCapaBtn.addEventListener('click', () => {
      const vaiRemover = removerCapaInput.value !== '1';
      removerCapaInput.value = vaiRemover ? '1' : '0';
      capaAtualImg.classList.toggle('opacity-30', vaiRemover);
      capaAtualImg.classList.toggle('border-red-500', vaiRemover);
      removerCapaBtn.textContent = vaiRemover ? 'Desfazer remoção' : 'Remover capa';
    });

    const renderizarImagensAtuais = (jogo) => {
      limparImagensAtuais();

      if (jogo.imagem_capa) {
        capaAtualImg.src = jogo.imagem_capa;
        capaAtualContainer.classList.remove('hidden');
      }

      if (jogo.imagens_extras && jogo.imagens_extras.length) {
        extrasAtuaisContainer.classList.remove('hidden');
        jogo.imagens_extras.forEach((img) => {
          const wrapper = document.createElement('div');
          wrapper.draggable = true;
          wrapper.setAttribute('data-extra-id', img.id);
          wrapper.className = 'relative cursor-grab overflow-hidden rounded-lg border border-zinc-700 active:cursor-grabbing';

          const thumb = document.createElement('img');
          thumb.src = img.url;
          thumb.alt = '';
          thumb.draggable = false;
          thumb.className = 'pointer-events-none h-16 w-24 select-none object-cover transition';

          const btn = document.createElement('button');
          btn.type = 'button';
          btn.title = 'Remover imagem';
          btn.textContent = '×';
          btn.className = 'absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/75 text-xs font-bold text-red-400 hover:bg-red-500 hover:text-white';

          btn.addEventListener('click', () => {
            if (extrasParaRemover.has(img.id)) {
              extrasParaRemover.delete(img.id);
              thumb.classList.remove('opacity-30');
              wrapper.classList.remove('border-red-500');
              btn.textContent = '×';
              btn.title = 'Remover imagem';
            } else {
              extrasParaRemover.add(img.id);
              thumb.classList.add('opacity-30');
              wrapper.classList.add('border-red-500');
              btn.textContent = '↺';
              btn.title = 'Desfazer remoção';
            }
            sincronizarRemoverExtras();
          });

          wrapper.addEventListener('dragstart', () => {
            draggedExtra = wrapper;
            wrapper.classList.add('opacity-50', 'ring-2', 'ring-blue-400');
          });

          wrapper.addEventListener('dragend', () => {
            wrapper.classList.remove('opacity-50', 'ring-2', 'ring-blue-400');
            draggedExtra = null;
            sincronizarOrdemExtras();
          });

          wrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggedExtra || draggedExtra === wrapper) return;
            const rect = wrapper.getBoundingClientRect();
            const depois = e.clientX > rect.left + rect.width / 2;
            if (depois) {
              wrapper.after(draggedExtra);
            } else {
              wrapper.before(draggedExtra);
            }
          });

          wrapper.append(thumb, btn);
          extrasAtuaisLista.appendChild(wrapper);
        });

        sincronizarOrdemExtras();
      }
    };

    const showFeedback = (msg, isError) => {
      feedback.textContent = msg;
      feedback.className = isError
        ? 'rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-2.5 text-sm text-red-400'
        : 'rounded-lg border border-emerald-500/50 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-400';
    };

    const hideFeedback = () => {
      feedback.className = 'hidden rounded-lg px-4 py-2.5 text-sm';
      feedback.textContent = '';
    };

    const carregarCategorias = async (selecionadas = []) => {
      categoriasLista.innerHTML = '<span class="text-xs text-zinc-500">Carregando categorias...</span>';
      try {
        const res = await fetch('/netstore/action/adicionar_jogo');
        const data = await res.json();

        if (!data.ok || !data.categorias.length) {
          categoriasLista.innerHTML = '<span class="text-xs text-zinc-500">Nenhuma categoria cadastrada ainda.</span>';
          return;
        }

        const selecionadasSet = new Set(selecionadas.map(Number));
        categoriasLista.innerHTML = '';
        data.categorias.forEach((cat) => {
          const label = document.createElement('label');
          label.className = 'flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-xs text-zinc-200 hover:border-blue-400';

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'categorias[]';
          checkbox.value = cat.id;
          checkbox.checked = selecionadasSet.has(Number(cat.id));
          checkbox.className = 'accent-blue-500';

          label.append(checkbox, document.createTextNode(cat.nome));
          categoriasLista.appendChild(label);
        });
      } catch {
        categoriasLista.innerHTML = '<span class="text-xs text-red-400">Erro ao carregar categorias.</span>';
      }
    };

    const closeModal = () => {
      modal.classList.add('hidden');
      form.reset();
      inputId.value = '';
      limparImagensAtuais();
      hideFeedback();
    };

    const openAddModal = () => {
      modoEdicao = false;
      form.reset();
      inputId.value = '';
      limparImagensAtuais();
      modalTitulo.textContent = 'Adicionar jogo';
      saveBtn.textContent = 'Adicionar';
      hideFeedback();
      modal.classList.remove('hidden');
      carregarCategorias([]);
    };

    window.abrirModalEditarJogo = (jogo) => {
      modoEdicao = true;
      form.reset();
      inputId.value = jogo.id;
      inputNome.value = jogo.nome;
      inputPreco.value = jogo.preco;
      inputDescricao.value = jogo.descricao || '';
      modalTitulo.textContent = 'Editar jogo';
      saveBtn.textContent = 'Salvar alterações';
      renderizarImagensAtuais(jogo);
      hideFeedback();
      modal.classList.remove('hidden');
      carregarCategorias(jogo.categoria_ids || []);
    };

    openBtn?.addEventListener('click', openAddModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideFeedback();
      saveBtn.disabled = true;

      const endpoint = modoEdicao
        ? '/netstore/action/editar_jogo'
        : '/netstore/action/adicionar_jogo';

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          body: new FormData(form),
        });
        const data = await res.json();

        if (data.ok) {
          closeModal();
          sessionStorage.setItem('netstore_toast', data.mensagem);
          window.location.reload();
        } else {
          showFeedback(data.erro || 'Erro ao salvar jogo.', true);
        }
      } catch {
        showFeedback('Erro de conexão. Tente novamente.', true);
      } finally {
        saveBtn.disabled = false;
      }
    });
  })();
</script>
