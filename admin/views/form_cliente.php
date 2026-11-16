<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/clientes.php';

$id = isset($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : null;
if (isset($_GET['id']) && !$id) {
    http_response_code(400);
    exit('ID inválido.');
}

$editando = $id !== null;
$dados = ['id' => '', 'nome' => '', 'email' => '', 'telefone' => ''];
if ($editando) {
    $dados = (new Cliente())->buscarPorId($id);
    if (!$dados) {
        http_response_code(404);
        exit('Cliente não encontrado.');
    }
}
$tituloPagina = $editando ? 'Editar cliente' : 'Cadastrar cliente';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> — Souls Store ADM</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="container">
    <?php require __DIR__ . '/menu.php'; ?>
    <section class="admin-page-head admin-page-head--form">
        <div><span class="sobretitulo">CENTRAL DE CONTROLE / CLIENTES</span><h1><?= e($tituloPagina) ?></h1><p>Informe os dados básicos da conta de cliente.</p></div>
    </section>
    <form class="formulario admin-formulario" method="post" action="../controllers/cliente.php">
        <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= e($dados['id']) ?>">
        <label>Nome<input type="text" name="nome" maxlength="100" autocomplete="name" required value="<?= e($dados['nome']) ?>"></label>
        <label>E-mail<input type="email" name="email" maxlength="150" autocomplete="email" required value="<?= e($dados['email']) ?>"></label>
        <label>Telefone<input type="tel" name="telefone" maxlength="30" autocomplete="tel" required placeholder="(00) 00000-0000" value="<?= e($dados['telefone']) ?>"></label>
        <label><?= $editando ? 'Nova senha (opcional)' : 'Senha' ?><input type="password" name="senha" minlength="8" autocomplete="new-password" <?= $editando ? '' : 'required' ?>><small class="ajuda-campo"><?= $editando ? 'Deixe em branco para manter a senha atual.' : 'Use pelo menos 8 caracteres.' ?></small></label>
        <div class="acoes"><button type="submit">Salvar cliente</button><a href="ver_clientes.php">Cancelar</a></div>
    </form>
</main>
</body>
</html>
