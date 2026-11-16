<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
exigirPostValido();
require_once __DIR__ . '/../models/games.php';

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id) {
    http_response_code(400);
    exit('ID inválido.');
}

$game = new Game([
    'id' => $id,
]);

try {
    if (!$game->buscarGamePorId($id)) {
        http_response_code(404);
        exit('Jogo não encontrado.');
    }

    $game->deletarGame();
} catch (PDOException $erro) {
    if ((int) ($erro->errorInfo[1] ?? 0) === 1451) {
        http_response_code(409);
        exit('Este jogo possui pedidos registrados. Exclua ou cancele os pedidos antes de removê-lo.');
    }
    error_log($erro->getMessage());

    http_response_code(500);
    exit('Não foi possível excluir o jogo. Consulte o log do PHP.');
}

redirecionar('../views/ver_games.php');
