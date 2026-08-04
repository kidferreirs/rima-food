<x-rimafood.layout>

    <div class="p-4 sm:p-6 lg:p-8">

        {{-- Cabeçalho --}}
        <div class="mb-6 sm:mb-8">

            <h1 class="text-3xl font-bold sm:text-4xl">
                Central da Cozinha
            </h1>

            <p class="mt-2 text-sm text-gray-500 sm:text-base">
                Acompanhe e atualize os pedidos em preparo.
            </p>

            <p class="mt-1 text-xs text-gray-400 sm:text-sm">
                {{ $restaurante->nome }}
            </p>

        </div>

        {{-- Resumo operacional --}}
        <div class="mb-6 grid grid-cols-3 gap-3 sm:gap-4">

            <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                <p class="text-xs font-semibold text-green-700 sm:text-sm">
                    Novos
                </p>

                <p class="mt-1 text-2xl font-bold text-green-800 sm:text-3xl">
                    {{ $novos->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                <p class="text-xs font-semibold text-orange-700 sm:text-sm">
                    Em preparo
                </p>

                <p class="mt-1 text-2xl font-bold text-orange-800 sm:text-3xl">
                    {{ $preparo->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <p class="text-xs font-semibold text-blue-700 sm:text-sm">
                    Prontos
                </p>

                <p class="mt-1 text-2xl font-bold text-blue-800 sm:text-3xl">
                    {{ $prontos->count() }}
                </p>
            </div>

        </div>

        {{-- Colunas operacionais --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- Novos --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm sm:p-5">

                <div class="mb-4 flex items-center justify-between gap-3">

                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            Novos
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Pedidos aguardando início.
                        </p>
                    </div>

                    <span
                        class="
                            rounded-full bg-green-100
                            px-3 py-1 text-xs font-bold text-green-700
                        "
                    >
                        {{ $novos->count() }}
                    </span>

                </div>

                <div class="space-y-4">

                    @forelse($novos as $pedido)
                        <x-cozinha.card-pedido :pedido="$pedido" />
                    @empty
                        <div class="rounded-2xl bg-gray-50 p-6 text-center">
                            <p class="font-semibold text-gray-700">
                                Nenhum pedido novo.
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Os próximos pedidos aparecerão aqui.
                            </p>
                        </div>
                    @endforelse

                </div>

            </section>

            {{-- Em preparo --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm sm:p-5">

                <div class="mb-4 flex items-center justify-between gap-3">

                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            Em preparo
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Pedidos sendo preparados.
                        </p>
                    </div>

                    <span
                        class="
                            rounded-full bg-orange-100
                            px-3 py-1 text-xs font-bold text-orange-700
                        "
                    >
                        {{ $preparo->count() }}
                    </span>

                </div>

                <div class="space-y-4">

                    @forelse($preparo as $pedido)
                        <x-cozinha.card-pedido :pedido="$pedido" />
                    @empty
                        <div class="rounded-2xl bg-gray-50 p-6 text-center">
                            <p class="font-semibold text-gray-700">
                                Nenhum pedido em preparo.
                            </p>
                        </div>
                    @endforelse

                </div>

            </section>

            {{-- Prontos --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm sm:p-5">

                <div class="mb-4 flex items-center justify-between gap-3">

                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            Prontos
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Pedidos aguardando finalização.
                        </p>
                    </div>

                    <span
                        class="
                            rounded-full bg-blue-100
                            px-3 py-1 text-xs font-bold text-blue-700
                        "
                    >
                        {{ $prontos->count() }}
                    </span>

                </div>

                <div class="space-y-4">

                    @forelse($prontos as $pedido)
                        <x-cozinha.card-pedido :pedido="$pedido" />
                    @empty
                        <div class="rounded-2xl bg-gray-50 p-6 text-center">
                            <p class="font-semibold text-gray-700">
                                Nenhum pedido pronto.
                            </p>
                        </div>
                    @endforelse

                </div>

            </section>

        </div>

    </div>

    <script>
        setInterval(() => {
            window.location.reload();
        }, 10000);
    </script>

</x-rimafood.layout>