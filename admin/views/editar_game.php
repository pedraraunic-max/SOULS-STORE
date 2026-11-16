<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/categorias.php';

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id) {
    http_response_code(400);
    exit('ID inválido.');
}

$model = new Game();

$dados = $model->buscarGamePorId($id);

if (!$dados) {
    http_response_code(404);
    exit('Jogo não encontrado.');
}

$tituloPagina = 'Editar jogo';
$categoriasDisponiveis = (new Categoria())->listar();

require __DIR__ . '/form_game.php';
