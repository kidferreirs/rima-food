<x-rimafood.layout>

    <div class="p-4 sm:p-6 lg:p-8">

        {{-- Cabeçalho --}}
        <div class="mb-6 flex items-start justify-between gap-4 sm:mb-8">

            <div class="min-w-0">

                <h1 class="text-3xl font-bold sm:text-4xl">
                    Produtos
                </h1>

                <p class="mt-2 text-sm text-gray-500 sm:text-base">
                    Gerencie os produtos do cardápio.
                </p>

                <p class="mt-1 truncate text-xs text-gray-400 sm:text-sm">
                    {{ $restauranteAtual->nome }}
                </p>

            </div>

            <a
                href="{{ route(
                    'restaurante.produtos.create',
                    $restauranteAtual->slug
                ) }}"
                class="
                    inline-flex shrink-0 items-center justify-center
                    whitespace-nowrap rounded-xl
                    bg-green-500 px-4 py-2
                    text-sm font-semibold text-white
                    shadow-sm transition
                    hover:bg-green-600
                "
            >
                + Produto
            </a>

        </div>

        @if(session('success'))
            <div
                class="
                    mb-6 rounded-xl border border-green-200
                    bg-green-50 p-4 text-sm text-green-800
                "
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- Busca --}}
        <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm sm:p-5">

            <label
                for="buscaProduto"
                class="mb-2 block text-sm font-semibold text-gray-700"
            >
                Buscar produto
            </label>

            <input
                type="search"
                id="buscaProduto"
                placeholder="Nome, categoria, preço ou status..."
                class="
                    w-full rounded-xl border border-gray-200
                    bg-white px-4 py-3 text-sm
                    outline-none transition
                    focus:border-green-500
                    focus:ring-2 focus:ring-green-100
                "
            >

        </div>

        {{-- Cards mobile --}}
        <div id="cardsProdutos" class="space-y-4 md:hidden">

            @forelse($produtos as $produto)

                <article
                    class="
                        produto-card overflow-hidden rounded-2xl
                        border border-gray-100 bg-white shadow-sm
                    "
                >

                    <div class="flex gap-4 p-4">

                        <div
                            class="
                                h-24 w-24 shrink-0 overflow-hidden
                                rounded-2xl bg-gray-100
                            "
                        >
                            @if($produto->imagem)
                                <img
                                    src="{{ asset('storage/' . $produto->imagem) }}"
                                    alt="{{ $produto->nome }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div
                                    class="
                                        flex h-full w-full items-center
                                        justify-center text-xs font-medium
                                        text-gray-400
                                    "
                                >
                                    Sem imagem
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">

                            <div class="flex items-start justify-between gap-3">

                                <div class="min-w-0">

                                    <h2 class="truncate text-lg font-bold text-gray-900">
                                        {{ $produto->nome }}
                                    </h2>

                                    <p class="mt-1 truncate text-sm text-gray-500">
                                        {{ $produto->categoria->nome }}
                                    </p>

                                </div>

                                @if($produto->ativo)
                                    <span
                                        class="
                                            shrink-0 rounded-full
                                            bg-green-100 px-3 py-1
                                            text-xs font-semibold text-green-700
                                        "
                                    >
                                        Ativo
                                    </span>
                                @else
                                    <span
                                        class="
                                            shrink-0 rounded-full
                                            bg-gray-100 px-3 py-1
                                            text-xs font-semibold text-gray-600
                                        "
                                    >
                                        Inativo
                                    </span>
                                @endif

                            </div>

                            <p class="mt-3 text-xl font-bold text-gray-900">
                                R$ {{ number_format($produto->preco, 2, ',', '.') }}
                            </p>

                            <p class="mt-2 text-xs text-gray-500">
                                {{ $produto->grupos_opcoes_count }}
                                {{ $produto->grupos_opcoes_count === 1
                                    ? 'grupo de opções'
                                    : 'grupos de opções' }}

                                @if($produto->grupos_opcoes_ativos_count > 0)
                                    · {{ $produto->grupos_opcoes_ativos_count }} ativos
                                @endif
                            </p>

                        </div>

                    </div>

                    @if($produto->descricao)
                        <div class="border-t border-gray-100 px-4 py-3">
                            <p class="line-clamp-2 text-sm text-gray-600">
                                {{ $produto->descricao }}
                            </p>
                        </div>
                    @endif

                    <div
                        class="
                            grid grid-cols-2 gap-3
                            border-t border-gray-100 p-4
                        "
                    >

                        <a
                            href="{{ route(
                                'restaurante.produtos.edit',
                                [
                                    $restauranteAtual->slug,
                                    $produto
                                ]
                            ) }}"
                            class="
                                inline-flex items-center justify-center
                                rounded-xl border border-gray-200
                                px-4 py-2.5
                                text-sm font-semibold text-gray-700
                                transition hover:bg-gray-50
                            "
                        >
                            Editar
                        </a>

                        <form
                            action="{{ route(
                                'restaurante.produtos.status',
                                [
                                    $restauranteAtual->slug,
                                    $produto
                                ]
                            ) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="
                                    inline-flex w-full items-center justify-center
                                    rounded-xl px-4 py-2.5
                                    text-sm font-semibold transition

                                    @if($produto->ativo)
                                        bg-red-50 text-red-700
                                        hover:bg-red-100
                                    @else
                                        bg-green-50 text-green-700
                                        hover:bg-green-100
                                    @endif
                                "
                            >
                                {{ $produto->ativo
                                    ? 'Desativar'
                                    : 'Ativar' }}
                            </button>

                        </form>

                    </div>

                </article>

            @empty

                <div class="rounded-2xl bg-white p-8 text-center shadow-sm">

                    <p class="font-semibold text-gray-700">
                        Nenhum produto cadastrado.
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        Crie o primeiro produto do seu cardápio.
                    </p>

                </div>

            @endforelse

        </div>

        {{-- Tabela desktop --}}
        <div
            class="
                hidden overflow-hidden rounded-2xl
                bg-white shadow md:block
            "
        >

            <div class="overflow-x-auto">

                <table class="w-full min-w-[980px]">

                    <thead>

                        <tr class="border-b bg-gray-50">

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Produto
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Categoria
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Preço
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Opções
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Status
                            </th>

                            <th class="p-4 text-right text-sm font-semibold text-gray-600">
                                Ações
                            </th>

                        </tr>

                    </thead>

                    <tbody id="tabelaProdutos">

                        @forelse($produtos as $produto)

                            <tr class="produto-linha border-b last:border-b-0 hover:bg-gray-50">

                                <td class="p-4">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="
                                                h-14 w-14 shrink-0 overflow-hidden
                                                rounded-xl bg-gray-100
                                            "
                                        >
                                            @if($produto->imagem)
                                                <img
                                                    src="{{ asset('storage/' . $produto->imagem) }}"
                                                    alt="{{ $produto->nome }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div
                                                    class="
                                                        flex h-full w-full items-center
                                                        justify-center text-[10px]
                                                        font-medium text-gray-400
                                                    "
                                                >
                                                    Sem imagem
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">

                                            <p class="font-semibold text-gray-900">
                                                {{ $produto->nome }}
                                            </p>

                                            @if($produto->descricao)
                                                <p class="mt-1 max-w-md truncate text-sm text-gray-500">
                                                    {{ $produto->descricao }}
                                                </p>
                                            @endif

                                        </div>

                                    </div>

                                </td>

                                <td class="p-4 text-gray-700">
                                    {{ $produto->categoria->nome }}
                                </td>

                                <td class="p-4 font-semibold text-gray-900">
                                    R$ {{ number_format($produto->preco, 2, ',', '.') }}
                                </td>

                                <td class="p-4 text-gray-600">
                                    {{ $produto->grupos_opcoes_count }}
                                </td>

                                <td class="p-4">

                                    @if($produto->ativo)
                                        <span
                                            class="
                                                inline-flex rounded-full
                                                bg-green-100 px-3 py-1
                                                text-xs font-semibold text-green-700
                                            "
                                        >
                                            Ativo
                                        </span>
                                    @else
                                        <span
                                            class="
                                                inline-flex rounded-full
                                                bg-gray-100 px-3 py-1
                                                text-xs font-semibold text-gray-600
                                            "
                                        >
                                            Inativo
                                        </span>
                                    @endif

                                </td>

                                <td class="p-4">

                                    <div class="flex justify-end gap-2">

                                        <a
                                            href="{{ route(
                                                'restaurante.produtos.edit',
                                                [
                                                    $restauranteAtual->slug,
                                                    $produto
                                                ]
                                            ) }}"
                                            class="
                                                rounded-lg border border-gray-200
                                                px-3 py-2
                                                text-sm font-semibold text-gray-700
                                                transition hover:bg-gray-50
                                            "
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="{{ route(
                                                'restaurante.produtos.status',
                                                [
                                                    $restauranteAtual->slug,
                                                    $produto
                                                ]
                                            ) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="
                                                    rounded-lg px-3 py-2
                                                    text-sm font-semibold transition

                                                    @if($produto->ativo)
                                                        bg-red-50 text-red-700
                                                        hover:bg-red-100
                                                    @else
                                                        bg-green-50 text-green-700
                                                        hover:bg-green-100
                                                    @endif
                                                "
                                            >
                                                {{ $produto->ativo
                                                    ? 'Desativar'
                                                    : 'Ativar' }}
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="p-8 text-center text-gray-500"
                                >
                                    Nenhum produto cadastrado neste restaurante.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div id="semResultadosDesktop" class="hidden p-8 text-center text-gray-500">
                Nenhum produto encontrado.
            </div>

        </div>

        <div
            id="semResultadosMobile"
            class="
                hidden rounded-2xl bg-white
                p-8 text-center text-gray-500
                shadow-sm md:hidden
            "
        >
            Nenhum produto encontrado.
        </div>

    </div>

    <script>
        const buscaProduto = document.getElementById('buscaProduto');
        const linhas = document.querySelectorAll('.produto-linha');
        const cards = document.querySelectorAll('.produto-card');
        const semResultadosDesktop = document.getElementById('semResultadosDesktop');
        const semResultadosMobile = document.getElementById('semResultadosMobile');

        buscaProduto?.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();

            let encontradosDesktop = 0;
            let encontradosMobile = 0;

            linhas.forEach(function (linha) {
                const texto = linha.innerText.toLowerCase();
                const exibir = texto.includes(termo);

                linha.style.display = exibir ? '' : 'none';

                if (exibir) {
                    encontradosDesktop++;
                }
            });

            cards.forEach(function (card) {
                const texto = card.innerText.toLowerCase();
                const exibir = texto.includes(termo);

                card.style.display = exibir ? '' : 'none';

                if (exibir) {
                    encontradosMobile++;
                }
            });

            semResultadosDesktop?.classList.toggle(
                'hidden',
                encontradosDesktop > 0
            );

            semResultadosMobile?.classList.toggle(
                'hidden',
                encontradosMobile > 0
            );
        });
    </script>

</x-rimafood.layout>