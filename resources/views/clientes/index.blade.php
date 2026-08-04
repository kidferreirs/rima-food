<x-rimafood.layout>

    <div class="p-4 sm:p-6 lg:p-8">

        {{-- Cabeçalho --}}
        <div class="mb-6 flex items-start justify-between gap-4 sm:mb-8">

            <div class="min-w-0">

                <h1 class="text-3xl font-bold sm:text-4xl">
                    Clientes
                </h1>

                <p class="mt-2 text-sm text-gray-500 sm:text-base">
                    Acompanhe seus clientes e o histórico de compras.
                </p>

                <p class="mt-1 truncate text-xs text-gray-400 sm:text-sm">
                    {{ $restauranteAtual->nome }}
                </p>

            </div>

            <a
                href="{{ route(
                    'restaurante.clientes.create',
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
                + Cliente
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

        @if(session('error'))
            <div
                class="
                    mb-6 rounded-xl border border-red-200
                    bg-red-50 p-4 text-sm text-red-800
                "
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Resumo --}}
        @php
            $clientesAtivos = $clientes->where('ativo', true)->count();
            $totalPedidosClientes = (int) $clientes->sum('pedidos_count');
            $totalVendasClientes = (float) $clientes->sum(
                fn($cliente) => (float) ($cliente->pedidos_sum_total ?? 0)
            );
        @endphp

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Clientes ativos
                </p>

                <p class="mt-1 text-3xl font-bold text-gray-900">
                    {{ $clientesAtivos }}
                </p>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Pedidos registrados
                </p>

                <p class="mt-1 text-3xl font-bold text-gray-900">
                    {{ $totalPedidosClientes }}
                </p>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    Total em compras
                </p>

                <p class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">
                    R$ {{ number_format(
                        $totalVendasClientes,
                        2,
                        ',',
                        '.'
                    ) }}
                </p>
            </div>

        </div>

        {{-- Busca --}}
        <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm sm:p-5">

            <label
                for="buscaCliente"
                class="mb-2 block text-sm font-semibold text-gray-700"
            >
                Buscar cliente
            </label>

            <input
                type="search"
                id="buscaCliente"
                placeholder="Nome, telefone, e-mail ou status..."
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
        <div id="cardsClientes" class="space-y-4 md:hidden">

            @forelse($clientes as $cliente)

                @php
                    $totalGasto = (float) (
                        $cliente->pedidos_sum_total
                        ?? $cliente->total_gasto
                        ?? 0
                    );

                    $ultimaCompra = $cliente->pedidos_max_created_at
                        ? \Carbon\Carbon::parse(
                            $cliente->pedidos_max_created_at
                        )
                        : null;
                @endphp

                <article
                    class="
                        cliente-card rounded-2xl
                        border border-gray-100
                        bg-white p-4 shadow-sm
                    "
                >

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <h2 class="truncate text-lg font-bold text-gray-900">
                                {{ $cliente->nome ?? 'Sem nome' }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $cliente->telefone }}
                            </p>

                            @if($cliente->email)
                                <p class="mt-1 truncate text-sm text-gray-500">
                                    {{ $cliente->email }}
                                </p>
                            @endif

                        </div>

                        @if($cliente->ativo)
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

                    <div class="mt-4 grid grid-cols-2 gap-3">

                        <div class="rounded-xl bg-gray-50 p-3">

                            <p class="text-xs text-gray-500">
                                Pedidos
                            </p>

                            <p class="mt-1 text-lg font-bold text-gray-900">
                                {{ $cliente->pedidos_count }}
                            </p>

                        </div>

                        <div class="rounded-xl bg-gray-50 p-3">

                            <p class="text-xs text-gray-500">
                                Total gasto
                            </p>

                            <p class="mt-1 font-bold text-gray-900">
                                R$ {{ number_format(
                                    $totalGasto,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </p>

                        </div>

                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-4">

                        <p class="text-xs text-gray-500">
                            Última compra
                        </p>

                        <p class="mt-1 text-sm font-semibold text-gray-800">
                            {{ $ultimaCompra
                                ? $ultimaCompra->format('d/m/Y H:i')
                                : 'Nenhuma compra registrada' }}
                        </p>

                    </div>

                    @if($cliente->observacao)
                        <div
                            class="
                                mt-4 rounded-xl border border-gray-100
                                bg-gray-50 p-3
                            "
                        >
                            <p class="text-xs text-gray-500">
                                Observação
                            </p>

                            <p class="mt-1 text-sm text-gray-700">
                                {{ $cliente->observacao }}
                            </p>
                        </div>
                    @endif

                    <div
                        class="
                            mt-4 grid grid-cols-2 gap-3
                            border-t border-gray-100 pt-4
                        "
                    >

                        <a
                            href="{{ route(
                                'restaurante.clientes.edit',
                                [
                                    $restauranteAtual->slug,
                                    $cliente
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
                                'restaurante.clientes.status',
                                [
                                    $restauranteAtual->slug,
                                    $cliente
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

                                    @if($cliente->ativo)
                                        bg-red-50 text-red-700
                                        hover:bg-red-100
                                    @else
                                        bg-green-50 text-green-700
                                        hover:bg-green-100
                                    @endif
                                "
                            >
                                {{ $cliente->ativo
                                    ? 'Desativar'
                                    : 'Ativar' }}
                            </button>

                        </form>

                    </div>

                </article>

            @empty

                <div class="rounded-2xl bg-white p-8 text-center shadow-sm">

                    <p class="font-semibold text-gray-700">
                        Nenhum cliente cadastrado.
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        Os clientes aparecerão aqui após o cadastro ou pedido.
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

                <table class="w-full min-w-[1100px]">

                    <thead>

                        <tr class="border-b bg-gray-50">

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Cliente
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Contato
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Pedidos
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Total gasto
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Última compra
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-gray-600">
                                Status
                            </th>

                            <th class="p-4 text-right text-sm font-semibold text-gray-600">
                                Ações
                            </th>

                        </tr>

                    </thead>

                    <tbody id="tabelaClientes">

                        @forelse($clientes as $cliente)

                            @php
                                $totalGasto = (float) (
                                    $cliente->pedidos_sum_total
                                    ?? $cliente->total_gasto
                                    ?? 0
                                );

                                $ultimaCompra = $cliente->pedidos_max_created_at
                                    ? \Carbon\Carbon::parse(
                                        $cliente->pedidos_max_created_at
                                    )
                                    : null;
                            @endphp

                            <tr class="cliente-linha border-b last:border-b-0 hover:bg-gray-50">

                                <td class="p-4">

                                    <p class="font-semibold text-gray-900">
                                        {{ $cliente->nome ?? 'Sem nome' }}
                                    </p>

                                    @if($cliente->observacao)
                                        <p class="mt-1 max-w-xs truncate text-xs text-gray-400">
                                            {{ $cliente->observacao }}
                                        </p>
                                    @endif

                                </td>

                                <td class="p-4">

                                    <p class="text-sm text-gray-800">
                                        {{ $cliente->telefone }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $cliente->email ?? 'Sem e-mail' }}
                                    </p>

                                </td>

                                <td class="p-4 font-semibold text-gray-900">
                                    {{ $cliente->pedidos_count }}
                                </td>

                                <td class="p-4 font-semibold text-gray-900">
                                    R$ {{ number_format(
                                        $totalGasto,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td class="p-4 text-sm text-gray-600">
                                    {{ $ultimaCompra
                                        ? $ultimaCompra->format('d/m/Y H:i')
                                        : 'Nenhuma' }}
                                </td>

                                <td class="p-4">

                                    @if($cliente->ativo)
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
                                                'restaurante.clientes.edit',
                                                [
                                                    $restauranteAtual->slug,
                                                    $cliente
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
                                                'restaurante.clientes.status',
                                                [
                                                    $restauranteAtual->slug,
                                                    $cliente
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

                                                    @if($cliente->ativo)
                                                        bg-red-50 text-red-700
                                                        hover:bg-red-100
                                                    @else
                                                        bg-green-50 text-green-700
                                                        hover:bg-green-100
                                                    @endif
                                                "
                                            >
                                                {{ $cliente->ativo
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
                                    colspan="7"
                                    class="p-8 text-center text-gray-500"
                                >
                                    Nenhum cliente cadastrado neste restaurante.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div id="semResultadosDesktop" class="hidden p-8 text-center text-gray-500">
                Nenhum cliente encontrado.
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
            Nenhum cliente encontrado.
        </div>

    </div>

    <script>
        const buscaCliente = document.getElementById('buscaCliente');
        const linhas = document.querySelectorAll('.cliente-linha');
        const cards = document.querySelectorAll('.cliente-card');
        const semResultadosDesktop = document.getElementById('semResultadosDesktop');
        const semResultadosMobile = document.getElementById('semResultadosMobile');

        buscaCliente?.addEventListener('input', function () {
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