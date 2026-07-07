<x-rimafood.layout>

    <div class="p-8">

        <h1 class="text-4xl font-bold mb-2">📱 Conversas WhatsApp</h1>
        <p class="text-gray-500 mb-8"> Central de atendimento automatizado. </p>

        <form class="mb-8">
            <div class="flex flex-wrap gap-4 items-center">
                <input type="text" name="busca" value="{{ $busca }}" placeholder="🔎 Buscar cliente ou telefone"
                    class="w-80 border rounded-xl px-4 py-3 shadow-sm">
                <select name="filtro" class="border rounded-xl px-4 py-3 shadow-sm">
                    <option value=""> 📋 Todas </option>
                    <option value="aguardando" @selected($filtro == 'aguardando')>
                        🔴 Aguardando
                    </option>
                    <option value="atendimento" @selected($filtro == 'atendimento')>
                        🟢 Em atendimento
                    </option>
                    <option value="finalizadas" @selected($filtro == 'finalizadas')>
                        ⚪ Finalizadas
                    </option>
                </select>
                <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl">
                    🔎 Filtrar
                </button>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500"> 🔴 Aguardando </h2>
                <p class="text-4xl font-bold mt-2"> {{ $aguardando }} </p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500"> 🟢 Em atendimento </h2>
                <p class="text-4xl font-bold mt-2">{{ $atendimento }} </p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500"> ⚪ Finalizadas </h2>
                <p class="text-4xl font-bold mt-2">{{ $finalizadas }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-8">
            @if($conversas->isEmpty())
                <div class="text-center text-gray-500">
                    🤖 Nenhuma conversa recebida ainda.
                </div>
            @else
                @foreach($conversas as $conversa)
                    <a href="{{ route('restaurante.whatsapp.show', [ $restauranteAtual->slug, $conversa ]) }}"
                       class="border-b py-5 flex justify-between items-center hover:bg-gray-50 rounded-xl px-4 transition">
                        <div>
                            <div class="font-bold text-lg">
                                👤 {{ $conversa->nome_cliente ?? 'Cliente sem nome' }}
                            </div>

                            <div class="text-sm text-gray-500">
                                📞 {{ $conversa->telefone }}
                            </div>

                            <div class="text-gray-700 mt-2">
                                💬 {{ $conversa->ultima_mensagem ?? 'Sem mensagem registrada.' }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-sm font-semibold mb-2">
                                @if($conversa->estado === 'inicio')
                                    🔴 Aguardando
                                @elseif($conversa->estado === 'finalizado')
                                    ⚪ Finalizada
                                @else
                                    🟢 Em atendimento
                                @endif
                            </div>

                            <div class="text-xs text-gray-400">
                                @if($conversa->ultima_interacao)
                                    🕒 {{ $conversa->ultima_interacao->diffForHumans() }}
                                @else
                                    🕒 Sem interação
                                @endif
                            </div>

                            @if($conversa->atendimento_humano)
                                <div class="text-xs text-blue-600 mt-2 font-semibold">
                                    👨‍💼 Atendimento humano
                                </div>
                            @else
                                <div class="text-xs text-green-600 mt-2 font-semibold">
                                    🤖 Rima atendendo
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</x-rimafood.layout>