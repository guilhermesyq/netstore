CREATE DATABASE IF NOT EXISTS netstore CHARACTER SET utf8mb4;
USE netstore;

CREATE TABLE usuarios (
  id         INT NOT NULL AUTO_INCREMENT,
  nome       VARCHAR(100) NOT NULL,
  usuario    VARCHAR(50)  NOT NULL,
  email      VARCHAR(150) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  is_admin   TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE (usuario),
  UNIQUE (email)
);

CREATE TABLE categorias (
  id   INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (nome)
);

CREATE TABLE jogos (
  id             INT NOT NULL AUTO_INCREMENT,
  nome           VARCHAR(150)  NOT NULL,
  descricao      TEXT,
  preco          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  imagem_url     VARCHAR(255),
  adicionado_por INT,
  criado_em      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (adicionado_por) REFERENCES usuarios(id)
);

CREATE TABLE jogos_categorias (
  jogo_id      INT NOT NULL,
  categoria_id INT NOT NULL,
  PRIMARY KEY (jogo_id, categoria_id),
  FOREIGN KEY (jogo_id) REFERENCES jogos(id),
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE imagens_jogo (
  id      INT NOT NULL AUTO_INCREMENT,
  jogo_id INT NOT NULL,
  url     VARCHAR(255) NOT NULL,
  ordem   INT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  FOREIGN KEY (jogo_id) REFERENCES jogos(id)
);

CREATE TABLE carrinho (
  usuario_id    INT NOT NULL,
  jogo_id       INT NOT NULL,
  adicionado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, jogo_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (jogo_id) REFERENCES jogos(id)
);

CREATE TABLE biblioteca (
  usuario_id   INT NOT NULL,
  jogo_id      INT NOT NULL,
  preco_pago   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  adquirido_em TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, jogo_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (jogo_id) REFERENCES jogos(id)
);

CREATE TABLE pedidos (
  id          INT NOT NULL AUTO_INCREMENT,
  usuario_id  INT NOT NULL,
  total       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  data_pedido TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE itens_pedido (
  pedido_id      INT NOT NULL,
  jogo_id        INT NOT NULL,
  preco_unitario DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (pedido_id, jogo_id),
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
  FOREIGN KEY (jogo_id) REFERENCES jogos(id)
);
