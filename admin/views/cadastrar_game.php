<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();

$tituloPagina = 'Cadastrar jogo';
$dados = [];

require __DIR__ . '/form_game.php';
