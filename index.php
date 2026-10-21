<?php

require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/models/games.php';

$buscaRecebida = $_GET['buscar'] ?? '';
$categoriaRecebida = $_GET['categoria'] ?? '';
$busca = is_string($buscaRecebida) ? trim($buscaRecebida) : '';
$categoriaSelecionada = is_string($categoriaRecebida)
    ? trim($categoriaRecebida)
    : '';

$catalogo = new Game();
$games = $catalogo->listarCatalogo($busca, $categoriaSelecionada);
$categorias = $catalogo->listarCategorias();
$usuario = usuarioAutenticado();
$totalEstoque = array_sum(array_map(
    static function ($game) {
        return (int) $game['estoque'];
    },
    $games
));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07111d">
    <title>GameStock — Jogos para descobrir</title>
    <link rel="stylesheet" href="<?= e(urlApp('admin/public/css/style.css')) ?>">
</head>
<body class="pagina-loja">
<header class="topo-loja">
    <div class="topo-loja__miolo">
        <a class="marca-loja" href="<?= e(urlApp('index.php')) ?>" aria-label="GameStock, início">
            <span class="marca-loja__icone">G</span>
            <span>GAME<span class="marca-loja__claro">STOCK</span></span>
        </a>

        <nav class="nav-loja" aria-label="Navegação principal">
            <a class="nav-loja__ativa" href="#catalogo">Loja</a>
            <a href="#catalogo">Catálogo</a>
            <?php if (usuarioEhAdmin()): ?>
                <a href="<?= e(urlApp('admin/views/ver_games.php')) ?>">Painel ADM</a>
            <?php endif; ?>
        </nav>

        <div class="conta-loja">
            <?php if ($usuario): ?>
                <div class="conta-loja__identidade">
                    <span class="avatar-conta"><?= e(inicial($usuario['nome'])) ?></span>
                    <span class="conta-loja__nome">
                        <small>Logado como</small>
                        <?= e($usuario['nome']) ?>
                    </span>
                </div>
                <form method="post" action="<?= e(urlApp('sair.php')) ?>">
                    <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                    <button class="link-sair" type="submit">Sair</button>
                </form>
            <?php else: ?>
                <a class="botao-texto" href="<?= e(urlApp('login.php')) ?>">Entrar</a>
                <a class="botao-contorno" href="<?= e(urlApp('registrar.php')) ?>">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="loja-conteudo">
    <section class="hero-loja">
        <div class="hero-loja__texto">
            <span class="sobretitulo"><span class="ponto-ao-vivo"></span> CENTRAL DE JOGOS</span>
            <h1>O próximo mundo<br>que você vai <span>explorar.</span></h1>
            <p>Descubra sua próxima aventura entre títulos para todos os estilos de jogador.</p>
            <a class="botao-principal" href="#catalogo">Explorar catálogo <span aria-hidden="true">↘</span></a>
        </div>
        <div class="hero-loja__painel" aria-label="Resumo do catálogo">
            <div class="hero-loja__brilho"></div>
            <span class="hero-loja__selo">SUA PRÓXIMA AVENTURA</span>
            <div class="hero-loja__arte" aria-hidden="true">
                <div class="arte-orbita arte-orbita--um"></div>
                <div class="arte-orbita arte-orbita--dois"></div>
                <span class="arte-simbolo">✦</span>
            </div>
            <div class="hero-loja__rodape">
                <div><small>NO CATÁLOGO</small><strong><?= count($games) ?> <span>jogos</span></strong></div>
                <div><small>ITENS DISPONÍVEIS</small><strong><?= $totalEstoque ?> <span>unidades</span></strong></div>
                <span class="hero-loja__status"><i></i> ATUALIZADO</span>
            </div>
        </div>
        <div class="hero-loja__linha" aria-hidden="true"></div>
    </section>

    <section class="catalogo-secao" id="catalogo">
        <div class="catalogo-cabecalho">
            <div>
                <span class="sobretitulo">ESCOLHA SUA PRÓXIMA PARTIDA</span>
                <h2>Explore o catálogo</h2>
            </div>
            <span class="catalogo-cabecalho__contador"><?= count($games) ?> resultado(s)</span>
        </div>

        <form class="filtros-loja" method="get" action="<?= e(urlApp('index.php')) ?>">
            <label class="busca-loja">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="buscar" value="<?= e($busca) ?>" placeholder="Buscar jogos..." aria-label="Buscar jogos">
            </label>
            <label class="seletor-categoria">
                <span class="sr-only">Filtrar por categoria</span>
                <select name="categoria">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $itemCategoria): ?>
                        <option value="<?= e($itemCategoria) ?>" <?= $categoriaSelecionada === $itemCategoria ? 'selected' : '' ?>>
                            <?= e($itemCategoria) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="botao-filtro" type="submit">Filtrar</button>
            <?php if ($busca !== '' || $categoriaSelecionada !== ''): ?>
                <a class="limpar-filtros" href="<?= e(urlApp('index.php#catalogo')) ?>">Limpar</a>
            <?php endif; ?>
        </form>

        <?php if ($games): ?>
            <div class="grade-jogos">
                <?php foreach ($games as $indice => $game): ?>
                    <?php
                    $arquivoImagem = trim((string) ($game['imagem'] ?? ''));
                    $extensaoValida = preg_match('/\.(jpe?g|png|webp|avif)$/i', $arquivoImagem) === 1;
                    $imagemDisponivel = $arquivoImagem !== ''
                        && basename($arquivoImagem) === $arquivoImagem
                        && $extensaoValida
                        && is_file(__DIR__ . '/admin/public/images/' . $arquivoImagem);
                    $classeCapa = 'capa-gradiente--' . (($indice % 5) + 1);
                    ?>
                    <article class="jogo-card">
                        <div class="jogo-card__capa <?= $imagemDisponivel ? 'jogo-card__capa--imagem' : e($classeCapa) ?>">
                            <?php if ($imagemDisponivel): ?>
                                <img src="<?= e(urlApp('admin/public/images/' . rawurlencode($arquivoImagem))) ?>" alt="Capa de <?= e($game['titulo']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="capa-gradiente__marca">GS<span> / PLAY</span></span>
                                <span class="capa-gradiente__letra" aria-hidden="true"><?= e(inicial($game['titulo'])) ?></span>
                            <?php endif; ?>
                            <span class="jogo-card__categoria"><?= e($game['categoria'] ?: 'Jogo') ?></span>
                        </div>
                        <div class="jogo-card__corpo">
                            <div class="jogo-card__titulo-linha">
                                <h3><?= e($game['titulo']) ?></h3>
                                <span class="jogo-card__disponibilidade <?= (int) $game['estoque'] > 0 ? '' : 'jogo-card__disponibilidade--esgotado' ?>">
                                    <i></i><?= (int) $game['estoque'] > 0 ? 'Disponível' : 'Esgotado' ?>
                                </span>
                            </div>
                            <p><?= e($game['descricao'] ?: 'Uma nova aventura espera por você.') ?></p>
                            <div class="jogo-card__rodape">
                                <small><?= (int) $game['estoque'] > 0 ? (int) $game['estoque'] . ' em estoque' : 'Sem unidades no momento' ?></small>
                                <strong>R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></strong>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="catalogo-vazio">
                <span aria-hidden="true">⌕</span>
                <h3>Nenhum jogo encontrado</h3>
                <p>Tente buscar outro título ou escolha uma categoria diferente.</p>
                <a href="<?= e(urlApp('index.php#catalogo')) ?>">Ver catálogo completo</a>
            </div>
        <?php endif; ?>
    </section>

    <footer class="rodape-loja">
        <a class="marca-loja marca-loja--pequena" href="<?= e(urlApp('index.php')) ?>"><span class="marca-loja__icone">G</span> GAMESTOCK</a>
        <span>Seu próximo jogo começa aqui.</span>
        <?php if (!$usuario): ?>
            <a href="<?= e(urlApp('registrar.php')) ?>">Junte-se à comunidade <span aria-hidden="true">→</span></a>
        <?php endif; ?>
    </footer>
</main>
</body>
</html>
