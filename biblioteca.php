<?php

require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/config/conexao.php';

exigirLogin();
$usuario = usuarioAutenticado();

$stmtBiblioteca = $pdo->prepare(
    "SELECT g.id, g.titulo, g.descricao, cat.nome AS categoria, g.imagem,
            p.preco_total AS preco_pago, p.criado_em AS comprado_em
     FROM pedidos p
     INNER JOIN games g ON g.id = p.game_id
     INNER JOIN categorias cat ON cat.id = g.categoria_id
     WHERE p.usuario_id = :usuario_id AND p.status = 'concluido'
     ORDER BY p.criado_em DESC, g.titulo ASC"
);
$stmtBiblioteca->execute(['usuario_id' => (int) $usuario['id']]);
$jogos = $stmtBiblioteca->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05080e">
    <title>Minha biblioteca — Souls Store</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(urlApp('admin/public/css/style.css?v=20261022')) ?>">
</head>
<body class="pagina-loja pagina-souls-v2">
<header class="topo-loja topo-loja-v2">
    <div class="topo-loja__miolo topo-loja__miolo-v2">
        <a class="marca-loja marca-loja-v2" href="<?= e(urlApp('index.php')) ?>" aria-label="Souls Store, início">
            <span class="marca-loja__icone" aria-hidden="true">✦</span>
            <span>SOULS<span class="marca-loja__claro"> STORE</span></span>
        </a>
        <nav class="nav-loja nav-loja-v2" aria-label="Navegação principal">
            <a href="<?= e(urlApp('index.php#acervo')) ?>">O acervo</a>
            <a class="nav-loja__ativa" href="<?= e(urlApp('biblioteca.php')) ?>" aria-current="page">Minha biblioteca</a>
            <?php if (usuarioEhAdmin()): ?>
                <a href="<?= e(urlApp('admin/views/ver_games.php')) ?>">Painel ADM</a>
            <?php endif; ?>
        </nav>
        <div class="conta-loja conta-loja-v2">
            <span class="conta-loja__identidade">
                <span class="avatar-conta"><?= e(inicial($usuario['nome'])) ?></span>
                <span class="conta-loja__nome"><small>Conectado</small><?= e($usuario['nome']) ?></span>
            </span>
            <form method="post" action="<?= e(urlApp('sair.php')) ?>">
                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                <button class="link-sair" type="submit">Sair</button>
            </form>
        </div>
    </div>
</header>

<main class="loja-conteudo loja-conteudo-v2 biblioteca-souls">
    <section class="catalogo-secao souls-acervo">
        <div class="catalogo-cabecalho souls-acervo__cabecalho">
            <div>
                <span class="sobretitulo">SUA COLEÇÃO</span>
                <h2>Minha biblioteca</h2>
                <p>Os mundos que você escolheu explorar.</p>
            </div>
            <span class="catalogo-cabecalho__contador"><?= count($jogos) ?> jogo(s) adquiridos</span>
        </div>

        <?php if ($jogos): ?>
            <div class="grade-jogos souls-grade">
                <?php foreach ($jogos as $game): ?>
                    <?php
                    $arquivoImagem = trim((string) ($game['imagem'] ?? ''));
                    $extensaoValida = preg_match('/\.(jpe?g|png|webp|avif|svg)$/i', $arquivoImagem) === 1;
                    $imagemDisponivel = $arquivoImagem !== ''
                        && basename($arquivoImagem) === $arquivoImagem
                        && $extensaoValida
                        && is_file(__DIR__ . '/admin/public/images/' . $arquivoImagem);
                    $imagemUrl = $imagemDisponivel
                        ? urlApp('admin/public/images/' . rawurlencode($arquivoImagem))
                        : '';
                    ?>
                    <article class="jogo-card souls-card biblioteca-card">
                        <div class="capa-jogo biblioteca-card__capa<?= $imagemDisponivel ? ' capa-jogo--imagem' : '' ?>">
                            <?php if ($imagemDisponivel): ?>
                                <img class="capa-jogo__imagem" src="<?= e($imagemUrl) ?>" alt="Capa de <?= e($game['titulo']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="capa-jogo__orbe" aria-hidden="true"></span>
                                <span class="capa-jogo__sigilo" aria-hidden="true">✦</span>
                            <?php endif; ?>
                            <span class="capa-jogo__marca">SOULS <i>STORE</i></span>
                            <span class="capa-jogo__titulo"><?= e($game['titulo']) ?></span>
                            <span class="biblioteca-card__selo">NA SUA BIBLIOTECA</span>
                        </div>
                        <div class="jogo-card__corpo souls-card__corpo">
                            <div class="jogo-card__titulo-linha">
                                <h3><?= e($game['titulo']) ?></h3>
                                <span class="souls-disponibilidade souls-disponibilidade--owned">Adquirido</span>
                            </div>
                            <p><?= e($game['descricao']) ?></p>
                            <div class="biblioteca-card__recibo">
                                <span>Compra simulada · R$ <?= e(number_format((float) $game['preco_pago'], 2, ',', '.')) ?></span>
                                <time datetime="<?= e(date(DATE_ATOM, strtotime($game['comprado_em']))) ?>"><?= e(date('d/m/Y', strtotime($game['comprado_em']))) ?></time>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <section class="catalogo-vazio souls-vazio">
                <span aria-hidden="true">✦</span>
                <h3>Sua jornada ainda não começou</h3>
                <p>Adicione um jogo do acervo e ele ficará guardado aqui.</p>
                <a class="store-button store-button--buy" href="<?= e(urlApp('index.php#acervo')) ?>">Explorar jogos <span aria-hidden="true">→</span></a>
            </section>
        <?php endif; ?>

        <p class="store-disclaimer"><span aria-hidden="true">ⓘ</span> Esta biblioteca registra compras simuladas para fins acadêmicos; nenhum pagamento é realizado.</p>
    </section>
</main>
</body>
</html>
