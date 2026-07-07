<x-rimafood.layout>

    <div class="p-8">
        <h1 class="text-4xl font-bold mb-2">📊 Relatórios</h1>
        @if($restaurante)
            <p class="text-gray-500 mb-8">Restaurante: <strong>{{ $restaurante->nome }}</strong></p>
        @endif

        @if($erroPeriodo)
            <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded-xl mb-6">
                ⚠️ {{ $erroPeriodo }}
            </div>
        @endif

        <form method="GET" class="mb-10">
            <div class="mb-4">
                <h3 class="font-bold text-lg"> 📅 Período </h3>
                <p class="text-sm text-gray-500"> Selecione o intervalo desejado. </p>
            </div>

            <div class="flex flex-wrap gap-3 mb-6">
                <a href="{{ route('restaurante.relatorios.index', [$restauranteAtual->slug, 'atalho' => 'hoje']) }}"
                    class="bg-white border px-4 py-2 rounded-xl shadow-sm hover:bg-gray-100 transition">
                    Hoje
                </a>

                <a href="{{ route('restaurante.relatorios.index', [$restauranteAtual->slug, 'atalho' => 'ontem']) }}"
                    class="bg-white border px-4 py-2 rounded-xl shadow-sm hover:bg-gray-100 transition">
                    Ontem
                </a>

                <a href="{{ route('restaurante.relatorios.index', [$restauranteAtual->slug, 'atalho' => 'semana']) }}"
                    class="bg-white border px-4 py-2 rounded-xl shadow-sm hover:bg-gray-100 transition">
                    Esta semana
                </a>

                <a href="{{ route('restaurante.relatorios.index', [$restauranteAtual->slug, 'atalho' => 'mes']) }}"
                    class="bg-white border px-4 py-2 rounded-xl shadow-sm hover:bg-gray-100 transition">
                    Este mês
                </a>
            </div>

            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">📅 Data inicial</label>
                    <input type="date" name="data_inicio" value="{{ $dataInicio }}"
                        class="w-44 h-12 border rounded-xl px-4 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2"> 📅 Data final </label>
                    <input type="date" name="data_fim" value="{{ $dataFim }}"
                        class="w-44 h-12 border rounded-xl px-4 shadow-sm">
                </div>

                <button type="submit"
                    class="w-[100px] h-[44px] bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold flex items-center justify-center gap-2 shadow-sm transition">
                    🔎
                    <span>Filtrar</span>
                </button>

                <a href="{{ route('restaurante.relatorios.index', $restauranteAtual->slug) }}"
                    class="w-[100px] h-[44px] bg-gray-800 hover:bg-gray-600 text-white rounded-xl font-semibold flex items-center justify-center gap-2 shadow-sm transition">
                    🧹
                    <span>Limpar</span>
                </a>

                @if($temFiltro)
                    <a href="{{ route('restaurante.relatorios.exportar', array_merge([$restauranteAtual->slug], request()->query())) }}"
                        class="h-[44px] bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold flex items-center justify-center gap-2 shadow-sm transition px-5">
                        📤
                        <span>Exportar CSV</span>
                    </a>
                @endif
            </div>
        </form>

        @if($temFiltro)
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-2xl font-bold whitespace-nowrap"> 💰 Financeiro </h2>
                <div class="w-full border-b border-gray-300"> </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-4">
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">💰 Faturamento Hoje</h2>
                    <p class="text-4xl font-bold mt-2"> R$ {{ number_format($faturamentoHoje, 2, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">💰 Faturamento Semanal</h2>
                    <p class="text-4xl font-bold mt-2"> R$ {{ number_format($faturamentoSemanal, 2, ',', '.') }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">💰 Faturamento do Mês</h2>
                    <p class="text-4xl font-bold mt-2"> R$ {{ number_format($faturamentoMes, 2, ',', '.') }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">📅 Faturamento no Período</h2>
                    <p class="text-4xl font-bold mt-2"> R$ {{ number_format($faturamentoPeriodo, 2, ',', '.') }} </p>
                </div>
            </div>

            <h2 class="text-2xl font-bold mt-10 mb-4"> 📈 Operação </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mt-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">📈 Ticket Médio</h2>
                    <p class="text-3xl font-bold mt-2"> R$ {{ number_format($ticketMedio, 2, ',', '.') }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">🏆 Campeão de Vendas</h2>
                    <p class="text-2xl font-bold mt-3"> {{ $produtoMaisVendido?->produto?->nome ?? 'Nenhum' }} </p>
                    @if($produtoMaisVendido)
                        <p class="text-sm text-gray-500 mt-2"> {{ $produtoMaisVendido->total_vendido }} unidades </p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">👥 Clientes Cadastrados</h2>
                    <p class="text-3xl font-bold mt-2"> {{ $novosClientes }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">📦 Pedidos Finalizados</h2>
                    <p class="text-3xl font-bold mt-2"> {{ $pedidosFinalizados }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">❌ Pedidos Cancelados</h2>
                    <p class="text-3xl font-bold mt-2"> {{ $pedidosCancelados }} </p>
                </div>

            </div>
            
            <h2 class="text-2xl font-bold mt-10 mb-4"> 💳 Pagamentos </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">💵 Dinheiro</h2>
                    <p class="text-3xl font-bold mt-2"> R$ {{ number_format($dinheiroPeriodo, 2, ',', '.') }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">💳 Cartão</h2>
                    <p class="text-3xl font-bold mt-2"> R$ {{ number_format($cartaoPeriodo, 2, ',', '.') }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">🏦 Pix</h2>
                    <p class="text-3xl font-bold mt-2"> R$ {{ number_format($pixPeriodo, 2, ',', '.') }} </p>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-gray-500">🥇 Mais Utilizado</h2>
                    <p class="text-2xl font-bold mt-3"> {{ $formaMaisUsada }} </p>
                </div>

            </div>
        @else
            <div class="bg-white rounded-xl shadow p-8 text-center text-gray-500 mt-8">
                📅 Selecione um período para visualizar os relatórios.
            </div>

        @endif

    </div>
</x-rimafood.layout>