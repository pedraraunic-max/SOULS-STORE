(() => {
    const body = document.body;
    const cards = Array.from(document.querySelectorAll('[data-game-card]'));

    if (!cards.length) {
        return;
    }

    const dialog = document.querySelector('#detalhe-jogo');
    const feature = {
        stage: document.querySelector('[data-feature-stage]'),
        image: document.querySelector('[data-feature-image]'),
        summary: document.querySelector('[data-feature-summary]'),
        title: document.querySelector('[data-feature-title]'),
        posterTitle: document.querySelector('[data-feature-poster-title]'),
        category: document.querySelector('[data-feature-category]'),
        price: document.querySelector('[data-feature-price]'),
        count: document.querySelector('[data-feature-count]'),
        line: document.querySelector('[data-feature-line]'),
        slogan: document.querySelector('[data-feature-slogan]'),
        visual: document.querySelector('[data-feature-visual]'),
    };
    const details = dialog ? {
        title: dialog.querySelector('[data-detalhe-titulo]'),
        summary: dialog.querySelector('[data-detalhe-resumo]'),
        category: dialog.querySelector('[data-detalhe-categoria]'),
        price: dialog.querySelector('[data-detalhe-preco]'),
        image: dialog.querySelector('[data-detalhe-imagem]'),
        cover: dialog.querySelector('[data-detalhe-capa]'),
    } : null;
    const storylines = {
        'dark-souls': ['A chama ainda', 'pode renascer.'],
        'dark-souls-ii': ['Drangleic chama', 'seu portador.'],
        'dark-souls-iii': ['Quando a chama', 'vacila.'],
        'elden-ring': ['Torne-se', 'Lorde Prístino.'],
        sekiro: ['A morte', 'não é o fim.'],
        bloodborne: ['A noite', 'tem fome.'],
    };
    const favoriteKey = 'souls-store-lista-de-desejos';
    const reduzirMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let favoritos = new Set();
    let filtroColecao = 'all';
    let jogoSelecionado = 0;
    let timerTransicao;
    let timerAviso;

    try {
        favoritos = new Set(JSON.parse(window.localStorage.getItem(favoriteKey) || '[]').map(String));
    } catch (erro) {
        favoritos = new Set();
    }

    const textoNormalizado = (valor) => String(valor || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('pt-BR')
        .trim();

    const atualizarFavoritos = () => {
        document.querySelectorAll('[data-favorite]').forEach((botao) => {
            const ativo = favoritos.has(String(botao.dataset.favorite));
            botao.setAttribute('aria-pressed', ativo ? 'true' : 'false');
            botao.setAttribute('aria-label', `${ativo ? 'Remover' : 'Adicionar'} ${botao.closest('[data-game-item]')?.querySelector('h3')?.textContent.trim() || 'jogo'} ${ativo ? 'da' : 'à'} minha lista`);
            botao.textContent = ativo ? '♥' : '♡';
        });

        document.querySelectorAll('[data-favorite-total]').forEach((contador) => {
            contador.textContent = String(favoritos.size).padStart(2, '0');
        });
    };

    const mostrarAviso = (mensagem) => {
        let aviso = document.querySelector('[data-world-toast]');

        if (!aviso) {
            aviso = document.createElement('div');
            aviso.className = 'souls-world-toast';
            aviso.dataset.worldToast = '';
            aviso.setAttribute('role', 'status');
            aviso.setAttribute('aria-live', 'polite');
            document.body.append(aviso);
        }

        aviso.textContent = mensagem;
        aviso.classList.add('is-visible');
        window.clearTimeout(timerAviso);
        timerAviso = window.setTimeout(() => aviso.classList.remove('is-visible'), 2200);
    };

    const salvarFavoritos = () => {
        try {
            window.localStorage.setItem(favoriteKey, JSON.stringify(Array.from(favoritos)));
        } catch (erro) {
            // A lista continua disponível enquanto a página estiver aberta.
        }
        atualizarFavoritos();
    };

    const atualizarFiltros = () => {
        const consulta = textoNormalizado(document.querySelector('[data-live-search]')?.value);
        let visiveis = 0;

        document.querySelectorAll('[data-game-item]').forEach((item) => {
            const id = String(item.dataset.gameId);
            const correspondeColecao = filtroColecao === 'all'
                || item.dataset.collection === filtroColecao
                || (filtroColecao === 'saved' && favoritos.has(id));
            const correspondeBusca = consulta === '' || textoNormalizado(item.textContent).includes(consulta);
            item.hidden = !(correspondeColecao && correspondeBusca);
            if (!item.hidden) {
                visiveis += 1;
            }
        });

        const contador = document.querySelector('[data-visible-count]');
        if (contador) {
            contador.textContent = `${String(visiveis).padStart(2, '0')} ${visiveis === 1 ? 'mundo encontrado' : 'mundos encontrados'}`;
        }

        const vazio = document.querySelector('[data-no-results]');
        if (vazio) {
            vazio.hidden = visiveis !== 0;
        }

        document.querySelectorAll('[data-collection-filter]').forEach((botao) => {
            const ativo = botao.dataset.collectionFilter === filtroColecao;
            botao.classList.toggle('is-active', ativo);
            botao.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        });
    };

    const definirImagem = (imagem, url, titulo, classeCapa) => {
        if (!imagem) {
            return;
        }

        imagem.hidden = true;
        imagem.alt = titulo ? `Capa de ${titulo}` : '';
        if (classeCapa) {
            classeCapa.classList.remove('detalhe-jogo__capa--imagem');
        }
        imagem.removeAttribute('src');
        imagem.onload = () => {
            imagem.hidden = false;
            if (classeCapa) {
                classeCapa.classList.add('detalhe-jogo__capa--imagem');
            }
        };
        imagem.onerror = () => {
            imagem.hidden = true;
            if (classeCapa) {
                classeCapa.classList.remove('detalhe-jogo__capa--imagem');
            }
        };
        if (url) {
            imagem.src = url;
        }
    };

    const atualizarMundo = (capa, indice) => {
        const titulo = capa.dataset.titulo || 'Souls Store';
        const mundo = capa.dataset.world || 'neutral';
        const texto = storylines[mundo] || ['A noite te chama', 'Escolha um mundo.'];
        jogoSelecionado = indice;
        body.dataset.world = mundo;
        body.dataset.game = capa.dataset.gameId || '';
        body.classList.remove('world-changing');
        void body.offsetWidth;
        if (!reduzirMovimento) {
            body.classList.add('world-changing');
            window.clearTimeout(timerTransicao);
            timerTransicao = window.setTimeout(() => body.classList.remove('world-changing'), 850);
        }

        if (feature.title) feature.title.textContent = titulo;
        if (feature.posterTitle) feature.posterTitle.textContent = titulo;
        if (feature.category) feature.category.textContent = capa.dataset.categoria || 'Soulslike';
        if (feature.summary) feature.summary.textContent = capa.dataset.resumo || '';
        if (feature.price) feature.price.textContent = capa.dataset.preco || '';
        if (feature.count) feature.count.textContent = `${String(indice + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        if (feature.line) feature.line.textContent = texto[0];
        if (feature.slogan) feature.slogan.textContent = texto[1];
        definirImagem(feature.image, capa.dataset.imagem || '', titulo);
        document.querySelectorAll('[data-feature-buy-game]').forEach((campo) => {
            campo.value = capa.dataset.gameId || '';
        });
        if (feature.stage) {
            feature.stage.dataset.world = mundo;
            feature.stage.setAttribute('aria-label', `Mundo em destaque: ${titulo}`);
        }
        if (feature.visual) feature.visual.dataset.world = mundo;

        document.querySelectorAll('[data-feature-dot]').forEach((ponto) => {
            const ativo = Number(ponto.dataset.featureDot) === indice;
            ponto.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        });
        cards.forEach((outraCapa) => {
            const ativo = outraCapa === capa;
            outraCapa.classList.toggle('is-world-selected', ativo);
            const item = outraCapa.closest('[data-game-item]');
            if (item) item.classList.toggle('is-world-selected', ativo);
        });

        const themeColor = document.querySelector('meta[name="theme-color"]');
        if (themeColor) {
            themeColor.content = getComputedStyle(body).getPropertyValue('--world-theme-color').trim() || '#080e17';
        }
        document.title = `${titulo} · Souls Store`;
    };

    const abrirDetalhe = (capa) => {
        if (!dialog || !details) {
            return;
        }
        details.title.textContent = capa.dataset.titulo || '';
        details.summary.textContent = capa.dataset.resumo || '';
        details.category.textContent = capa.dataset.categoria || 'Soulslike';
        details.price.textContent = capa.dataset.preco || '';
        definirImagem(details.image, capa.dataset.imagem || '', capa.dataset.titulo || 'jogo', details.cover);

        const classeFlash = 'capa-jogo--flash';
        capa.classList.remove(classeFlash);
        void capa.offsetWidth;
        capa.classList.add(classeFlash);

        window.setTimeout(() => {
            if (!dialog.open) {
                dialog.showModal();
            }
        }, reduzirMovimento ? 0 : 220);
    };

    const selecionarMundo = (indice, abrir = false) => {
        if (!cards.length) return;
        const normalizado = (indice + cards.length) % cards.length;
        const capa = cards[normalizado];
        atualizarMundo(capa, normalizado);
        if (abrir) abrirDetalhe(capa);
    };

    cards.forEach((capa, indice) => {
        capa.addEventListener('click', () => selecionarMundo(indice, true));
    });

    const tituloInicial = cards.findIndex((capa) => capa.dataset.titulo === 'ELDEN RING');
    selecionarMundo(tituloInicial >= 0 ? tituloInicial : 0);

    document.querySelector('[data-feature-prev]')?.addEventListener('click', () => selecionarMundo(jogoSelecionado - 1));
    document.querySelector('[data-feature-next]')?.addEventListener('click', () => selecionarMundo(jogoSelecionado + 1));
    document.querySelector('[data-feature-details]')?.addEventListener('click', () => abrirDetalhe(cards[jogoSelecionado]));
    document.querySelectorAll('[data-feature-dot]').forEach((ponto) => {
        ponto.addEventListener('click', () => selecionarMundo(Number(ponto.dataset.featureDot)));
    });

    document.querySelectorAll('[data-collection-filter]').forEach((botao) => {
        botao.addEventListener('click', () => {
            filtroColecao = botao.dataset.collectionFilter || 'all';
            atualizarFiltros();
        });
    });
    document.querySelector('[data-live-search]')?.addEventListener('input', atualizarFiltros);
    document.querySelector('[data-search-form]')?.addEventListener('submit', (evento) => evento.preventDefault());

    document.querySelectorAll('[data-favorite]').forEach((botao) => {
        botao.addEventListener('click', (evento) => {
            evento.stopPropagation();
            const id = String(botao.dataset.favorite);
            if (favoritos.has(id)) {
                favoritos.delete(id);
                mostrarAviso('Jogo removido da sua lista de desejos.');
            } else {
                favoritos.add(id);
                mostrarAviso('Jogo guardado na sua lista de desejos.');
            }
            salvarFavoritos();
            atualizarFiltros();
        });
    });

    if (feature.visual && !reduzirMovimento) {
        feature.visual.addEventListener('pointermove', (evento) => {
            const retangulo = feature.visual.getBoundingClientRect();
            const x = (evento.clientX - retangulo.left) / retangulo.width - 0.5;
            const y = (evento.clientY - retangulo.top) / retangulo.height - 0.5;
            feature.visual.style.setProperty('--pointer-x', `${(-x * 12).toFixed(2)}px`);
            feature.visual.style.setProperty('--pointer-y', `${(-y * 12).toFixed(2)}px`);
            feature.visual.style.setProperty('--float-y', `${(y * 4).toFixed(2)}px`);
        });
        feature.visual.addEventListener('pointerleave', () => {
            feature.visual.style.setProperty('--pointer-x', '0px');
            feature.visual.style.setProperty('--pointer-y', '0px');
            feature.visual.style.setProperty('--float-y', '0px');
        });
    }

    document.querySelectorAll('[data-game-item]').forEach((item) => {
        item.addEventListener('pointermove', (evento) => {
            const retangulo = item.getBoundingClientRect();
            item.style.setProperty('--spot-x', `${((evento.clientX - retangulo.left) / retangulo.width * 100).toFixed(1)}%`);
            item.style.setProperty('--spot-y', `${((evento.clientY - retangulo.top) / retangulo.height * 100).toFixed(1)}%`);
        });
    });

    if (dialog) {
        dialog.querySelectorAll('[data-fechar-detalhe]').forEach((botao) => botao.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (evento) => {
            if (evento.target === dialog) dialog.close();
        });
        dialog.addEventListener('cancel', () => body.classList.remove('world-changing'));
    }

    window.addEventListener('keydown', (evento) => {
        if (dialog?.open || evento.altKey || evento.ctrlKey || evento.metaKey) return;
        if (evento.target instanceof HTMLElement && ['INPUT', 'TEXTAREA'].includes(evento.target.tagName)) return;
        if (evento.key === 'ArrowLeft') selecionarMundo(jogoSelecionado - 1);
        if (evento.key === 'ArrowRight') selecionarMundo(jogoSelecionado + 1);
    });

    window.addEventListener('storage', (evento) => {
        if (evento.key !== favoriteKey) return;
        try {
            favoritos = new Set(JSON.parse(evento.newValue || '[]').map(String));
        } catch (erro) {
            favoritos = new Set();
        }
        atualizarFavoritos();
        atualizarFiltros();
    });

    atualizarFavoritos();
    atualizarFiltros();
})();
