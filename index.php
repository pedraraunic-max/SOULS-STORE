<?php

require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/config/conexao.php';
require_once __DIR__ . '/admin/models/games.php';

$buscaRecebida = $_GET['buscar'] ?? '';
$busca = is_string($buscaRecebida) ? trim($buscaRecebida) : '';
$catalogo = new Game();
$games = $catalogo->listarCatalogo($busca, '');
$usuario = usuarioAutenticado();
$idsNaBiblioteca = [];
$temasCapa = [
    'DARK SOULS: REMASTERED' => 'dark-souls',
    'DARK SOULS II: SCHOLAR OF THE FIRST SIN' => 'dark-souls-ii',
    'DARK SOULS III' => 'dark-souls-iii',
    'ELDEN RING' => 'elden-ring',
    'SEKIRO: SHADOWS DIE TWICE' => 'sekiro',
    'BLOODBORNE' => 'bloodborne',
];
$resolverImagem = static function (array $game): string {
    $arquivo = trim((string) ($game['imagem'] ?? ''));
    $extensaoValida = preg_match('/\.(jpe?g|png|webp|avif|svg)$/i', $arquivo) === 1;

    if (
        $arquivo === ''
        || basename($arquivo) !== $arquivo
        || !$extensaoValida
        || !is_file(__DIR__ . '/admin/public/images/' . $arquivo)
    ) {
        return '';
    }

    return urlApp('admin/public/images/' . rawurlencode($arquivo));
};
$jogoDestaque = null;

foreach ($games as $gameEmDestaque) {
    if ($gameEmDestaque['titulo'] === 'ELDEN RING') {
        $jogoDestaque = $gameEmDestaque;
        break;
    }
}

$jogoDestaque ??= $games[0] ?? null;
$imagemDestaque = $jogoDestaque !== null ? $resolverImagem($jogoDestaque) : '';

if ($usuario !== null) {
    $stmtColecao = $pdo->prepare(
        "SELECT game_id FROM pedidos
         WHERE usuario_id = :usuario_id AND status = 'concluido'"
    );
    $stmtColecao->execute(['usuario_id' => (int) $usuario['id']]);

    foreach ($stmtColecao->fetchAll(PDO::FETCH_COLUMN) as $idGame) {
        $idsNaBiblioteca[(int) $idGame] = true;
    }
}

