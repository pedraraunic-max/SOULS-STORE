<?php

require_once __DIR__ . '/../config/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/pedidos.php';

exigirPostValido();

$usuario = usuarioAutenticado();
if ($usuario === null) {
    $_SESSION['aviso_login'] = 'Entre na sua conta para adicionar jogos à biblioteca.';
    redirecionar(urlApp('login.php'));
}

$gameIdRecebido = $_POST['game_id'] ?? null;
$gameId = is_string($gameIdRecebido)
    ? filter_var(
        $gameIdRecebido,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($gameId === false || $gameId === null) {
    $_SESSION['aviso_loja'] = [
        'tipo' => 'erro',
        'texto' => 'Não foi possível identificar esse jogo.',
    ];
    redirecionar(urlApp('index.php#acervo'));
}

$stmtGame = $pdo->prepare('SELECT id, preco FROM games WHERE id = :id');
$stmtGame->execute(['id' => $gameId]);
$game = $stmtGame->fetch();

if (!$game) {
    $_SESSION['aviso_loja'] = [
        'tipo' => 'erro',
        'texto' => 'Esse jogo não está mais disponível no catálogo.',
    ];
    redirecionar(urlApp('index.php#acervo'));
}

$pedidos = new Pedido();
$pedidoExistente = $pedidos->buscarPorClienteJogo(
    (int) $usuario['id'],
    (int) $game['id']
);

if ($pedidoExistente && $pedidoExistente['status'] === 'concluido') {
    $_SESSION['aviso_loja'] = [
        'tipo' => 'info',
        'texto' => 'Esse jogo já está na sua biblioteca.',
        'link' => 'biblioteca.php',
    ];
    redirecionar(urlApp('index.php#acervo'));
}

try {
    if ($pedidoExistente) {
        $pedidos->reativar((int) $pedidoExistente['id'], $game['preco']);
    } else {
        $pedidos->cadastrar(
            (int) $usuario['id'],
            (int) $game['id'],
            $game['preco']
        );
    }

    $_SESSION['aviso_loja'] = [
        'tipo' => 'sucesso',
        'texto' => 'Jogo adicionado à sua biblioteca. Compra simulada para o projeto acadêmico.',
        'link' => 'biblioteca.php',
    ];
} catch (PDOException $erro) {
    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        $_SESSION['aviso_loja'] = [
            'tipo' => 'info',
            'texto' => 'Esse jogo já está na sua biblioteca.',
            'link' => 'biblioteca.php',
        ];
    } else {
        error_log($erro->getMessage());
        $_SESSION['aviso_loja'] = [
            'tipo' => 'erro',
            'texto' => 'Não foi possível adicionar o jogo agora. Tente novamente.',
        ];
    }
}

redirecionar(urlApp('index.php#acervo'));
