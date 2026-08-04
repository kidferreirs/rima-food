<x-rimafood.layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="mb-6 flex items-start justify-between gap-4 sm:mb-8">
            <div class="min-w-0">
                <h1 class="text-3xl font-bold sm:text-4xl">Central de Pedidos</h1>
                <p class="mt-2 text-sm text-gray-500 sm:text-base">Gerencie todos os pedidos do restaurante.</p>
                <p class="mt-1 text-xs text-gray-400 sm:text-sm">Pedidos prioritários recebem atendimento preferencial.</p>
            </div>

            <a href="{{ route('restaurante.pedidos.create', $restaurante->slug) }}"
               class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-xl bg-green-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-600">
                + Pedido
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm sm:p-5">
            <label for="buscaPedido" class="mb-2 block text-sm font-semibold text-gray-700">Buscar pedido</label>
            <input type="search" id="buscaPedido"
                   placeholder="Pedido, cliente, item, status ou origem..."
                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
        </div>

        <div id="cardsPedidos" class="space-y-4 md:hidden">
            @forelse($pedidos as $pedido)
                @php
                    $statusLabel = match ($pedido->status) {
                        'novo' => 'Novo',
                        'preparando' => 'Preparando',
                        'pronto' => 'Pronto',
                        'saiu_entrega' => 'Saiu para entrega',
                        'finalizado' => 'Finalizado',
                        'cancelado' => 'Cancelado',
                        default => ucfirst($pedido->status),
                    };

                    $statusClass = match ($pedido->status) {
                        'novo' => 'bg-yellow-100 text-yellow-800',
                        'preparando' => 'bg-blue-100 text-blue-800',
                        'pronto' => 'bg-purple-100 text-purple-800',
                        'saiu_entrega' => 'bg-orange-100 text-orange-800',
                        'finalizado' => 'bg-green-100 text-green-800',
                        'cancelado' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-700',
                    };

                    $origemLabel = match ($pedido->origem) {
                        'balcao' => 'Balcão',
                        'whatsapp' => 'WhatsApp',
                        default => 'Online',
                    };

                    $origemClass = match ($pedido->origem) {
                        'balcao' => 'bg-gray-100 text-gray-700',
                        'whatsapp' => 'bg-green-100 text-green-800',
                        default => 'bg-blue-100 text-blue-800',
                    };

                    $inicioStatus = match ($pedido->status) {
                        'novo' => $pedido->novo_em ?? $pedido->created_at,
                        'preparando' => $pedido->preparando_em,
                        'pronto' => $pedido->pronto_em,
                        'saiu_entrega' => $pedido->pronto_em,
                        default => null,
                    };

                    $minutos = $inicioStatus
                        ? (int) floor(\Carbon\Carbon::parse($inicioStatus)->diffInMinutes(now()))
                        : null;

                    $tempoClass = $minutos === null
                        ? ''
                        : ($minutos < 20
                            ? 'bg-green-100 text-green-700'
                            : ($minutos < 40
                                ? 'bg-orange-100 text-orange-700'
                                : 'bg-red-100 text-red-700'));
                @endphp

                <article class="pedido-card rounded-2xl border bg-white p-4 shadow-sm {{ $pedido->prioritario && !in_array($pedido->status, ['finalizado', 'cancelado']) ? 'border-yellow-300 ring-1 ring-yellow-200' : 'border-gray-100' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @if($pedido->prioritario && !in_array($pedido->status, ['finalizado', 'cancelado']))
                                    <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-[11px] font-bold text-yellow-800">Prioritário</span>
                                @endif

                                <h2 class="truncate text-lg font-bold text-blue-600">Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}</h2>
                            </div>

                            @if($pedido->token)
                                <p class="mt-1 text-xs text-gray-400">Token: {{ $pedido->token }}</p>
                            @endif
                        </div>

                        <p class="shrink-0 text-lg font-bold text-gray-900">R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Cliente</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $pedido->cliente->nome ?? 'Cliente não informado' }}</p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Data</p>
                            <p class="mt-1 text-sm font-medium text-gray-700">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $origemClass }}">{{ $origemLabel }}</span>

                        @if($minutos !== null && !in_array($pedido->status, ['finalizado', 'cancelado']))
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tempoClass }}">{{ $minutos }} min</span>
                        @endif
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Itens</p>
                        <div class="mt-2 space-y-1 text-sm text-gray-700">
                            @foreach($pedido->itens as $item)
                                <p>{{ $item->quantidade }}x {{ $item->produto->nome }}</p>
                            @endforeach
                        </div>
                    </div>

                    @if(!in_array($pedido->status, ['cancelado', 'finalizado']))
                        <form action="{{ route('restaurante.pedidos.status', [$restauranteAtual->slug, $pedido]) }}"
                              method="POST" class="mt-4">
                            @csrf
                            @method('PATCH')

                            <label class="mb-2 block text-sm font-semibold text-gray-700">Alterar status</label>
                            <select name="status" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-3 text-sm">
                                <option value="novo" @selected($pedido->status === 'novo')>Novo</option>
                                <option value="preparando" @selected($pedido->status === 'preparando')>Preparando</option>
                                <option value="pronto" @selected($pedido->status === 'pronto')>Pronto</option>
                                <option value="saiu_entrega" @selected($pedido->status === 'saiu_entrega')>Saiu para entrega</option>
                                <option value="finalizado" @selected($pedido->status === 'finalizado')>Finalizado</option>
                                <option value="cancelado" @selected($pedido->status === 'cancelado')>Cancelado</option>
                            </select>
                        </form>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a href="{{ route('restaurante.pedidos.show', [$restauranteAtual->slug, $pedido]) }}"
                           class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Ver
                        </a>

                        <a href="{{ route('restaurante.pedidos.imprimir', [$restauranteAtual->slug, $pedido]) }}"
                           target="_blank"
                           class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Imprimir
                        </a>

                        @if(in_array($pedido->status, ['novo', 'preparando']))
                            <a href="{{ route('restaurante.pedidos.edit', [$restauranteAtual->slug, $pedido]) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-yellow-50 px-4 py-2.5 text-sm font-semibold text-yellow-800 transition hover:bg-yellow-100">
                                Editar
                            </a>

                            <form action="{{ route('restaurante.pedidos.cancelar', [$restauranteAtual->slug, $pedido]) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                                        onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">
                                    Cancelar
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl bg-white p-8 text-center shadow-sm">
                    <p class="font-semibold text-gray-700">Nenhum pedido criado ainda.</p>
                    <p class="mt-2 text-sm text-gray-500">Os pedidos aparecerão aqui assim que forem registrados.</p>
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-hidden rounded-2xl bg-white shadow md:block">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px]">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Pedido</th>
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Cliente</th>
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Itens</th>
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Total</th>
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Origem</th>
                            <th class="p-4 text-left text-sm font-semibold text-gray-600">Data</th>
                            <th class="p-4 text-right text-sm font-semibold text-gray-600">Ações</th>
                        </tr>
                    </thead>

                    <tbody id="tabelaPedidos">
                        @forelse($pedidos as $pedido)
                            @php
                                $statusLabel = match ($pedido->status) {
                                    'novo' => 'Novo',
                                    'preparando' => 'Preparando',
                                    'pronto' => 'Pronto',
                                    'saiu_entrega' => 'Saiu para entrega',
                                    'finalizado' => 'Finalizado',
                                    'cancelado' => 'Cancelado',
                                    default => ucfirst($pedido->status),
                                };

                                $statusClass = match ($pedido->status) {
                                    'novo' => 'bg-yellow-100 text-yellow-800',
                                    'preparando' => 'bg-blue-100 text-blue-800',
                                    'pronto' => 'bg-purple-100 text-purple-800',
                                    'saiu_entrega' => 'bg-orange-100 text-orange-800',
                                    'finalizado' => 'bg-green-100 text-green-800',
                                    'cancelado' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-700',
                                };

                                $origemLabel = match ($pedido->origem) {
                                    'balcao' => 'Balcão',
                                    'whatsapp' => 'WhatsApp',
                                    default => 'Online',
                                };

                                $inicioStatus = match ($pedido->status) {
                                    'novo' => $pedido->novo_em ?? $pedido->created_at,
                                    'preparando' => $pedido->preparando_em,
                                    'pronto' => $pedido->pronto_em,
                                    'saiu_entrega' => $pedido->pronto_em,
                                    default => null,
                                };

                                $minutos = $inicioStatus
                                    ? (int) floor(\Carbon\Carbon::parse($inicioStatus)->diffInMinutes(now()))
                                    : null;

                                $tempoClass = $minutos === null
                                    ? ''
                                    : ($minutos < 20
                                        ? 'text-green-600'
                                        : ($minutos < 40
                                            ? 'text-orange-600'
                                            : 'text-red-600'));
                            @endphp

                            <tr class="pedido-linha border-b transition hover:bg-gray-50 {{ $pedido->prioritario && !in_array($pedido->status, ['finalizado', 'cancelado']) ? 'bg-yellow-50' : '' }}">
                                <td class="p-4 align-top">
                                    <div class="flex items-start gap-2">
                                        @if($pedido->prioritario && !in_array($pedido->status, ['finalizado', 'cancelado']))
                                            <span class="mt-0.5 rounded-full bg-yellow-100 px-2 py-1 text-[10px] font-bold text-yellow-800">Prioritário</span>
                                        @endif

                                        <div>
                                            <p class="font-bold text-blue-600">#{{ $pedido->numero_pedido ?? $pedido->id }}</p>
                                            @if($pedido->token)
                                                <p class="mt-1 text-xs text-gray-400">{{ $pedido->token }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4 align-top">{{ $pedido->cliente->nome ?? 'Cliente não informado' }}</td>

                                <td class="p-4 align-top">
                                    <div class="space-y-1 text-sm text-gray-700">
                                        @foreach($pedido->itens as $item)
                                            <p>{{ $item->quantidade }}x {{ $item->produto->nome }}</p>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="p-4 align-top font-semibold">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>

                                <td class="p-4 align-top">
                                    <div class="space-y-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>

                                        @if($minutos !== null && !in_array($pedido->status, ['finalizado', 'cancelado']))
                                            <p class="text-xs font-semibold {{ $tempoClass }}">{{ $minutos }} min</p>
                                        @endif
                                    </div>

                                    @if(!in_array($pedido->status, ['cancelado', 'finalizado']))
                                        <form action="{{ route('restaurante.pedidos.status', [$restauranteAtual->slug, $pedido]) }}"
                                              method="POST" class="mt-3">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" onchange="this.form.submit()"
                                                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                                                <option value="novo" @selected($pedido->status === 'novo')>Novo</option>
                                                <option value="preparando" @selected($pedido->status === 'preparando')>Preparando</option>
                                                <option value="pronto" @selected($pedido->status === 'pronto')>Pronto</option>
                                                <option value="saiu_entrega" @selected($pedido->status === 'saiu_entrega')>Saiu para entrega</option>
                                                <option value="finalizado" @selected($pedido->status === 'finalizado')>Finalizado</option>
                                                <option value="cancelado" @selected($pedido->status === 'cancelado')>Cancelado</option>
                                            </select>
                                        </form>
                                    @endif
                                </td>

                                <td class="p-4 align-top">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $origemLabel }}</span>
                                </td>

                                <td class="p-4 align-top text-sm text-gray-600">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>

                                <td class="p-4 align-top">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('restaurante.pedidos.show', [$restauranteAtual->slug, $pedido]) }}"
                                           class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Ver</a>

                                        <a href="{{ route('restaurante.pedidos.imprimir', [$restauranteAtual->slug, $pedido]) }}"
                                           target="_blank"
                                           class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Imprimir</a>

                                        @if(in_array($pedido->status, ['novo', 'preparando']))
                                            <a href="{{ route('restaurante.pedidos.edit', [$restauranteAtual->slug, $pedido]) }}"
                                               class="rounded-lg bg-yellow-50 px-3 py-2 text-sm font-semibold text-yellow-800 transition hover:bg-yellow-100">Editar</a>

                                            <form action="{{ route('restaurante.pedidos.cancelar', [$restauranteAtual->slug, $pedido]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                                                        onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">Cancelar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">Nenhum pedido criado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="semResultadosDesktop" class="hidden p-8 text-center text-gray-500">Nenhum pedido encontrado.</div>
        </div>

        <div id="semResultadosMobile" class="hidden rounded-2xl bg-white p-8 text-center text-gray-500 shadow-sm md:hidden">
            Nenhum pedido encontrado.
        </div>
    </div>

    <script>
        const buscaPedido = document.getElementById('buscaPedido');
        const linhas = document.querySelectorAll('.pedido-linha');
        const cards = document.querySelectorAll('.pedido-card');
        const semResultadosDesktop = document.getElementById('semResultadosDesktop');
        const semResultadosMobile = document.getElementById('semResultadosMobile');

        buscaPedido?.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let encontradosDesktop = 0;
            let encontradosMobile = 0;

            linhas.forEach(function (linha) {
                const exibir = linha.innerText.toLowerCase().includes(termo);
                linha.style.display = exibir ? '' : 'none';
                if (exibir) encontradosDesktop++;
            });

            cards.forEach(function (card) {
                const exibir = card.innerText.toLowerCase().includes(termo);
                card.style.display = exibir ? '' : 'none';
                if (exibir) encontradosMobile++;
            });

            semResultadosDesktop?.classList.toggle('hidden', encontradosDesktop > 0);
            semResultadosMobile?.classList.toggle('hidden', encontradosMobile > 0);
        });
    </script>
</x-rimafood.layout>