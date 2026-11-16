<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();

if (!isset($tituloPagina) || !isset($dados) || !is_array($dados)) {
    http_response_code(404);
    exit('Página não encontrada.');
}

// Este arquivo é carregado pelas páginas de cadastro e edição.
// As variáveis $dados e $tituloPagina vêm dessas páginas.

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($tituloPagina) ?> — Souls Store</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">

    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="container">
    <?php require __DIR__ . '/menu.php'; ?>

    <section class="admin-page-head admin-page-head--form">
        <div>
            <span class="sobretitulo">CENTRAL DE CONTROLE <span class="separador-titulo">/</span> CATÁLOGO</span>
            <h1><?= e($tituloPagina) ?></h1>
            <p>Preencha as informações que aparecem na vitrine da loja.</p>
        </div>
    </section>

    <form
        class="formulario admin-formulario"
        method="post"
        action="../controllers/salvar_game.php"
    >
        <input
            type="hidden"
            name="csrf"
            value="<?= e(tokenCsrf()) ?>"
        >

        <input
            type="hidden"
            name="id"
            value="<?= e($dados['id'] ?? '') ?>"
        >

        <label>
            Título
            <input
                type="text"
                name="titulo"
                maxlength="100"
                required
                value="<?= e($dados['titulo'] ?? '') ?>"
            >
        </label>

        <label>
            Descrição
            <textarea name="descricao"><?= e(
                $dados['descricao'] ?? ''
            ) ?></textarea>
        </label>

        <label>
            Preço
            <input
                type="number"
                name="preco"
                min="0"
                max="99999999.99"
                step="0.01"
                required
                value="<?= e($dados['preco'] ?? '') ?>"
            >
        </label>

        <label>
            Estoque
            <input
                type="number"
                name="estoque"
                min="0"
                max="2147483647"
                step="1"
                required
                value="<?= e($dados['estoque'] ?? 0) ?>"
            >
        </label>

        <label>
            Categoria
            <select name="categoria_id" required>
                <?php foreach ($categoriasDisponiveis as $categoriaDisponivel): ?>
                    <option value="<?= (int) $categoriaDisponivel['id'] ?>" <?= (int) ($dados['categoria_id'] ?? 0) === (int) $categoriaDisponivel['id'] ? 'selected' : '' ?>>
                        <?= e($categoriaDisponivel['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!$categoriasDisponiveis): ?>
                <small class="ajuda-campo">Cadastre uma categoria antes de criar um jogo.</small>
            <?php endif; ?>
        </label>

        <label>
            Nome do arquivo da imagem
            <input
                type="text"
                name="imagem"
                maxlength="255"
                placeholder="Exemplo: ds2.jpg"
                value="<?= e($dados['imagem'] ?? '') ?>"
            >
            <small class="ajuda-campo">Para exibir a capa, coloque o arquivo em admin/public/images/.</small>
        </label>

        <div class="acoes">
            <button type="submit">Salvar</button>
            <a href="ver_games.php">Cancelar</a>
        </div>
    </form>
</main>
</body>
</html>
