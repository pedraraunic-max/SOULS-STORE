<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
exigirPostValido();
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/categorias.php';

// Impede que campos esperados como texto sejam enviados como arrays.
$campos = [
    'id',
    'titulo',
    'descricao',
    'preco',
    'estoque',
    'categoria_id',
    'imagem',
];

foreach ($campos as $campo) {
    if (isset($_POST[$campo]) && !is_string($_POST[$campo])) {
        http_response_code(400);
        exit('Formato de campo inválido.');
    }
}

$idTexto = trim($_POST['id'] ?? '');

$id = null;

if ($idTexto !== '') {
    $id = filter_var(
        $idTexto,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        http_response_code(400);
        exit('ID inválido.');
    }
}

$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$preco = str_replace(',', '.', trim($_POST['preco'] ?? ''));
$categoriaId = filter_var(
    $_POST['categoria_id'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$imagem = trim($_POST['imagem'] ?? '');

$estoque = filter_var(
    $_POST['estoque'] ?? '',
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 0,
            'max_range' => 2147483647,
        ],
    ]
);

// Conta caracteres UTF-8 sem depender da extensão mbstring.
$comprimento = static function ($texto) {
    $quantidade = preg_match_all('/./us', $texto);
    return $quantidade === false ? PHP_INT_MAX : $quantidade;
};

$erros = [];

if ($titulo === '' || $comprimento($titulo) > 100) {
    $erros[] = 'Informe um título com até 100 caracteres.';
}

if ($categoriaId === false || !(new Categoria())->buscarPorId($categoriaId)) {
    $erros[] = 'Selecione uma categoria cadastrada.';
}

if ($comprimento($imagem) > 255) {
    $erros[] = 'O nome da imagem deve ter até 255 caracteres.';
}

if (strlen($descricao) > 65535) {
    $erros[] = 'A descrição está muito longa.';
}

// DECIMAL(10,2): até 8 dígitos inteiros e 2 casas decimais.
if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/D', $preco)) {
    $erros[] = 'Informe um preço válido, como 149.90, sem separador de milhar.';
}

if ($estoque === false) {
    $erros[] = 'O estoque deve ser um número inteiro maior ou igual a zero.';
}

if ($erros) {
    http_response_code(422);

    echo '<meta charset="UTF-8">';
    echo '<h1>Confira os campos</h1>';
    echo '<ul>';

    foreach ($erros as $erro) {
        echo '<li>' . e($erro) . '</li>';
    }

    echo '</ul>';
    echo '<p>Use Voltar no navegador para corrigir o formulário.</p>';

    exit;
}

$game = new Game([
    'id' => $id,
    'titulo' => $titulo,
    'descricao' => $descricao,
    'preco' => $preco,
    'estoque' => $estoque,
    'categoria_id' => $categoriaId,
    'imagem' => $imagem,
]);

try {
    if ($id !== null) {
        if (!$game->buscarGamePorId($id)) {
            http_response_code(404);
            exit('Jogo não encontrado.');
        }

        $game->editarGame();
    } else {
        $game->cadastrarGame();
    }
} catch (PDOException $erro) {
    error_log($erro->getMessage());

    http_response_code(500);
    exit('Não foi possível salvar o jogo. Consulte o log do PHP.');
}

redirecionar('../views/ver_games.php');
