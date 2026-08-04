<x-rimafood.layout>

    <div class="p-4 sm:p-6 lg:p-8">

        {{-- Cabeçalho --}}
        <div class="mb-6 sm:mb-8">

            <h1 class="text-3xl font-bold sm:text-4xl">
                Relatórios
            </h1>

            <p class="mt-2 text-sm text-gray-500 sm:text-base">
                Acompanhe o desempenho do restaurante.
            </p>

            <p class="mt-1 text-xs text-gray-400 sm:text-sm">
                {{ $restaurante->nome }}
            </p>

        </div>

        @if($erroPeriodo)
            <div
                class="
                    mb-6 rounded-xl border border-red-200
                    bg-red-50 p-4 text-sm text-red-800
                "
            >
                {{ $erroPeriodo }}
            </div>
        @endif

        {{-- Filtros --}}
        <form
            method="GET"
            class="mb-8 rounded-2xl bg-white p-4 shadow-sm sm:p-6"
        >

            <div class="mb-5">

                <h2 class="text-xl font-bold text-gray-900">
                    Período
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Escolha um atalho ou informe as datas.
                </p>

            </div>

            <div class="mb-5 grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">

                @php
                    $atalhos = [
                        'hoje' => 'Hoje',
                        'ontem' => 'Ontem',
                        'semana' => 'Esta semana',
                        'mes' => 'Este mês',
                    ];
                @endphp

                @foreach($atalhos as $valor => $label)

                    <a
                        href="{{ route(
                            'restaurante.relatorios.index',
                            [
                                $restauranteAtual->slug,
                                'atalho' => $valor
                            ]
                        ) }}"
                        class="
                            inline-flex items-center justify-center
                            rounded-xl border px-4 py-2.5
                            text-sm font-semibold transition

                            {{ $atalho === $valor
                                ? 'border-green-500 bg-green-50 text-green-700'
                                : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}
                        "
                    >
                        {{ $label }}
                    </a>

                @endforeach

            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div>

                    <label
                        for="data_inicio"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Data inicial
                    </label>

                    <input
                        type="date"
                        id="data_inicio"
                        name="data_inicio"
                        value="{{ $dataInicio }}"
                        class="
                            h-12 w-full rounded-xl
                            border border-gray-200 px-4
                        "
                    >

                </div>

                <div>

                    <label
                        for="data_fim"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Data final
                    </label>

                    <input
                        type="date"
                        id="data_fim"
                        name="data_fim"
                        value="{{ $dataFim }}"
                        class="
                            h-12 w-full rounded-xl
                            border border-gray-200 px-4
                        "
                    >

                </div>

                <button
                    type="submit"
                    class="
                        h-12 self-end rounded-xl bg-green-500
                        px-5 text-sm font-semibold text-white
                        transition hover:bg-green-600
                    "
                >
                    Filtrar
                </button>

                <a
                    href="{{ route(
                        'restaurante.relatorios.index',
                        $restauranteAtual->slug
                    ) }}"
                    class="
                        inline-flex h-12 items-center justify-center
                        self-end rounded-xl bg-gray-900
                        px-5 text-sm font-semibold text-white
                        transition hover:bg-gray-800
                    "
                >
                    Limpar
                </a>

            </div>

            @if($temFiltro)

                <a
                    href="{{ route(
                        'restaurante.relatorios.exportar',
                        array_merge(
                            [$restauranteAtual->slug],
                            request()->query()
                        )
                    ) }}"
                    class="
                        mt-4 inline-flex w-full items-center
                        justify-center rounded-xl
                        border border-blue-200 bg-blue-50
                        px-5 py-3 text-sm font-semibold
                        text-blue-700 transition hover:bg-blue-100
                        sm:w-auto
                    "
                >
                    Exportar CSV
                </a>

            @endif

        </form>

        @if($temFiltro)

            {{-- Financeiro --}}
            <section class="mb-8">

                <div class="mb-4">

                    <h2 class="text-2xl font-bold text-gray-900">
                        Financeiro
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Valores dos pedidos finalizados.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Hoje
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $faturamentoHoje,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Esta semana
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $faturamentoSemanal,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Este mês
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $faturamentoMes,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div
                        class="
                            rounded-2xl border border-green-200
                            bg-green-50 p-5 shadow-sm
                        "
                    >

                        <p class="text-sm text-green-700">
                            Período selecionado
                        </p>

                        <p class="mt-2 text-3xl font-bold text-green-800">
                            R$ {{ number_format(
                                $faturamentoPeriodo,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                </div>

            </section>

            {{-- Operação --}}
            <section class="mb-8">

                <div class="mb-4">

                    <h2 class="text-2xl font-bold text-gray-900">
                        Operação
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Indicadores do período selecionado.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Ticket médio
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $ticketMedio,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Produto campeão
                        </p>

                        <p class="mt-2 text-xl font-bold text-gray-900">
                            {{ $produtoMaisVendido?->produto?->nome
                                ?? 'Nenhum' }}
                        </p>

                        @if($produtoMaisVendido)
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $produtoMaisVendido->total_vendido }}
                                unidades
                            </p>
                        @endif

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Novos clientes
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ $novosClientes }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Finalizados
                        </p>

                        <p class="mt-2 text-3xl font-bold text-green-700">
                            {{ $pedidosFinalizados }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Cancelados
                        </p>

                        <p class="mt-2 text-3xl font-bold text-red-700">
                            {{ $pedidosCancelados }}
                        </p>

                    </div>

                </div>

            </section>

            {{-- Pagamentos --}}
            <section>

                <div class="mb-4">

                    <h2 class="text-2xl font-bold text-gray-900">
                        Pagamentos
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Distribuição do faturamento por forma de pagamento.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Dinheiro
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $dinheiroPeriodo,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Cartão
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $cartaoPeriodo,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                        <p class="mt-2 text-xs text-gray-400">
                            Crédito:
                            R$ {{ number_format(
                                $creditoPeriodo,
                                2,
                                ',',
                                '.'
                            ) }}
                            · Débito:
                            R$ {{ number_format(
                                $debitoPeriodo,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">

                        <p class="text-sm text-gray-500">
                            Pix
                        </p>

                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            R$ {{ number_format(
                                $pixPeriodo,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    </div>

                    <div
                        class="
                            rounded-2xl border border-blue-200
                            bg-blue-50 p-5 shadow-sm
                        "
                    >

                        <p class="text-sm text-blue-700">
                            Mais utilizada
                        </p>

                        <p class="mt-2 text-2xl font-bold text-blue-800">
                            {{ $formaMaisUsada }}
                        </p>

                    </div>

                </div>

            </section>

        @else

            <div class="rounded-2xl bg-white p-8 text-center shadow-sm">

                <p class="font-semibold text-gray-700">
                    Selecione um período.
                </p>

                <p class="mt-2 text-sm text-gray-500">
                    Use os atalhos ou informe as datas para visualizar os relatórios.
                </p>

            </div>

        @endif

    </div>

</x-rimafood.layout>