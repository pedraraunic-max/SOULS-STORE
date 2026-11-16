<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/categorias.php';

$tituloPagina = 'Cadastrar jogo';
$dados = [];
$categoriasDisponiveis = (new Categoria())->listar();

require __DIR__ . '/form_game.php';
