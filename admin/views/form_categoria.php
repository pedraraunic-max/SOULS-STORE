<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/categorias.php';

$id = isset($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : null;
if (isset($_GET['id']) && !$id) {
    http_response_code(400);
    exit('ID inválido.');
}
$editando = $id !== null;
$dados = ['id' => '', 'nome' => '', 'descricao' => ''];
if ($editando) {
    $dados = (new Categoria())->buscarPorId($id);
    if (!$dados) {
        http_response_code(404);
        exit('Categoria não encontrada.');
    }
}
$tituloPagina = $editando ? 'Editar categoria' : 'Cadastrar categoria';

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
    <section class="admin-page-head admin-page-head--form"><div><span class="sobretitulo">CENTRAL DE CONTROLE / CATÁLOGO</span><h1><?= e($tituloPagina) ?></h1><p>Cadastre o gênero usado para classificar os jogos.</p></div></section>
    <form class="formulario admin-formulario" method="post" action="../controllers/categoria.php">
        <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= e($dados['id']) ?>">
        <label>Nome<input type="text" name="nome" maxlength="50" required value="<?= e($dados['nome']) ?>"></label>
        <label>Descrição<textarea name="descricao" maxlength="255" rows="4"><?= e($dados['descricao']) ?></textarea></label>
        <div class="acoes"><button type="submit">Salvar categoria</button><a href="ver_categorias.php">Cancelar</a></div>
    </form>
</main>
</body>
</html>
