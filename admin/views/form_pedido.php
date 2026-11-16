<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../models/games.php';

$id = isset($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : null;
if (isset($_GET['id']) && !$id) {
    http_response_code(400);
    exit('ID inválido.');
}
$editando = $id !== null;
$dados = ['id' => '', 'usuario_id' => '', 'game_id' => '', 'preco_total' => '', 'status' => 'concluido'];
if ($editando) {
    $dados = (new Pedido())->buscarPorId($id);
    if (!$dados) {
        http_response_code(404);
        exit('Pedido não encontrado.');
    }
}
$clientes = (new Cliente())->listar();
$games = (new Game())->listarGames();
$tituloPagina = $editando ? 'Editar pedido' : 'Cadastrar pedido';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> — Souls Store ADM</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="container">
    <?php require __DIR__ . '/menu.php'; ?>
    <section class="admin-page-head admin-page-head--form"><div><span class="sobretitulo">CENTRAL DE CONTROLE / VENDAS</span><h1><?= e($tituloPagina) ?></h1><p>Registre ou ajuste o pedido simulado de um cliente.</p></div></section>
    <?php if (!$clientes || !$games): ?><div class="store-notice store-notice--info" role="status">Cadastre pelo menos um cliente e um jogo antes de criar pedidos.</div><?php endif; ?>
    <form class="formulario admin-formulario" method="post" action="../controllers/pedido.php">
        <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= e($dados['id']) ?>">
        <label>Cliente<select name="usuario_id" required>
            <option value="">Selecione um cliente</option>
            <?php foreach ($clientes as $cliente): ?><option value="<?= (int) $cliente['id'] ?>" <?= (int) $dados['usuario_id'] === (int) $cliente['id'] ? 'selected' : '' ?>><?= e($cliente['nome'] . ' · ' . $cliente['email']) ?></option><?php endforeach; ?>
        </select></label>
        <label>Jogo<select name="game_id" required data-pedido-game>
            <option value="">Selecione um jogo</option>
            <?php foreach ($games as $game): ?><option value="<?= (int) $game['id'] ?>" data-preco="<?= e($game['preco']) ?>" <?= (int) $dados['game_id'] === (int) $game['id'] ? 'selected' : '' ?>><?= e($game['titulo']) ?></option><?php endforeach; ?>
        </select></label>
        <label>Total (R$)<input type="number" name="preco_total" min="0" max="99999999.99" step="0.01" required value="<?= e($dados['preco_total']) ?>" data-pedido-preco></label>
        <label>Status<select name="status" required><option value="concluido" <?= $dados['status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option><option value="cancelado" <?= $dados['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option></select></label>
        <div class="acoes"><button type="submit" <?= (!$clientes || !$games) ? 'disabled' : '' ?>>Salvar pedido</button><a href="ver_pedidos.php">Cancelar</a></div>
    </form>
</main>
<script>
(() => {
    const jogo = document.querySelector('[data-pedido-game]');
    const preco = document.querySelector('[data-pedido-preco]');
    if (!jogo || !preco) return;
    const sincronizarPreco = () => {
        const opcao = jogo.selectedOptions[0];
        if (opcao && opcao.dataset.preco) preco.value = opcao.dataset.preco;
    };
    jogo.addEventListener('change', sincronizarPreco);
    <?php if (!$editando): ?>sincronizarPreco();<?php endif; ?>
})();
</script>
</body>
</html>
