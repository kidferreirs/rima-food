function iniciarMenuCategorias() {
    const abrir = document.getElementById('abrir-menu-categorias');
    const fechar = document.getElementById('fechar-menu-categorias');
    const fundo = document.getElementById('fundo-menu-categorias');

    const overlay = document.getElementById('menu-categorias-overlay');
    const painel = document.getElementById('menu-categorias-painel');

    if (!abrir || !overlay || !painel) {
        return;
    }

    function abrirMenu() {
        overlay.classList.remove('hidden');
        overlay.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            painel.classList.remove('-translate-x-full');
        });
    }

    function fecharMenu() {
        painel.classList.add('-translate-x-full');
        overlay.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('overflow-hidden');

        window.setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }

    abrir.addEventListener('click', abrirMenu);
    fechar?.addEventListener('click', fecharMenu);
    fundo?.addEventListener('click', fecharMenu);

    document
        .querySelectorAll('.categoria-menu-link')
        .forEach(botao => {
            botao.addEventListener('click', () => {
                const id = botao.dataset.categoriaAlvo;
                const categoria = document.getElementById(id);

                fecharMenu();

                window.setTimeout(() => {
                    categoria?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }, 320);
            });
        });

    document.addEventListener('keydown', event => {
        if (
            event.key === 'Escape' &&
            !overlay.classList.contains('hidden')
        ) {
            fecharMenu();
        }
    });
}

document.addEventListener(
    'DOMContentLoaded',
    iniciarMenuCategorias
);