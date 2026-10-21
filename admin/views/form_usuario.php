<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/usuarios.php';

$dados = [
    'id' => '',
    'nome' => '',
    'email' => '',
    'tipo' => 'user',
];

$editando = isset($_GET['id']);

if ($editando) {
    $id = filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$id) {
        http_response_code(400);
        exit('ID inválido.');
    }

    $model = new Usuario();
    $dados = $model->buscarPorId($id);

    if (!$dados) {
        http_response_code(404);
        exit('Usuário não encontrado.');
    }
}

$tituloPagina = $editando
    ? 'Editar usuário'
    : 'Cadastrar usuário';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($tituloPagina) ?> — GameStock ADM</title>

    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="pagina-admin">
<main class="container">
    <?php require __DIR__ . '/menu.php'; ?>

    <section class="admin-page-head admin-page-head--form">
        <div>
            <span class="sobretitulo">CENTRAL DE CONTROLE <span class="separador-titulo">/</span> ACESSOS</span>
            <h1><?= e($tituloPagina) ?></h1>
            <p>Defina os dados da conta e o nível de acesso.</p>
        </div>
    </section>

    <form
        class="formulario admin-formulario"
        method="post"
        action="../controllers/usuario.php"
    >
        <input
            type="hidden"
            name="csrf"
            value="<?= e(tokenCsrf()) ?>"
        >

        <input type="hidden" name="acao" value="salvar">

        <input
            type="hidden"
            name="id"
            value="<?= e($dados['id']) ?>"
        >

        <label>
            Nome
            <input
                type="text"
                name="nome"
                maxlength="100"
                autocomplete="name"
                required
                value="<?= e($dados['nome']) ?>"
            >
        </label>

        <label>
            E-mail
            <input
                type="email"
                name="email"
                maxlength="150"
                autocomplete="email"
                required
                value="<?= e($dados['email']) ?>"
            >
        </label>

        <label>
            <?= $editando ? 'Nova senha (opcional)' : 'Senha' ?>

            <input
                type="password"
                name="senha"
                minlength="8"
                autocomplete="new-password"
                <?= !$editando ? 'required' : '' ?>
            >

            <small class="ajuda-campo">
                <?php if ($editando): ?>
                    Deixe em branco para manter a senha atual.
                <?php else: ?>
                    Use pelo menos 8 caracteres.
                <?php endif; ?>
                Limite de 72 bytes — acentos podem ocupar mais de um.
            </small>
        </label>

        <label>
            Tipo de usuário
            <select name="tipo" required>
                <option
                    value="user"
                    <?= $dados['tipo'] === 'user' ? 'selected' : '' ?>
                >
                    Usuário
                </option>

                <option
                    value="admin"
                    <?= $dados['tipo'] === 'admin' ? 'selected' : '' ?>
                >
                    Administrador
                </option>
            </select>
            <small class="ajuda-campo">Administradores podem acessar e alterar todo o painel da loja.</small>
        </label>

        <div class="acoes">
            <button type="submit">Salvar usuário</button>

            <a href="ver_usuarios.php">Cancelar</a>
        </div>
    </form>
</main>
</body>
</html>
