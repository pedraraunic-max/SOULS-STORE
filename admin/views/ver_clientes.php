<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/clientes.php';

$clientes = (new Cliente())->listar();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes — Souls Store ADM</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="admin-container">
    <?php require __DIR__ . '/menu.php'; ?>

    <section class="admin-page-head">
        <div>
            <span class="sobretitulo">CENTRAL DE CONTROLE / CLIENTES</span>
            <h1>Clientes</h1>
            <p>Gerencie os perfis de cliente e seus dados de contato.</p>
        </div>
        <a class="botao-principal" href="form_cliente.php"><span aria-hidden="true">＋</span> Novo cliente</a>
    </section>

    <section class="admin-metricas" aria-label="Resumo de clientes">
        <article class="metrica-card"><span class="metrica-card__icone">♙</span><small>CLIENTES CADASTRADOS</small><strong><?= count($clientes) ?></strong><span class="metrica-card__nota">contas de acesso à loja</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--azul">◈</span><small>CONTATO</small><strong>Telefone</strong><span class="metrica-card__nota">registrado no cadastro</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--verde">✓</span><small>ACESSO</small><strong>Cliente</strong><span class="metrica-card__nota">sem permissões administrativas</span></article>
    </section>

    <section class="admin-lista">
        <div class="admin-lista__topo"><div><h2>Cadastro de clientes</h2><p>Contas comuns podem acessar a loja e sua biblioteca.</p></div></div>
        <div class="table-container">
            <table class="tabela-usuarios">
                <thead><tr><th>Cliente</th><th>E-mail</th><th>Telefone</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><div class="celula-jogo"><span class="celula-jogo__icone celula-jogo__icone--usuario"><?= e(inicial($cliente['nome'])) ?></span><span><strong><?= e($cliente['nome']) ?></strong><small>Cliente #<?= (int) $cliente['id'] ?></small></span></div></td>
                        <td><?= e($cliente['email']) ?></td>
                        <td><?= e($cliente['telefone'] ?: '—') ?></td>
                        <td><div class="acoes">
                            <a class="acao-icone" href="form_cliente.php?id=<?= (int) $cliente['id'] ?>" aria-label="Editar <?= e($cliente['nome']) ?>" title="Editar">✎</a>
                            <form method="post" action="../controllers/cliente.php" onsubmit="return confirm('Deseja excluir este cliente?');">
                                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">
                                <button class="acao-icone acao-icone--perigo" type="submit" aria-label="Excluir <?= e($cliente['nome']) ?>" title="Excluir">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$clientes): ?><tr><td colspan="4" class="tabela-vazia">Nenhum cliente cadastrado.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
