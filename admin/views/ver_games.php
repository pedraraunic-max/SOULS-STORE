<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/games.php';

$model = new Game();
$buscaRecebida = $_GET['buscar'] ?? '';
$nome = is_string($buscaRecebida) ? trim($buscaRecebida) : '';
$games = $nome !== '' ? $model->buscarPorNome($nome) : $model->listarGames();
$totalEstoque = array_sum(array_map(
    static function ($game) {
        return (int) $game['estoque'];
    },
    $games
));
$categorias = array_unique(array_filter(array_map(
    static function ($game) {
        return trim((string) $game['categoria']);
    },
    $games
)));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080b10">
    <title>Painel de jogos — Souls Store</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="admin-container">
    <?php require __DIR__ . '/menu.php'; ?>

    <section class="admin-page-head">
        <div>
            <span class="sobretitulo">CENTRAL DE CONTROLE <span class="separador-titulo">/</span> CATÁLOGO</span>
            <h1>Jogos</h1>
            <p>Organize seu catálogo e acompanhe o estoque da loja.</p>
        </div>
        <a class="botao-principal" href="cadastrar_game.php"><span aria-hidden="true">＋</span> Novo jogo</a>
    </section>

    <section class="admin-metricas" aria-label="Resumo dos jogos">
        <article class="metrica-card"><span class="metrica-card__icone">▦</span><small>JOGOS LISTADOS</small><strong><?= count($games) ?></strong><span class="metrica-card__nota">itens no catálogo atual</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--verde">↗</span><small>UNIDADES EM ESTOQUE</small><strong><?= $totalEstoque ?></strong><span class="metrica-card__nota">unidades disponíveis</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--roxo">◈</span><small>CATEGORIAS</small><strong><?= count($categorias) ?></strong><span class="metrica-card__nota">categorias representadas</span></article>
    </section>

    <section class="admin-lista">
        <div class="admin-lista__topo">
            <div><h2>Catálogo completo</h2><p><?= count($games) ?> jogo(s)<?= $nome !== '' ? ' para “' . e($nome) . '”' : '' ?></p></div>
            <form class="busca-admin" method="get" action="ver_games.php">
                <label><span aria-hidden="true">⌕</span><input type="search" name="buscar" placeholder="Buscar jogo..." value="<?= e($nome) ?>" aria-label="Buscar jogo"></label>
                <button type="submit">Buscar</button>
                <?php if ($nome !== ''): ?><a href="ver_games.php">Limpar</a><?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead><tr><th>Jogo</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Imagem</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <td><div class="celula-jogo"><span class="celula-jogo__icone"><?= e(inicial($game['titulo'])) ?></span><span><strong><?= e($game['titulo']) ?></strong><small><?= e($game['descricao'] ?: 'Sem descrição') ?></small></span></div></td>
                        <td><span class="categoria-tag"><?= e($game['categoria'] ?: 'Sem categoria') ?></span></td>
                        <td class="preco-tabela">R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></td>
                        <td><span class="estoque-tag <?= (int) $game['estoque'] > 0 ? 'estoque-tag--ok' : 'estoque-tag--baixo' ?>"><i></i><?= (int) $game['estoque'] ?> un.</span></td>
                        <td class="nome-imagem"><?= e($game['imagem'] ?: '—') ?></td>
                        <td><div class="acoes">
                            <a class="acao-icone" href="editar_game.php?id=<?= (int) $game['id'] ?>" aria-label="Editar <?= e($game['titulo']) ?>" title="Editar">✎</a>
                            <form method="post" action="../controllers/deletar_game.php" onsubmit="return confirm('Deseja excluir este jogo?');">
                                <input type="hidden" name="id" value="<?= (int) $game['id'] ?>">
                                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                                <button class="acao-icone acao-icone--perigo" type="submit" aria-label="Excluir <?= e($game['titulo']) ?>" title="Excluir">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$games): ?>
                    <tr><td colspan="6" class="tabela-vazia">Nenhum jogo encontrado. Cadastre um título ou ajuste a busca.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
