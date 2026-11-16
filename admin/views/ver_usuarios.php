<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/../models/usuarios.php';

$model = new Usuario();
$usuarios = $model->listar();
$totalAdmins = count(array_filter($usuarios, static function ($usuario) {
    return $usuario['tipo'] === 'admin';
}));
$totalClientes = count($usuarios) - $totalAdmins;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080b10">
    <title>Painel de usuários — Souls Store</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="../public/css/style.css?v=20261116-crud">
</head>
<body class="pagina-admin">
<main class="admin-container">
    <?php require __DIR__ . '/menu.php'; ?>

    <section class="admin-page-head">
        <div>
            <span class="sobretitulo">CENTRAL DE CONTROLE <span class="separador-titulo">/</span> ACESSOS</span>
            <h1>Usuários</h1>
            <p>Gerencie contas e defina quem pode administrar a loja.</p>
        </div>
        <a class="botao-principal" href="form_usuario.php"><span aria-hidden="true">＋</span> Novo usuário</a>
    </section>

    <section class="admin-metricas" aria-label="Resumo de usuários">
        <article class="metrica-card"><span class="metrica-card__icone">♙</span><small>TOTAL DE CONTAS</small><strong><?= count($usuarios) ?></strong><span class="metrica-card__nota">perfis cadastrados</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--verde">◉</span><small>USUÁRIOS</small><strong><?= $totalClientes ?></strong><span class="metrica-card__nota">acesso à loja</span></article>
        <article class="metrica-card"><span class="metrica-card__icone metrica-card__icone--azul">◆</span><small>ADMINISTRADORES</small><strong><?= $totalAdmins ?></strong><span class="metrica-card__nota">acesso ao painel</span></article>
    </section>

    <section class="admin-lista">
        <div class="admin-lista__topo">
            <div><h2>Contas cadastradas</h2><p>As permissões são controladas pelo perfil de cada conta.</p></div>
            <span class="selo-privacidade"><span aria-hidden="true">◈</span> Dados de acesso protegidos</span>
        </div>

        <div class="table-container">
            <table class="tabela-usuarios">
                <thead><tr><th>Usuário</th><th>E-mail</th><th>Perfil de acesso</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><div class="celula-jogo"><span class="celula-jogo__icone celula-jogo__icone--usuario"><?= e(inicial($usuario['nome'])) ?></span><span><strong><?= e($usuario['nome']) ?></strong><small>Conta #<?= (int) $usuario['id'] ?></small></span></div></td>
                        <td><?= e($usuario['email']) ?></td>
                        <td><span class="perfil-tag <?= $usuario['tipo'] === 'admin' ? 'perfil-tag--admin' : 'perfil-tag--user' ?>"><i></i><?= $usuario['tipo'] === 'admin' ? 'Administrador' : 'Usuário' ?></span></td>
                        <td><div class="acoes">
                            <a class="acao-icone" href="form_usuario.php?id=<?= (int) $usuario['id'] ?>" aria-label="Editar <?= e($usuario['nome']) ?>" title="Editar">✎</a>
                            <form method="post" action="../controllers/usuario.php" onsubmit="return confirm('Deseja excluir este usuário?');">
                                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                <button class="acao-icone acao-icone--perigo" type="submit" aria-label="Excluir <?= e($usuario['nome']) ?>" title="Excluir">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$usuarios): ?>
                    <tr><td colspan="4" class="tabela-vazia">Nenhum usuário cadastrado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
