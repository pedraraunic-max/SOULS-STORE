<?php
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$abas = [
    'jogos' => ['ver_games.php', 'cadastrar_game.php', 'editar_game.php'],
    'categorias' => ['ver_categorias.php', 'form_categoria.php'],
    'clientes' => ['ver_clientes.php', 'form_cliente.php'],
    'pedidos' => ['ver_pedidos.php', 'form_pedido.php'],
    'usuarios' => ['ver_usuarios.php', 'form_usuario.php'],
];
$abaAtiva = 'jogos';
foreach ($abas as $nomeAba => $paginas) {
    if (in_array($paginaAtual, $paginas, true)) {
        $abaAtiva = $nomeAba;
        break;
    }
}
$usuarioPainel = usuarioAutenticado();
?>

<header class="cabecalho-admin">
    <a class="marca-loja" href="<?= e(urlApp('admin/views/ver_games.php')) ?>">
        <span class="marca-loja__icone" aria-hidden="true">✦</span>
        <span>SOULS<span class="marca-loja__claro"> STORE</span></span>
    </a>

    <nav class="menu-admin" aria-label="Navegação do painel">
        <a class="<?= $abaAtiva === 'jogos' ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_games.php')) ?>" <?= $abaAtiva === 'jogos' ? 'aria-current="page"' : '' ?>><span aria-hidden="true">▦</span> Jogos</a>
        <a class="<?= $abaAtiva === 'categorias' ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_categorias.php')) ?>" <?= $abaAtiva === 'categorias' ? 'aria-current="page"' : '' ?>><span aria-hidden="true">◈</span> Categorias</a>
        <a class="<?= $abaAtiva === 'clientes' ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_clientes.php')) ?>" <?= $abaAtiva === 'clientes' ? 'aria-current="page"' : '' ?>><span aria-hidden="true">♙</span> Clientes</a>
        <a class="<?= $abaAtiva === 'pedidos' ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_pedidos.php')) ?>" <?= $abaAtiva === 'pedidos' ? 'aria-current="page"' : '' ?>><span aria-hidden="true">▤</span> Pedidos</a>
        <a class="<?= $abaAtiva === 'usuarios' ? 'ativa' : '' ?>" href="<?= e(urlApp('admin/views/ver_usuarios.php')) ?>" <?= $abaAtiva === 'usuarios' ? 'aria-current="page"' : '' ?>><span aria-hidden="true">⚙</span> Usuários</a>
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
