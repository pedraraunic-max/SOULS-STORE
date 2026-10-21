<?php
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$abaUsuarios = in_array(
    $paginaAtual,
    ['ver_usuarios.php', 'form_usuario.php'],
    true
);
$usuarioPainel = usuarioAutenticado();
?>

<header class="cabecalho-admin">
    <a class="marca-loja" href="<?= e(urlApp('admin/views/ver_games.php')) ?>">
        <span class="marca-loja__icone">G</span>
        <span>GAME<span class="marca-loja__claro">STOCK</span></span>
    </a>

    <nav class="menu-admin" aria-label="Navegação do painel">
        <a class="<?= !$abaUsuarios ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_games.php')) ?>" <?= !$abaUsuarios ? 'aria-current="page"' : '' ?>>
            <span aria-hidden="true">▦</span> Jogos
        </a>
        <a class="<?= $abaUsuarios ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_usuarios.php')) ?>" <?= $abaUsuarios ? 'aria-current="page"' : '' ?>>
            <span aria-hidden="true">♙</span> Usuários
        </a>
    </nav>

    <div class="admin-conta">
        <div class="admin-conta__pessoa">
            <span class="avatar-conta"><?= e(inicial($usuarioPainel['nome'] ?? 'A')) ?></span>
            <span class="admin-conta__nome"><strong><?= e($usuarioPainel['nome'] ?? 'Administrador') ?></strong><small>Administrador</small></span>
        </div>
        <a class="admin-loja-link" href="<?= e(urlApp('index.php')) ?>" title="Abrir loja" aria-label="Abrir loja">↗</a>
        <form method="post" action="<?= e(urlApp('sair.php')) ?>">
            <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
            <button class="link-sair" type="submit">Sair</button>
        </form>
    </div>
</header>
