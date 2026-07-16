function iniciarChat() {
    const main = document.querySelector(
        'main[data-garcom-url]'
    );

    const input = document.getElementById('search');
    const mensagens = document.getElementById('chat-mensagens');
    const container = document.getElementById('garcom-container');
    const fecharChat = document.getElementById(
        'fechar-chat-garcom'
    );

    if (!main || !input || !mensagens || !container) {
        return;
    }

    const url = main.dataset.garcomUrl;
    const garcomLiberado =
        main.dataset.garcomLiberado === '1';

    const atalhos = container.querySelectorAll(
        '.garcom-pergunta'
    );

    if (!garcomLiberado) {
        input.placeholder = 'Pesquise no cardápio...';

        atalhos.forEach(botao => {
            botao.classList.add('hidden');
        });
        container.classList.add('hidden');
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    let historico = [];
    let enviando = false;

    function mostrarGarcom() {
        container.classList.remove('hidden');

        requestAnimationFrame(() => {
            container.classList.remove(
                'opacity-0',
                'translate-y-2'
            );
        });
    }

    function esconderGarcom() {
        container.classList.add(
            'opacity-0',
            'translate-y-2'
        );

        window.setTimeout(() => {
            container.classList.add('hidden');
        }, 300);
    }

    function avisarModoChat(ativo) {
        document.dispatchEvent(
            new CustomEvent('rima:modo-chat', {
                detail: { ativo }
            })
        );
    }

    function adicionarAoHistorico(autor, mensagem) {
        historico.push({
            autor,
            mensagem
        });

        historico = historico.slice(-8);
    }

    function criarMensagem(autor, texto, opcoes = {}) {
        const linha = document.createElement('div');

        linha.className = autor === 'cliente'
            ? 'flex justify-end'
            : 'flex';

        const balao = document.createElement('div');

        balao.className = autor === 'cliente'
            ? 'bg-green-600 text-white rounded-2xl px-4 py-3 shadow-sm max-w-[90%]'
            : 'bg-white text-slate-800 rounded-2xl px-4 py-3 shadow-sm max-w-[90%]';

        if (opcoes.erro) {
            balao.className =
                'bg-red-50 border border-red-100 text-red-800 rounded-2xl px-4 py-3 shadow-sm max-w-[90%]';
        }

        const titulo = document.createElement('p');

        titulo.className = 'font-bold mb-1';
        titulo.textContent = autor === 'cliente'
            ? '👤 Você'
            : '🤖 Garçom';

        const mensagem = document.createElement('p');

        mensagem.className =
            'text-sm whitespace-pre-line break-words';

        mensagem.textContent = texto;

        balao.appendChild(titulo);
        balao.appendChild(mensagem);
        linha.appendChild(balao);
        mensagens.appendChild(linha);

        mensagens.scrollTop = mensagens.scrollHeight;

        return {
            linha,
            balao,
            mensagem
        };
    }

    function removerDestaquesAnteriores() {
        document
            .querySelectorAll('.produto-card')
            .forEach(card => {
                card.classList.remove(
                    'ring-4',
                    'ring-green-400',
                    'ring-offset-2'
                );
            });
    }

    function destacarProdutos(produtoIds = []) {
        removerDestaquesAnteriores();

        if (!Array.isArray(produtoIds) || produtoIds.length === 0) {
            return;
        }

        let primeiroCard = null;

        produtoIds.forEach(id => {
            const card = document.querySelector(
                `.produto-card[data-produto-id="${id}"]`
            );

            if (!card) {
                return;
            }

            card.classList.add(
                'ring-4',
                'ring-green-400',
                'ring-offset-2'
            );

            card.animate(
                [
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.03)' },
                    { transform: 'scale(1)' }
                ],
                {
                    duration: 500,
                    easing: 'ease-out'
                }
            );

            primeiroCard ??= card;
        });

        if (primeiroCard) {
            window.setTimeout(() => {
                primeiroCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 350);
        }
    }

    async function consultarGarcom(pergunta) {
        const historicoAnterior = historico.slice(-8);

        adicionarAoHistorico('cliente', pergunta);

        const pensando = criarMensagem(
            'garcom',
            '⏳ Estou consultando o cardápio...'
        );

        try {
            const resposta = await fetch(url, {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                credentials: 'same-origin',

                body: JSON.stringify({
                    pergunta,
                    historico: historicoAnterior
                })
            });

            let dados = {};

            try {
                dados = await resposta.json();
            } catch {
                throw new Error(
                    'O servidor retornou uma resposta inválida.'
                );
            }

            pensando.linha.remove();

            if (!resposta.ok) {
                if (
                    resposta.status === 403 &&
                    dados.codigo === 'upgrade_required'
                ) {
                    criarMensagem(
                        'garcom',
                        dados.mensagem,
                        { erro: true }
                    );

                    return;
                }

                if (resposta.status === 422) {
                    criarMensagem(
                        'garcom',
                        'Não consegui entender essa pergunta. Tente escrevê-la de outra forma.',
                        { erro: true }
                    );

                    return;
                }

                throw new Error(
                    dados.mensagem ??
                    'Não foi possível consultar o Garçom.'
                );
            }

            const textoResposta =
                dados.mensagem ??
                'Não encontrei uma resposta no cardápio.';

            criarMensagem(
                'garcom',
                textoResposta
            );

            adicionarAoHistorico(
                'garcom',
                textoResposta
            );

            destacarProdutos(
                dados.produto_ids ?? []
            );
        } catch (erro) {
            pensando.linha.remove();

            console.error(
                'Erro no Garçom Inteligente:',
                erro
            );

            criarMensagem(
                'garcom',
                'Não consegui responder agora. Tente novamente em alguns instantes.',
                { erro: true }
            );
        }
    }

    async function enviarPergunta(pergunta) {
        const texto = pergunta.trim();

        if (!texto || enviando) {
            return;
        }

        avisarModoChat(true);
        mostrarGarcom();

        criarMensagem(
            'cliente',
            texto
        );

        input.value = '';
        input.disabled = true;
        enviando = true;

        try {
            if (!garcomLiberado) {
                criarMensagem(
                    'garcom',
                    'O Garçom Inteligente está disponível nos planos Rima Menu + IA e Rima Food.',
                    { erro: true }
                );

                return;
            }

            await consultarGarcom(texto);
        } finally {
            enviando = false;
            input.disabled = false;
            input.focus();
        }
    }

    input.addEventListener('keydown', event => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        enviarPergunta(input.value);
    });

    document
        .querySelectorAll('.garcom-pergunta')
        .forEach(botao => {
            botao.addEventListener('click', () => {
                enviarPergunta(
                    botao.dataset.pergunta ?? ''
                );
            });
        });

    fecharChat?.addEventListener('click', () => {
        esconderGarcom();

        input.value = '';

        avisarModoChat(false);
        removerDestaquesAnteriores();

        input.focus();
    });
}

document.addEventListener(
    'DOMContentLoaded',
    iniciarChat
);