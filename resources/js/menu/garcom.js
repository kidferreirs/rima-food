function normalizarTexto(texto = '') {
    return texto
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function interpretarIntencao(texto) {
    const consulta = normalizarTexto(texto);

    const intencoes = {
        bebida: {
            rotulo: 'bebidas',
            expressoes: [
                'quero beber',
                'algo para beber',
                'bebida',
                'refrigerante',
                'suco',
                'agua',
                'coca',
            ],
        },

        sobremesa: {
            rotulo: 'opções doces',
            expressoes: [
                'algo doce',
                'doce',
                'sobremesa',
                'chocolate',
                'bolo',
                'brownie',
                'sorvete',
                'acai',
            ],
        },

        lanche: {
            rotulo: 'lanches',
            expressoes: [
                'quero um lanche',
                'lanche',
                'hamburguer',
                'hamburger',
                'burger',
                'sanduiche',
            ],
        },

        pizza: {
            rotulo: 'pizzas',
            expressoes: [
                'quero pizza',
                'quero uma pizza',
                'pizza',
                'calabresa',
                'mussarela',
                'portuguesa',
            ],
        },

        leve: {
            rotulo: 'opções leves',
            expressoes: [
                'algo leve',
                'comida leve',
                'salada',
                'natural',
                'fit',
            ],
        },
    };

    for (const [termo, configuracao] of Object.entries(intencoes)) {
        const encontrou = configuracao.expressoes.some(
            expressao => consulta.includes(expressao)
        );

        if (encontrou) {
            return {
                termo,
                rotulo: configuracao.rotulo,
            };
        }
    }

    return {
        termo: consulta,
        rotulo: 'produtos',
    };
}

function iniciarGarcomInteligente() {
    const searchInput = document.getElementById('search');

    if (!searchInput) {
        return;
    }

    const cards = Array.from(
        document.querySelectorAll('.produto-card')
    );

    const categorias = Array.from(
        document.querySelectorAll('.categoria-section')
    );

    const mensagemVazia = document.getElementById(
        'nenhum-produto-encontrado'
    );

    const respostaGarcom = document.getElementById(
        'resposta-garcom'
    );

    const textoResposta = document.getElementById(
        'texto-resposta-garcom'
    );

    const navegacaoCategorias = document.getElementById(
        'navegacao-categorias'
    );

    const destaques = document.getElementById(
        'destaques-section'
    );

    const maisVendidos = document.getElementById(
        'mais-vendidos-section'
    );

    const favoritos = document.getElementById(
        'favoritos-section'
    );

    searchInput.addEventListener('input', function () {
        const textoOriginal = this.value.trim();
        const pesquisando = textoOriginal !== '';

        const intencao = interpretarIntencao(textoOriginal);
        const consulta = intencao.termo;

        let encontrados = 0;

        cards.forEach(card => {
            const conteudoProduto = normalizarTexto([
                card.dataset.nome,
                card.dataset.descricao,
                card.dataset.palavras,
                card.dataset.sinonimos,
                card.dataset.ingredientes,
                card.dataset.tags,
                card.dataset.categoria,
            ].join(' '));

            const encontrado =
                !pesquisando ||
                conteudoProduto.includes(consulta);

            card.classList.toggle('hidden', !encontrado);

            if (pesquisando && encontrado) {
                encontrados++;
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Esconde categorias sem produtos visíveis
        |--------------------------------------------------------------------------
        */

        categorias.forEach(categoria => {
            const produtosVisiveis = categoria.querySelectorAll(
                '.produto-card:not(.hidden)'
            );

            categoria.classList.toggle(
                'hidden',
                pesquisando && produtosVisiveis.length === 0
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Estado normal ou resultado da busca
        |--------------------------------------------------------------------------
        */

        navegacaoCategorias?.classList.toggle(
            'hidden',
            pesquisando
        );

        destaques?.classList.toggle(
            'hidden',
            pesquisando
        );

        maisVendidos?.classList.toggle(
            'hidden',
            pesquisando
        );

        if (favoritos && !favoritos.dataset.semFavoritos) {
            favoritos.classList.toggle(
                'hidden',
                pesquisando
            );
        }

        respostaGarcom?.classList.toggle(
            'hidden',
            !pesquisando || encontrados === 0
        );

        mensagemVazia?.classList.toggle(
            'hidden',
            !pesquisando || encontrados > 0
        );

        if (pesquisando && encontrados > 0 && textoResposta) {
            const produtoTexto =
                encontrados === 1
                    ? intencao.rotulo.replace(/s$/, '')
                    : intencao.rotulo;

            textoResposta.textContent =
                `Encontrei ${encontrados} ${produtoTexto} para você.`;
        }
    });
}

document.addEventListener(
    'DOMContentLoaded',
    iniciarGarcomInteligente
);