<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/categorias.php';
$categorias = (new Categoria())->listar();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias — Souls Store ADM</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="admin-container">
    <?php require __DIR__ . '/menu.php'; ?>
    <section class="admin-page-head">
        <div><span class="sobretitulo">CENTRAL DE CONTROLE / CATÁLOGO</span><h1>Categorias</h1><p>Organize os jogos por gênero e tema.</p></div>
        <a class="botao-principal" href="form_categoria.php"><span aria-hidden="true">＋</span> Nova categoria</a>
    </section>
    <section class="admin-metricas" aria-label="Resumo de categorias">
        <article class="metrica-card"><span class="metrica-card__icone">◈</span><small>CATEGORIAS</small><strong><?= count($categorias) ?></strong><span class="metrica-card__nota">gêneros cadastrados</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--azul">▦</span><small>JOGOS ASSOCIADOS</small><strong><?= array_sum(array_map(static function ($c) { return (int) $c['total_games']; }, $categorias)) ?></strong><span class="metrica-card__nota">em todas as categorias</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--verde">✓</span><small>RELACIONAMENTO</small><strong>Ativo</strong><span class="metrica-card__nota">categoria ligada ao jogo</span></article>
    </section>
    <section class="admin-lista">
        <div class="admin-lista__topo"><div><h2>Lista de categorias</h2><p>Uma categoria em uso não pode ser removida até os jogos serem reclassificados.</p></div></div>
        <div class="table-container">
            <table>
                <thead><tr><th>Categoria</th><th>Descrição</th><th>Jogos</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><strong><?= e($categoria['nome']) ?></strong></td>
                        <td><?= e($categoria['descricao'] ?: '—') ?></td>
                        <td><span class="categoria-tag"><?= (int) $categoria['total_games'] ?></span></td>
                        <td><div class="acoes">
                            <a class="acao-icone" href="form_categoria.php?id=<?= (int) $categoria['id'] ?>" aria-label="Editar <?= e($categoria['nome']) ?>" title="Editar">✎</a>
                            <form method="post" action="../controllers/categoria.php" onsubmit="return confirm('Deseja excluir esta categoria?');">
                                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
                                <button class="acao-icone acao-icone--perigo" type="submit" aria-label="Excluir <?= e($categoria['nome']) ?>" title="Excluir">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$categorias): ?><tr><td colspan="4" class="tabela-vazia">Nenhuma categoria cadastrada.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