$avisoLoja = $_SESSION['aviso_loja'] ?? null;
unset($_SESSION['aviso_loja']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05080e">
    <meta name="description" content="Souls Store: um acervo dedicado aos mundos sombrios dos soulslikes.">
    <title>Souls Store — O acervo dos sem-luz</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(urlApp('admin/public/css/style.css?v=20261022-buycta')) ?>">
    <script src="<?= e(urlApp('admin/public/js/store.js?v=20261022-buycta')) ?>" defer></script>
</head>
<body class="pagina-loja pagina-souls-v2 souls-v3">
<div class="souls-world-flare" aria-hidden="true"></div>
<header class="topo-loja topo-loja-v2">
    <div class="topo-loja__miolo topo-loja__miolo-v2">
        <a class="marca-loja marca-loja-v2" href="<?= e(urlApp('index.php')) ?>" aria-label="Souls Store, início">
            <span class="marca-loja__icone" aria-hidden="true">✦</span>
            <span>SOULS<span class="marca-loja__claro"> STORE</span></span>
        </a>

        <nav class="nav-loja nav-loja-v2" aria-label="Navegação principal">
            <a class="nav-loja__ativa" href="#inicio" aria-current="page">Início</a>
            <a href="#acervo">O acervo</a>
            <?php if ($usuario !== null): ?>
                <a href="<?= e(urlApp('biblioteca.php')) ?>">Minha biblioteca</a>
            <?php endif; ?>
            <?php if (usuarioEhAdmin()): ?>
                <a href="<?= e(urlApp('admin/views/ver_games.php')) ?>">Painel ADM</a>
            <?php endif; ?>
        </nav>

        <div class="conta-loja conta-loja-v2">
            <?php if ($usuario !== null): ?>
                <span class="conta-loja__identidade">
                    <span class="avatar-conta"><?= e(inicial($usuario['nome'])) ?></span>
                    <span class="conta-loja__nome"><small>Conectado</small><?= e($usuario['nome']) ?></span>
                </span>
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

<main class="loja-conteudo loja-conteudo-v2 souls-store-shell">
    <section class="souls-expedition" id="inicio" aria-label="Jogo em destaque" data-feature-stage>
        <div class="souls-expedition__texture" aria-hidden="true"></div>
        <div class="souls-expedition__copy">
            <span class="souls-expedition__eyebrow"><i></i> ARQUIVO DAS TERRAS ESQUECIDAS <b>•</b> 06 MUNDOS</span>
            <h1><span data-feature-line>A noite te chama</span><em data-feature-slogan>Escolha um mundo.</em></h1>
            <p data-feature-summary><?= e($jogoDestaque['descricao'] ?? 'Reinos em ruínas. Mestres à espreita. Uma coleção para quem encara a noite de frente.') ?></p>
            <div class="souls-expedition__actions">
                <button class="expedition-button" type="button" data-feature-details>Ver detalhes <span aria-hidden="true">↗</span></button>
                <?php if ($jogoDestaque !== null): ?>
                    <?php if ($usuario !== null): ?>
                        <form class="souls-feature-purchase" method="post" action="<?= e(urlApp('admin/controllers/comprar_game.php')) ?>" data-feature-buy-form>
                            <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                            <input type="hidden" name="game_id" value="<?= (int) $jogoDestaque['id'] ?>" data-feature-buy-game>
                            <button class="store-button store-button--buy" type="submit">Comprar este jogo <span aria-hidden="true">↗</span></button>
                        </form>
                    <?php else: ?>
                        <a class="store-button store-button--buy" href="<?= e(urlApp('login.php')) ?>">Entrar para comprar <span aria-hidden="true">↗</span></a>
                    <?php endif; ?>
                <?php endif; ?>
                <a class="expedition-link" href="#acervo">Explorar o acervo <span aria-hidden="true">↓</span></a>
            </div>

            <div class="souls-expedition__current" aria-live="polite">
                <span>EM DESTAQUE <i data-feature-count>01 / 06</i></span>
                <strong data-feature-title><?= e($jogoDestaque['titulo'] ?? 'Souls Store') ?></strong>
                <small data-feature-category><?= e($jogoDestaque['categoria'] ?? 'Uma coleção soulslike') ?></small>
            </div>

            <div class="souls-expedition__controls" aria-label="Navegar pelos jogos em destaque">
                <button type="button" class="expedition-arrow" data-feature-prev aria-label="Jogo anterior">←</button>
                <div class="expedition-dots" role="group" aria-label="Escolher jogo em destaque">
                    <?php foreach ($games as $indice => $game): ?>
                        <button
                            type="button"
                            data-feature-dot="<?= $indice ?>"
                            aria-label="Destacar <?= e($game['titulo']) ?>"
                            aria-pressed="<?= $game['id'] === ($jogoDestaque['id'] ?? null) ? 'true' : 'false' ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="expedition-arrow" data-feature-next aria-label="Próximo jogo">→</button>
            </div>
        </div>

        <?php if ($jogoDestaque !== null): ?>
            <?php $precoDestaque = 'R$ ' . number_format((float) $jogoDestaque['preco'], 2, ',', '.'); ?>
            <div class="souls-expedition__visual" data-feature-visual>
                <div class="expedition-orbit expedition-orbit--one" aria-hidden="true"></div>
                <div class="expedition-orbit expedition-orbit--two" aria-hidden="true"></div>
                <div class="souls-expedition__poster">
                    <?php if ($imagemDestaque !== ''): ?>
                        <img src="<?= e($imagemDestaque) ?>" alt="Capa de <?= e($jogoDestaque['titulo']) ?>" data-feature-image>
                    <?php else: ?>
                        <img src="" alt="" data-feature-image hidden>
                    <?php endif; ?>
                    <span class="souls-expedition__shade" aria-hidden="true"></span>
                    <span class="souls-expedition__poster-label">SOULS STORE <i>•</i> EDIÇÃO LIMITADA</span>
                    <span class="souls-expedition__poster-name" data-feature-poster-title><?= e($jogoDestaque['titulo']) ?></span>
                </div>
                <div class="souls-expedition__floating-card">
                    <span><i></i> JOGO EM DESTAQUE</span>
                    <strong data-feature-price><?= e($precoDestaque) ?></strong>
                    <small>Compra simulada · sem checkout</small>
                </div>
                <span class="souls-expedition__rune" aria-hidden="true">✦</span>
            </div>
        <?php endif; ?>

        <div class="souls-expedition__bottom" aria-hidden="true"><span>01</span><i></i><span>06</span><b>ENCONTRE SEU DESTINO</b></div>
    </section>

    <?php if (is_array($avisoLoja) && !empty($avisoLoja['texto'])): ?>
        <div class="store-notice store-notice--<?= e($avisoLoja['tipo'] ?? 'info') ?>" role="status">
            <span><?= e($avisoLoja['texto']) ?></span>
            <?php if (!empty($avisoLoja['link'])): ?>
                <a href="<?= e(urlApp($avisoLoja['link'])) ?>">Abrir biblioteca</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="catalogo-secao souls-acervo" id="acervo">
        <div class="catalogo-cabecalho souls-acervo__cabecalho">
            <div>
                <span class="sobretitulo">SEU PRÓXIMO DESTINO</span>
                <h2>Escolha um mundo</h2>
                <p>Cada capa abre um portal. Cada portal muda a loja.</p>
            </div>
            <span class="catalogo-cabecalho__contador" data-visible-count><?= count($games) ?> mundos disponíveis</span>
        </div>

        <div class="souls-discovery-bar">
            <div class="souls-filter-tabs" role="group" aria-label="Filtrar mundos">
                <button type="button" class="souls-filter-tab is-active" data-collection-filter="all" aria-pressed="true">Todos <span><?= count($games) ?></span></button>
                <button type="button" class="souls-filter-tab" data-collection-filter="saga" aria-pressed="false">Trilogia <span>03</span></button>
                <button type="button" class="souls-filter-tab" data-collection-filter="world" aria-pressed="false">Outros mundos <span>03</span></button>
                <button type="button" class="souls-filter-tab" data-collection-filter="saved" aria-pressed="false">Minha lista <span data-favorite-total>0</span></button>
            </div>

            <form class="filtros-loja souls-busca" method="get" action="<?= e(urlApp('index.php')) ?>" data-search-form>
            <label class="busca-loja">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="buscar" value="<?= e($busca) ?>" placeholder="Buscar um mundo..." aria-label="Buscar jogos no acervo" data-live-search>
            </label>
            <button class="botao-filtro" type="submit" aria-label="Buscar jogos">⌕</button>
            <?php if ($busca !== ''): ?>
                <a class="limpar-filtros" href="<?= e(urlApp('index.php#acervo')) ?>">Limpar busca</a>
            <?php endif; ?>
            </form>
        </div>

        <?php if ($games): ?>
            <div class="grade-jogos souls-grade souls-v3-grid" data-game-grid>
                <?php foreach ($games as $game): ?>
                    <?php
                    $gameId = (int) $game['id'];
                    $imagemUrl = $resolverImagem($game);
                    $imagemDisponivel = $imagemUrl !== '';
                    $tema = $temasCapa[$game['titulo']] ?? 'souls';
                    $colecao = str_starts_with($game['titulo'], 'DARK SOULS') ? 'saga' : 'world';
                    $precoTexto = 'R$ ' . number_format((float) $game['preco'], 2, ',', '.');
                    $comprado = isset($idsNaBiblioteca[$gameId]);
                    ?>
                    <article class="jogo-card souls-card souls-v3-card" id="jogo-<?= $gameId ?>" data-game-item data-game-id="<?= $gameId ?>" data-collection="<?= e($colecao) ?>">
                        <div class="souls-v3-art">
                            <button
                                class="capa-jogo capa-jogo--<?= e($tema) ?> souls-v3-cover<?= $imagemDisponivel ? ' capa-jogo--imagem' : '' ?>"
                                type="button"
                                data-game-card
                                data-game-id="<?= $gameId ?>"
                                data-world="<?= e($tema) ?>"
                                data-titulo="<?= e($game['titulo']) ?>"
                                data-resumo="<?= e($game['descricao']) ?>"
                                data-categoria="<?= e($game['categoria'] ?: 'Soulslike') ?>"
                                data-preco="<?= e($precoTexto) ?>"
                                data-imagem="<?= e($imagemUrl) ?>"
                                aria-label="Entrar no mundo de <?= e($game['titulo']) ?> e ver resumo"
                            >
                                <?php if ($imagemDisponivel): ?>
                                    <img class="capa-jogo__imagem" src="<?= e($imagemUrl) ?>" alt="Capa de <?= e($game['titulo']) ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="capa-jogo__orbe" aria-hidden="true"></span>
                                    <span class="capa-jogo__sigilo" aria-hidden="true">✦</span>
                                <?php endif; ?>
                                <span class="souls-v3-cover__shade" aria-hidden="true"></span>
                                <span class="souls-v3-cover__label"><?= e($colecao === 'saga' ? 'A TRILOGIA' : 'MUNDO SOLO') ?></span>
                                <span class="capa-jogo__titulo"><?= e($game['titulo']) ?></span>
                                <span class="souls-v3-cover__enter">ABRIR PORTAL <i aria-hidden="true">↗</i></span>
                                <span class="capa-jogo__flash" aria-hidden="true"></span>
                            </button>
                            <button class="souls-v3-favorite" type="button" data-favorite="<?= $gameId ?>" aria-label="Adicionar <?= e($game['titulo']) ?> à minha lista" aria-pressed="false">♡</button>
                            <span class="souls-v3-art__index" aria-hidden="true"><?= str_pad((string) ($gameId), 2, '0', STR_PAD_LEFT) ?> <i>•</i> 06</span>
                        </div>
                        <div class="jogo-card__corpo souls-card__corpo">
                            <div class="jogo-card__titulo-linha">
                                <h3><?= e($game['titulo']) ?></h3>
                                <?php if ($comprado): ?>
                                    <span class="souls-disponibilidade souls-disponibilidade--owned">Na biblioteca</span>
                                <?php else: ?>
                                    <span class="jogo-card__disponibilidade souls-disponibilidade"><i></i> No acervo</span>
                                <?php endif; ?>
                            </div>
                            <p><?= e($game['descricao']) ?></p>
                            <div class="jogo-card__rodape">
                                <small><?= e($game['categoria'] ?: 'Soulslike') ?></small>
                                <strong><?= e($precoTexto) ?></strong>
                            </div>
                            <div class="souls-card__acoes">
                                <?php if ($comprado): ?>
                                    <a class="store-button store-button--owned" href="<?= e(urlApp('biblioteca.php')) ?>">Na biblioteca <span aria-hidden="true">✓</span></a>
                                <?php elseif ($usuario !== null): ?>
                                    <form method="post" action="<?= e(urlApp('admin/controllers/comprar_game.php')) ?>">
                                        <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                                        <input type="hidden" name="game_id" value="<?= $gameId ?>">
                                        <button class="store-button store-button--buy" type="submit">Comprar <span aria-hidden="true">↗</span></button>
                                    </form>
                                <?php else: ?>
                                    <a class="store-button store-button--buy" href="<?= e(urlApp('login.php')) ?>">Entrar para comprar <span aria-hidden="true">→</span></a>
                                <?php endif; ?>
                                <span class="souls-card__nota">Compra simulada</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="souls-no-results" data-no-results hidden>
                <span aria-hidden="true">✦</span>
                <strong>Nenhum portal encontrado</strong>
                <p>Tente outra busca ou escolha um filtro diferente.</p>
            </div>
        <?php else: ?>
            <div class="catalogo-vazio souls-vazio">
                <span aria-hidden="true">☾</span>
                <h3>Este caminho não levou a lugar algum</h3>
                <p>Tente buscar outro título do acervo.</p>
                <a href="<?= e(urlApp('index.php#acervo')) ?>">Ver todos os jogos</a>
            </div>
        <?php endif; ?>

        <p class="store-disclaimer"><span aria-hidden="true">ⓘ</span> Projeto acadêmico: não há pagamento real nem etapa de checkout.</p>
    </section>

    <footer class="rodape-loja souls-rodape">
        <a class="marca-loja marca-loja-v2 marca-loja--pequena" href="<?= e(urlApp('index.php')) ?>"><span class="marca-loja__icone" aria-hidden="true">✦</span> SOULS STORE</a>
        <span>Que a chama guie seu caminho.</span>
        <?php if ($usuario === null): ?>
            <a href="<?= e(urlApp('registrar.php')) ?>">Junte-se aos sem-luz <span aria-hidden="true">→</span></a>
        <?php endif; ?>
    </footer>
</main>

<dialog class="detalhe-jogo" id="detalhe-jogo" aria-labelledby="detalhe-titulo">
    <div class="detalhe-jogo__painel">
        <button class="detalhe-jogo__fechar" type="button" data-fechar-detalhe aria-label="Fechar resumo">×</button>
        <div class="detalhe-jogo__capa" data-detalhe-capa>
            <img src="" alt="" data-detalhe-imagem hidden>
            <span class="detalhe-jogo__sigilo" aria-hidden="true">✦</span>
            <span class="detalhe-jogo__marca">SOULS STORE</span>
        </div>
        <div class="detalhe-jogo__texto">
            <span class="sobretitulo" data-detalhe-categoria>SOULSLIKE</span>
            <h2 id="detalhe-titulo" data-detalhe-titulo></h2>
            <p data-detalhe-resumo></p>
            <div class="detalhe-jogo__rodape">
                <span>PREÇO</span>
                <strong data-detalhe-preco></strong>
            </div>
            <?php if ($usuario !== null): ?>
                <form class="detalhe-jogo__compra" method="post" action="<?= e(urlApp('admin/controllers/comprar_game.php')) ?>">
                    <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                    <input type="hidden" name="game_id" value="<?= (int) ($jogoDestaque['id'] ?? 0) ?>" data-feature-buy-game>
                    <button class="store-button store-button--buy" type="submit">Comprar este jogo <span aria-hidden="true">↗</span></button>
                    <small>Compra simulada · sem pagamento real</small>
                </form>
            <?php else: ?>
                <a class="store-button store-button--buy detalhe-jogo__compra" href="<?= e(urlApp('login.php')) ?>">Entrar para comprar <span aria-hidden="true">↗</span></a>
            <?php endif; ?>
            <button class="store-button store-button--quiet" type="button" data-fechar-detalhe>Voltar ao acervo <span aria-hidden="true">↗</span></button>
        </div>
    </div>
</dialog>
</body>
</html>
