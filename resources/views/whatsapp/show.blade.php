<x-rimafood.layout>

    <div class="p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold"> 📱 Conversa #{{ $conversa->id }}</h1>
                <p class="text-gray-500"> Atendimento em tempo real.</p>
            </div>

            <a href="{{ route('restaurante.whatsapp.index', $restauranteAtual->slug) }}" class="text-gray-600 hover:underline">
                ← Voltar
            </a>
        </div>

        <div class="bg-white rounded-xl shadow p-8">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-2xl font-bold">
                        👤 {{ $conversa->nome_cliente ?? 'Cliente' }}
                    </div>
                    <div class="text-gray-500 mt-1">
                        📞 {{ $conversa->telefone }}
                    </div>
                    <div class="text-sm text-gray-400 mt-2">
                        🆔 Conversa #{{ $conversa->id }}
                    </div>
                </div>

                <div class="text-right">
                    @if($conversa->atendimento_humano)
                        <div class="text-blue-600 font-semibold">
                            👨‍💼 Atendimento humano
                        </div>
                    @else
                        <div class="text-green-600 font-semibold">
                            🤖 Rima atendendo
                        </div>
                    @endif

                    <div class="text-gray-400 text-sm mt-2">
                        🕒 {{ $conversa->ultima_interacao?->diffForHumans() }}
                    </div>
                </div>
            </div>

            <hr class="my-8">

            <h2 class="font-bold text-xl mb-4">

                💬 Histórico da conversa

            </h2>

            <div class="space-y-4">

                @if($conversa->historico)

                    @foreach($conversa->historico as $item)

                        <div class="bg-gray-50 rounded-xl p-4 border">

                            <div class="flex justify-between mb-2">

                                <div class="font-semibold">

                                    @if($item['autor'] === 'cliente')

                                        👤 Cliente

                                    @else

                                        🤖 Rima

                                    @endif

                                </div>

                                <div class="text-sm text-gray-400">

                                    🕒 {{ $item['hora'] }}

                                </div>

                            </div>

                            <div>

                                {{ $item['mensagem'] }}

                            </div>

                        </div>

                    @endforeach

                @else

                    <div class="text-gray-400">

                        Nenhuma mensagem ainda.

                    </div>

                @endif

            </div>

            <div class="mt-8">
                <h2 class="font-bold text-xl mb-4"> 🧠 Estado atual </h2>
                <div
                    class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-semibold">
                    🟡 {{ ucfirst(str_replace('_', ' ', $conversa->estado)) }}
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mt-8">
                {{-- Carrinho --}}
                <div class="bg-gray-50 rounded-xl p-6 border">
                    <div class="font-bold text-lg mb-4">

                        🛒 Carrinho atual

                    </div>

                    @if($conversa->carrinho)

                        @foreach($conversa->carrinho as $item)

                            <div class="mb-2">

                                🍔 {{ $item }}

                            </div>

                        @endforeach

                    @else

                        <div class="text-gray-400">
                            Ainda vazio
                        </div>

                    @endif

                </div>


                {{-- Entrega --}}

                <div class="bg-gray-50 rounded-xl p-6 border">

                    <div class="font-bold text-lg mb-4">
                        🚚 Entrega
                    </div>

                    @if($conversa->endereco_entrega)

                        <div>

                            {{ $conversa->endereco_entrega }}

                        </div>

                    @else

                        <div class="text-gray-400">

                            Não definida

                        </div>

                    @endif

                </div>


                {{-- Pagamento --}}

                <div class="bg-gray-50 rounded-xl p-6 border">

                    <div class="font-bold text-lg mb-4">

                        💳 Pagamento

                    </div>

                    @if($conversa->forma_pagamento)

                        <div>

                            {{ ucfirst($conversa->forma_pagamento) }}

                        </div>

                    @else

                        <div class="text-gray-400">

                            Não definido

                        </div>

                    @endif

                </div>
            </div>
            <div class="mt-8">
                <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                    <h2 class="font-bold text-xl mb-3"> 🎯 Próxima ação </h2>
                    <div class="text-gray-700">
                        Aguardando a próxima interação do cliente.
                    </div>
                </div>
            </div>
            <div class="mt-8">

                <div class="bg-white border rounded-xl p-6">

                    <h2 class="font-bold text-xl mb-4">

                        🧪 Simulador da Rima

                    </h2>

                    <form action="{{ route('restaurante.whatsapp.simular', [ $restauranteAtual->slug, $conversa ]) }}" method="POST">

                    @csrf
                        <div class="flex gap-4">

                            <input type="text" name="mensagem" placeholder="Digite uma mensagem..."
                                class="flex-1 border rounded-xl px-4 py-3">

                            <button class="bg-green-500 hover:bg-green-600 text-white px-6 rounded-xl">

                                🚀 Enviar

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-rimafood.layout>