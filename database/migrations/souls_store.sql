-- Atualiza o catálogo existente para a Souls Store e adiciona proteção
-- contra tentativas repetidas de login.
-- Execute no banco loja_games. Os usuários existentes são preservados.

CREATE TABLE IF NOT EXISTS tentativas_login (
    email_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    tentativas TINYINT UNSIGNED NOT NULL DEFAULT 1,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (email_hash, ip_hash),
    KEY idx_tentativas_login_data (atualizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

DELETE FROM games;

INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem) VALUES
('DARK SOULS: REMASTERED', 'Explore Lordran, atravesse suas ruínas e enfrente inimigos implacáveis em uma jornada de descoberta e superação.', 329.90, 0, 'Soulslike', 'dark-souls-remastered.jpg'),
('DARK SOULS II: SCHOLAR OF THE FIRST SIN', 'Viaje por Drangleic, uma terra consumida pela maldição, e descubra as histórias esquecidas entre seus reinos.', 349.90, 0, 'Soulslike', 'dark-souls-ii.jpg'),
('DARK SOULS III', 'Atravesse um mundo em cinzas, encare os Lordes das Cinzas e decida o destino da chama.', 369.90, 0, 'Soulslike', 'dark-souls-iii.jpg'),
('ELDEN RING', 'Desbrave as Terras Intermédias, enfrente criaturas lendárias e construa seu próprio caminho até o Anel Prístino.', 399.90, 0, 'Soulslike', 'elden-ring.jpg'),
('SEKIRO: SHADOWS DIE TWICE', 'Domine a lâmina e a furtividade em uma jornada de vingança para resgatar seu senhor e quebrar uma antiga maldição.', 379.90, 0, 'Soulslike', 'sekiro.png'),
('BLOODBORNE', 'Cace horrores pelas ruas de Yharnam e desvende os segredos de uma cidade tomada pela noite.', 359.90, 0, 'Soulslike', 'bloodborne.png');

COMMIT;
