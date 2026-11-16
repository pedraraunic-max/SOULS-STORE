<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
exigirPostValido();
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../models/games.php';

function erroPedido($mensagem, $status = 422)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>Não foi possível concluir</h1><p>' . e($mensagem) . '</p>';
    echo '<p><a href="../views/ver_pedidos.php">Voltar para pedidos</a></p>';
    exit;
}

foreach (['acao', 'id', 'usuario_id', 'game_id', 'preco_total', 'status'] as $campo) {
    if (isset($_POST[$campo]) && !is_string($_POST[$campo])) {
        erroPedido('Formato de campo inválido.', 400);
    }
}
$acao = $_POST['acao'] ?? '';
$idTexto = trim($_POST['id'] ?? '');
$id = $idTexto === '' ? null : filter_var($idTexto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    erroPedido('ID inválido.', 400);
}
$pedidos = new Pedido();

try {
    if ($acao === 'excluir') {
        if ($id === null || !$pedidos->buscarPorId($id)) {
            erroPedido('Pedido não encontrado.', 404);
        }
        $pedidos->deletar($id);
        redirecionar('../views/ver_pedidos.php');
    }
    if ($acao !== 'salvar') {
        erroPedido('Ação inválida.', 400);
    }

    $usuarioId = filter_var($_POST['usuario_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $gameId = filter_var($_POST['game_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $preco = str_replace(',', '.', trim($_POST['preco_total'] ?? ''));
    $status = $_POST['status'] ?? '';
    if ($usuarioId === false || !(new Cliente())->buscarPorId($usuarioId)) {
        erroPedido('Selecione um cliente cadastrado.');
    }
    if ($gameId === false || !(new Game())->buscarGamePorId($gameId)) {
        erroPedido('Selecione um jogo cadastrado.');
    }
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/D', $preco)) {
        erroPedido('Informe um total válido, com até duas casas decimais.');
    }
    if (!in_array($status, ['concluido', 'cancelado'], true)) {
        erroPedido('Status de pedido inválido.');
    }
    if ($id !== null && !$pedidos->buscarPorId($id)) {
        erroPedido('Pedido não encontrado.', 404);
    }
    if ($id === null) {
        $pedidos->cadastrar($usuarioId, $gameId, $preco, $status);
    } else {
        $pedidos->editar($id, $usuarioId, $gameId, $preco, $status);
    }
    redirecionar('../views/ver_pedidos.php');
} catch (PDOException $erro) {
    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        erroPedido('Este cliente já possui um pedido desse jogo.', 409);
    }
    error_log($erro->getMessage());
    erroPedido('Erro ao salvar o pedido no banco de dados.', 500);
}
