<?php
  $qtdCarrinho = 0;
  if (isset($_SESSION['usuario_id'])) {
    require_once dirname(__DIR__) . '/config/database.php';
    $stmtQtdCar = $conn->prepare("SELECT COUNT(*) FROM carrinho WHERE usuario_id = ?");
    $stmtQtdCar->execute([$_SESSION['usuario_id']]);
    $qtdCarrinho = (int)$stmtQtdCar->fetchColumn();
  }

  $toastSucessoSessao = $_SESSION['toast_sucesso'] ?? '';
  unset($_SESSION['toast_sucesso']);
?>
<header class="border-b border-zinc-800 bg-zinc-900">
  <div class="flex flex-wrap items-center justify-between gap-6 px-6 py-5 lg:px-12">
    <a href="/netstore/home" class="flex items-center gap-3 text-xl font-bold">
      <img src="/netstore/assets/logosite.png" alt="" class="h-10 w-10 object-contain">
      <span>Net<span class="text-blue-400">Store</span></span>
    </a>

    <nav aria-label="Navegação principal" class="flex flex-wrap items-center gap-4 text-sm text-zinc-400">
      <a href="/netstore/home" class="hover:text-white">Home</a>
      <a href="/netstore/carrinho" class="inline-flex items-center gap-1.5 hover:text-white">
        <span>Carrinho</span>
        <?php if ($qtdCarrinho > 0): ?>
          <span class="rounded-full bg-blue-500 px-2 py-0.5 text-xs font-semibold text-white"><?= $qtdCarrinho ?></span>
        <?php endif; ?>
      </a>
      <a href="/netstore/biblioteca" class="hover:text-white">Biblioteca</a>

      <?php if (isset($_SESSION['usuario_id'])): ?>
        <div id="user-menu-container" class="relative">
          <button
            id="user-menu-btn"
            type="button"
            aria-expanded="false"
            aria-haspopup="true"
            class="group flex items-center gap-1.5 font-medium text-zinc-200 hover:text-white"
          >
            <span>Olá, <span id="menu-usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span></span>
            <svg id="user-menu-arrow" class="h-4 w-4 text-zinc-400 transition-transform group-hover:text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>

          <div
            id="user-menu-dropdown"
            class="hidden absolute right-0 z-50 mt-2 w-48 rounded-lg border border-zinc-800 bg-zinc-900 py-1 shadow-xl"
          >
            <button id="open-perfil-modal" type="button" class="block w-full px-4 py-2 text-left text-zinc-200 hover:bg-zinc-800 hover:text-white">Editar Perfil</button>
            <a href="/netstore/pedidos" class="block px-4 py-2 text-zinc-200 hover:bg-zinc-800 hover:text-white">Pedidos</a>
            <hr class="my-1 border-zinc-800">
            <a href="/netstore/action/logout" class="block px-4 py-2 text-red-400 hover:bg-red-500/10">Sair</a>
          </div>
        </div>

        <?php include __DIR__ . '/modal_perfil.php'; ?>

        <script>
          (() => {
            const container = document.getElementById('user-menu-container');
            const btn = document.getElementById('user-menu-btn');
            const dropdown = document.getElementById('user-menu-dropdown');
            const arrow = document.getElementById('user-menu-arrow');

            const closeMenu = () => {
              dropdown.classList.add('hidden');
              arrow.classList.remove('rotate-180');
              btn.setAttribute('aria-expanded', 'false');
            };

            btn.addEventListener('click', () => {
              const isHidden = dropdown.classList.toggle('hidden');
              arrow.classList.toggle('rotate-180', !isHidden);
              btn.setAttribute('aria-expanded', String(!isHidden));
            });

            document.getElementById('open-perfil-modal')?.addEventListener('click', closeMenu);

            document.addEventListener('click', (e) => {
              if (!container.contains(e.target)) closeMenu();
            });

            document.addEventListener('keydown', (e) => {
              if (e.key === 'Escape') closeMenu();
            });
          })();
        </script>
      <?php else: ?>
        <a href="/netstore/login" class="hover:text-white">Login</a>
        <a href="/netstore/cadastro" class="rounded-lg border border-zinc-700 px-4 py-2 text-zinc-100 hover:bg-zinc-800">
          Criar conta
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<div
  id="netstore-toast"
  class="hidden fixed bottom-6 right-6 z-[60] flex items-center gap-3 rounded-xl border border-emerald-500/40 bg-zinc-900 px-4 py-3 text-sm text-zinc-100 shadow-2xl"
  role="status"
  aria-live="polite"
>
  <svg class="h-5 w-5 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
  </svg>
  <span id="netstore-toast-msg" class="font-medium"></span>
  <button
    id="netstore-toast-close"
    type="button"
    aria-label="Fechar aviso"
    class="ml-1 rounded p-0.5 text-zinc-400 hover:bg-zinc-800 hover:text-white"
  >
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
      <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
    </svg>
  </button>
</div>

<script>
  (() => {
    const toast = document.getElementById('netstore-toast');
    const toastMsg = document.getElementById('netstore-toast-msg');
    const toastClose = document.getElementById('netstore-toast-close');
    let toastTimer = null;

    const esconderToast = () => {
      toast.classList.add('hidden');
      if (toastTimer) {
        clearTimeout(toastTimer);
        toastTimer = null;
      }
    };

    window.mostrarToast = (mensagem) => {
      if (!mensagem) return;
      toastMsg.textContent = mensagem;
      toast.classList.remove('hidden');
      if (toastTimer) clearTimeout(toastTimer);
      toastTimer = setTimeout(esconderToast, 4000);
    };

    toastClose.addEventListener('click', esconderToast);

    const pendenteSessao = <?= json_encode($toastSucessoSessao, JSON_UNESCAPED_UNICODE) ?>;
    if (pendenteSessao) {
      window.mostrarToast(pendenteSessao);
    }

    const pendente = sessionStorage.getItem('netstore_toast');
    if (pendente) {
      sessionStorage.removeItem('netstore_toast');
      window.mostrarToast(pendente);
    }
  })();
</script>