-- Atualiza uma instalação anterior da Souls Store para a avaliação acadêmica.
-- Faça backup do banco antes de importar este arquivo. Execute uma única vez.

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (nome)
SELECT DISTINCT LEFT(TRIM(categoria), 50)
FROM games
WHERE categoria IS NOT NULL AND TRIM(categoria) <> '';

INSERT IGNORE INTO categorias (nome) VALUES ('Sem categoria');

ALTER TABLE games ADD COLUMN categoria_id INT UNSIGNED NULL AFTER estoque;

UPDATE games g
LEFT JOIN categorias c ON c.nome = LEFT(TRIM(g.categoria), 50)
SET g.categoria_id = COALESCE(
    c.id,
    (SELECT id FROM categorias WHERE nome = 'Sem categoria' LIMIT 1)
);

ALTER TABLE games MODIFY categoria_id INT UNSIGNED NOT NULL;
ALTER TABLE games DROP INDEX idx_games_categoria;
ALTER TABLE games DROP COLUMN categoria;
ALTER TABLE games ADD KEY idx_games_categoria (categoria_id);
ALTER TABLE games ADD CONSTRAINT fk_games_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias (id)
    ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE usuarios ADD COLUMN telefone VARCHAR(30) NOT NULL DEFAULT '' AFTER email;

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

-- Migra o histórico antigo para pedidos, mantendo a tabela compras como cópia de segurança.
INSERT IGNORE INTO pedidos (id, usuario_id, game_id, preco_total, status, criado_em)
SELECT id, usuario_id, game_id, preco_pago, 'concluido', comprado_em
FROM compras;
