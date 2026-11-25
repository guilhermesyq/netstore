<div
  id="perfil-modal"
  class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
  role="dialog"
  aria-modal="true"
  aria-labelledby="perfil-modal-titulo"
>
  <div class="w-full max-w-md rounded-xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl">
    <div class="mb-5 flex items-center justify-between">
      <h2 id="perfil-modal-titulo" class="text-xl font-bold text-zinc-100">Editar Perfil</h2>
      <button
        id="close-perfil-modal"
        type="button"
        aria-label="Fechar modal"
        class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-800 hover:text-white"
      >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
        </svg>
      </button>
    </div>

    <form id="perfil-form" class="space-y-4">
      <div>
        <label for="perfil-nome" class="text-sm text-zinc-300">Nome</label>
        <input id="perfil-nome" name="nome" type="text" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 focus:border-blue-400 focus:outline-none">
      </div>
      <div>
        <label for="perfil-usuario" class="text-sm text-zinc-300">Usuário</label>
        <input id="perfil-usuario" name="usuario" type="text" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 focus:border-blue-400 focus:outline-none">
      </div>
      <div>
        <label for="perfil-email" class="text-sm text-zinc-300">E-mail</label>
        <input id="perfil-email" name="email" type="email" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 focus:border-blue-400 focus:outline-none">
      </div>
      <div>
        <label for="perfil-senha" class="text-sm text-zinc-300">Nova senha <span class="text-xs text-zinc-500">(opcional)</span></label>
        <input id="perfil-senha" name="senha" type="password" placeholder="Deixe em branco para manter" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 placeholder:text-zinc-500 focus:border-blue-400 focus:outline-none">
      </div>
      <div class="border-t border-zinc-800 pt-4">
        <label for="perfil-senha-atual" class="text-sm text-zinc-300">Senha atual</label>
        <input id="perfil-senha-atual" name="senha_atual" type="password" required placeholder="Digite sua senha atual para confirmar" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-4 py-2.5 text-zinc-100 placeholder:text-zinc-500 focus:border-blue-400 focus:outline-none">
      </div>

      <div id="perfil-feedback" class="hidden rounded-lg px-4 py-2.5 text-sm"></div>

      <div class="flex justify-end gap-3 pt-2">
        <button
          id="cancel-perfil-modal"
          type="button"
          class="rounded-lg border border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800 hover:text-white"
        >
          Cancelar
        </button>
        <button
          id="save-perfil-btn"
          type="submit"
          class="rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50"
        >
          Salvar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  (() => {
    const modal = document.getElementById('perfil-modal');
    const openBtn = document.getElementById('open-perfil-modal');
    const closeBtn = document.getElementById('close-perfil-modal');
    const cancelBtn = document.getElementById('cancel-perfil-modal');
    const form = document.getElementById('perfil-form');
    const saveBtn = document.getElementById('save-perfil-btn');
    const feedback = document.getElementById('perfil-feedback');

    const inputNome = document.getElementById('perfil-nome');
    const inputUsuario = document.getElementById('perfil-usuario');
    const inputEmail = document.getElementById('perfil-email');
    const inputSenha = document.getElementById('perfil-senha');
    const inputSenhaAtual = document.getElementById('perfil-senha-atual');
    const menuNome = document.getElementById('menu-usuario-nome');

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

    const closeModal = () => {
      modal.classList.add('hidden');
      inputSenha.value = '';
      inputSenhaAtual.value = '';
      hideFeedback();
    };

    const openModal = async () => {
      hideFeedback();
      inputSenha.value = '';
      inputSenhaAtual.value = '';
      modal.classList.remove('hidden');

      try {
        const res = await fetch('/netstore/action/editar_perfil');
        const data = await res.json();
        if (data.ok) {
          inputNome.value = data.dados.nome;
          inputUsuario.value = data.dados.usuario;
          inputEmail.value = data.dados.email;
        } else {
          showFeedback(data.erro || 'Erro ao carregar dados.', true);
        }
      } catch {
        showFeedback('Erro ao carregar dados do perfil.', true);
      }
    };

    openBtn?.addEventListener('click', openModal);
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

      try {
        const res = await fetch('/netstore/action/editar_perfil', {
          method: 'POST',
          body: new FormData(form),
        });
        const data = await res.json();

        if (data.ok) {
          if (menuNome) menuNome.textContent = data.nome;
          closeModal();
          if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(data.mensagem);
          }
        } else {
          showFeedback(data.erro || 'Erro ao salvar.', true);
        }
      } catch {
        showFeedback('Erro de conexão. Tente novamente.', true);
      } finally {
        saveBtn.disabled = false;
      }
    });
  })();
</script>
