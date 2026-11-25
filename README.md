# NetStore

Loja digital de jogos desenvolvida com **PHP**, **MySQL (PDO)**, **Tailwind CSS** e **JavaScript**, rodando em ambiente local com **XAMPP** e rotas amigáveis via `.htaccess`.

## Funcionalidades

### Visitante
- Visualização do catálogo de jogos na página inicial.
- Pesquisa em tempo real por nome ou categoria (ignorando acentos).
- Painel de filtros com seleção múltipla de categorias, faixa de preço (mínimo e máximo) e ordenação (mais recentes, menor/maior preço e nome A–Z).
- Modal de detalhes do jogo com descrição, categorias e galeria interativa de imagens.
- Cadastro de nova conta e login com senhas criptografadas (`password_hash` / `password_verify`).

### Usuário autenticado
- Menu de usuário no cabeçalho com acesso a **Editar Perfil**, **Pedidos** e **Sair**.
- Edição de perfil via modal (nome, usuário, e-mail e troca opcional de senha) exigindo confirmação da senha atual.
- Adição e remoção de jogos no **Carrinho**, com contador de itens no menu.
- Finalização de compra em transação única (gera pedido, registra itens do pedido, libera jogos na biblioteca e limpa o carrinho).
- Página **Biblioteca** com os jogos adquiridos, busca interna e modal de detalhes com galeria.
- Página **Pedidos** com histórico de compras, data, itens e valores.

### Administrador (`is_admin = 1`)
- Cadastro de novos jogos via modal com upload de imagem de capa, múltiplas imagens extras e seleção/criação de categorias.
- Edição de jogos existentes (dados, categorias, substituição/remoção de capa, remoção e reordenação drag & drop de imagens extras).
- Exclusão de jogos (com remoção das imagens no servidor e proteção contra exclusão de jogos já comprados).

## Tecnologias utilizadas

- **Back-end:** PHP 8+ com PDO (prepared statements e transações)
- **Banco de dados:** MySQL (`utf8mb4`)
- **Front-end:** HTML5, Tailwind CSS (via CDN) e JavaScript (Fetch API)
- **Servidor:** Apache com `mod_rewrite` (`.htaccess`)

## Estrutura do projeto

```text
netstore/
├── .htaccess                  # Rotas amigáveis (/home, /login, /action/*) e proteção interna
├── .env.example               # Modelo de variáveis de ambiente para conexão com o banco
├── .gitignore                 # Arquivos ignorados no Git (.env, uploads locais, etc.)
├── config/                    # Configurações da aplicação
│   └── database.php           # Conexão PDO com suporte a .env
├── database/                  # Estrutura do banco de dados
│   └── schema.sql             # Script SQL para criação das tabelas
├── helpers/                   # Funções utilitárias em PHP
│   └── upload.php             # Validação, salvamento e remoção de imagens
├── components/                # Componentes reutilizáveis de interface (HTML/JS)
│   ├── menu.php
│   ├── modal_detalhes_jogo.php
│   ├── modal_jogo.php
│   └── modal_perfil.php
├── pages/                     # Páginas da aplicação
│   ├── home.php
│   ├── biblioteca.php
│   ├── cadastro.php
│   ├── carrinho.php
│   ├── login.php
│   └── pedidos.php
├── actions/                   # Endpoints de ação (POST/GET JSON e logout)
│   ├── adicionar_jogo.php
│   ├── carrinho.php
│   ├── editar_jogo.php
│   ├── editar_perfil.php
│   ├── excluir_jogo.php
│   ├── finalizar_compra.php
│   └── logout.php
├── assets/                    # Arquivos estáticos do projeto (logo)
└── uploads/                   # Imagens enviadas pelo administrador (uploads/jogos/<id>/)
```

## Como executar localmente (XAMPP)

1. Copie a pasta `netstore` para dentro do diretório `htdocs` do XAMPP (ex.: `C:\xampp\htdocs\netstore`).
2. Abra o **XAMPP Control Panel** e inicie os serviços **Apache** e **MySQL**.
3. Acesse o phpMyAdmin (`http://localhost/phpmyadmin`) e importe o arquivo `database/schema.sql`.
4. (Opcional) Caso seu MySQL use porta, usuário ou senha diferentes do padrão do XAMPP (`root` sem senha na porta `3306`), copie `.env.example` para `.env` e ajuste as credenciais.
5. Acesse no navegador:
   ```text
   http://localhost/netstore/home
   ```

## Conta de administrador

Por padrão, toda conta criada pela tela de cadastro recebe `is_admin = 0`. Para promover uma conta a administrador e testar o cadastro/edição/exclusão de jogos, execute no phpMyAdmin:

```sql
UPDATE usuarios SET is_admin = 1 WHERE usuario = 'seu_usuario';
```
