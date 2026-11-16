-- Estrutura completa para uma instalação nova da Souls Store.
-- Cria o banco padrão e todas as tabelas. Execute este arquivo uma vez.

CREATE DATABASE IF NOT EXISTS loja_games
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loja_games;

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nome, descricao)
VALUES ('Soulslike', 'Mundos desafiadores, histórias sombrias e combates precisos.')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

CREATE TABLE IF NOT EXISTS games (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estoque INT UNSIGNED NOT NULL DEFAULT 0,
    categoria_id INT UNSIGNED NOT NULL,
    imagem VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY idx_games_titulo (titulo),
    KEY idx_games_categoria (categoria_id),
    CONSTRAINT fk_games_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(30) NOT NULL DEFAULT '',
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conta pública de cliente para demonstração local (não é administradora).
INSERT IGNORE INTO usuarios (nome, email, telefone, senha, tipo) VALUES
    ('Visitante de demonstração', 'demo@souls.store', '', '$2y$10$hkzuQfiXdHb61TjKeIpWzeFZK/v.BYyTQQpamq.9k8ExXdpJo.Dby', 'user');

CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    game_id INT UNSIGNED NOT NULL,
    preco_total DECIMAL(10, 2) NOT NULL,
    status ENUM('concluido', 'cancelado') NOT NULL DEFAULT 'concluido',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_pedidos_usuario_game (usuario_id, game_id),
    KEY idx_pedidos_game (game_id),
    KEY idx_pedidos_status (status),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE RESTRICT,
    CONSTRAINT fk_pedidos_game FOREIGN KEY (game_id)
        REFERENCES games (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tentativas_login (
    email_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    tentativas TINYINT UNSIGNED NOT NULL DEFAULT 1,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (email_hash, ip_hash),
    KEY idx_tentativas_login_data (atualizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO games (titulo, descricao, preco, estoque, categoria_id, imagem) VALUES
('DARK SOULS: REMASTERED', 'Explore Lordran, atravesse suas ruínas e enfrente inimigos implacáveis em uma jornada de descoberta e superação.', 329.90, 0, 1, 'dark-souls-remastered.jpg'),
('DARK SOULS II: SCHOLAR OF THE FIRST SIN', 'Viaje por Drangleic, uma terra consumida pela maldição, e descubra as histórias esquecidas entre seus reinos.', 349.90, 0, 1, 'dark-souls-ii.jpg'),
('DARK SOULS III', 'Atravesse um mundo em cinzas, encare os Lordes das Cinzas e decida o destino da chama.', 369.90, 0, 1, 'dark-souls-iii.jpg'),
('ELDEN RING', 'Desbrave as Terras Intermédias, enfrente criaturas lendárias e construa seu próprio caminho até o Anel Prístino.', 399.90, 0, 1, 'elden-ring.jpg'),
('SEKIRO: SHADOWS DIE TWICE', 'Domine a lâmina e a furtividade em uma jornada de vingança para resgatar seu senhor e quebrar uma antiga maldição.', 379.90, 0, 1, 'sekiro.png'),
('BLOODBORNE', 'Cace horrores pelas ruas de Yharnam e desvende os segredos de uma cidade tomada pela noite.', 359.90, 0, 1, 'bloodborne.png');
