<?php

require_once __DIR__ . '/admin/config/conexao.php';

$stmt = $pdo->query(
    'SELECT id, titulo, preco FROM games ORDER BY id'
);

$games = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de conexão</title>
</head>
<body>
    <h1>Conexão realizada com sucesso!</h1>

    <p>Jogos encontrados: <?= count($games) ?></p>

    <ul>
        <?php foreach ($games as $game): ?>
            <li>
                <?= htmlspecialchars(
                    $game['titulo'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                — R$
                <?= number_format(
                    (float) $game['preco'],
                    2,
                    ',',
                    '.'
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>