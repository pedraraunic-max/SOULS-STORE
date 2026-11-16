-- Registra compras simuladas na biblioteca de cada usuário.
-- Não integra pagamento real nem etapa de checkout.

CREATE TABLE IF NOT EXISTS compras (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    game_id INT UNSIGNED NOT NULL,
    preco_pago DECIMAL(10, 2) NOT NULL,
    comprado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_compras_usuario_game (usuario_id, game_id),
    KEY idx_compras_game (game_id),
    CONSTRAINT fk_compras_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_compras_game FOREIGN KEY (game_id)
        REFERENCES games (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
