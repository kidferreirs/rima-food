function iniciarFavoritos() {
    const main = document.querySelector('main[data-restaurante-slug]');

    if (!main) {
        return;
    }

    const slug = main.dataset.restauranteSlug;
    const chave = `rima-favoritos-${slug}`;

    let favoritos = JSON.parse(
        localStorage.getItem(chave)
    ) || [];

    const secao = document.getElementById('favoritos-section');
    const lista = document.getElementById('favoritos-lista');

    const toast = document.getElementById('toast');

    const contador = document.getElementById(
        'favoritos-contador'
    );

    function salvar() {
        localStorage.setItem(
            chave,
            JSON.stringify(favoritos)
        );
    }

    function mostrarToast(texto) {

        if (!toast) return;

        toast.textContent = texto;

        toast.classList.remove(
            'hidden',
            'opacity-0'
        );

        toast.classList.add(
            'opacity-100'
        );

        clearTimeout(
            toast.dataset.timer
        );

        toast.dataset.timer = setTimeout(() => {

            toast.classList.add(
                'opacity-0'
            );

            setTimeout(() => {
                toast.classList.add(
                    'hidden'
                );
            }, 250);

        }, 1500);
    }

        function atualizarBotoes() {
            document
                .querySelectorAll('.toggle-favorito')
                .forEach(botao => {
                    const id = botao.dataset.produtoId;

                    botao.textContent = favoritos.includes(id)
                        ? '❤️'
                        : '🤍';
                });
        }

        function atualizarLista() {
            if (!secao || !lista) {
                return;
            }

            lista.innerHTML = '';

            favoritos.forEach(id => {
                const card = document.querySelector(
                    `.produto-card[data-produto-id="${id}"]`
                );

                if (!card) {
                    return;
                }

                const nome = card.dataset.nome;
                const preco = Number(
                    card.dataset.produtoPreco
                );

                const item = document.createElement('button');

                item.type = 'button';
                item.className =
                    'w-full flex justify-between items-center bg-red-50 border border-red-100 rounded-2xl p-4 text-left';

                item.innerHTML = `
                <span>
                    <strong class="block text-slate-800">
                        ❤️ ${nome}
                    </strong>

                    <small class="text-slate-500">
                        Toque para localizar no cardápio
                    </small>
                </span>

                <strong class="text-green-600">
                    R$ ${preco.toFixed(2).replace('.', ',')}
                </strong>
            `;

                item.addEventListener('click', () => {
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                });

                lista.appendChild(item);
            });

            secao.classList.toggle(
                'hidden',
                favoritos.length === 0
            );

            if (contador) {
                contador.textContent =
                    `(${favoritos.length})`;
            }
        }

        document.addEventListener('click', event => {
            const botao = event.target.closest(
                '.toggle-favorito'
            );

            if (!botao) {
                return;
            }

            const id = botao.dataset.produtoId;
            const coracao = botao;

            if (favoritos.includes(id)) {

                favoritos = favoritos.filter(
                    favoritoId => favoritoId !== id
                );

                mostrarToast(
                    '🤍 Removido dos favoritos'
                );

            } else {

                favoritos.push(id);

                mostrarToast(
                    '❤️ Adicionado aos favoritos'
                );
            }

            coracao.animate(
                [
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.25)' },
                    { transform: 'scale(1)' }
                ],
                {
                    duration: 250
                }
            );

            salvar();
            atualizarBotoes();
            atualizarLista();
        });

        atualizarBotoes();
        atualizarLista();
    }

    document.addEventListener(
        'DOMContentLoaded',
        iniciarFavoritos
    );