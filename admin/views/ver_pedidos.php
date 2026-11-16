<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/pedidos.php';
$pedidos = (new Pedido())->listar();
$concluidos = count(array_filter($pedidos, static function ($p) { return $p['status'] === 'concluido'; }));
$cancelados = count($pedidos) - $concluidos;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — Souls Store ADM</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="admin-container">
    <?php require __DIR__ . '/menu.php'; ?>
    <section class="admin-page-head">
        <div><span class="sobretitulo">CENTRAL DE CONTROLE / VENDAS</span><h1>Pedidos</h1><p>Acompanhe as compras simuladas registradas pela loja.</p></div>
        <a class="botao-principal" href="form_pedido.php"><span aria-hidden="true">＋</span> Novo pedido</a>
    </section>
    <section class="admin-metricas" aria-label="Resumo de pedidos">
        <article class="metrica-card"><span class="metrica-card__icone">▤</span><small>TOTAL DE PEDIDOS</small><strong><?= count($pedidos) ?></strong><span class="metrica-card__nota">registros de venda</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--verde">✓</span><small>CONCLUÍDOS</small><strong><?= $concluidos ?></strong><span class="metrica-card__nota">na biblioteca dos clientes</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--roxo">×</span><small>CANCELADOS</small><strong><?= $cancelados ?></strong><span class="metrica-card__nota">fora das bibliotecas</span></article>
    </section>
    <section class="admin-lista">
        <div class="admin-lista__topo"><div><h2>Histórico de pedidos</h2><p>Os pedidos desta loja são acadêmicos e não movimentam pagamentos reais.</p></div></div>
        <div class="table-container">
            <table>
                <thead><tr><th>Pedido</th><th>Cliente</th><th>Jogo</th><th>Total</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><strong><?= e($pedido['cliente']) ?></strong><small class="pedido-email"><?= e($pedido['email']) ?></small></td>
                        <td><?= e($pedido['jogo']) ?></td>
                        <td class="preco-tabela">R$ <?= number_format((float) $pedido['preco_total'], 2, ',', '.') ?></td>
                        <td><span class="pedido-status pedido-status--<?= e($pedido['status']) ?>"><?= $pedido['status'] === 'concluido' ? 'Concluído' : 'Cancelado' ?></span></td>
                        <td><time datetime="<?= e(date(DATE_ATOM, strtotime($pedido['criado_em']))) ?>"><?= e(date('d/m/Y H:i', strtotime($pedido['criado_em']))) ?></time></td>
                        <td><div class="acoes">
                            <a class="acao-icone" href="form_pedido.php?id=<?= (int) $pedido['id'] ?>" aria-label="Editar pedido <?= (int) $pedido['id'] ?>" title="Editar">✎</a>
                            <form method="post" action="../controllers/pedido.php" onsubmit="return confirm('Deseja excluir este pedido?');">
                                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                <button class="acao-icone acao-icone--perigo" type="submit" aria-label="Excluir pedido <?= (int) $pedido['id'] ?>" title="Excluir">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pedidos): ?><tr><td colspan="7" class="tabela-vazia">Nenhum pedido registrado ainda.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
