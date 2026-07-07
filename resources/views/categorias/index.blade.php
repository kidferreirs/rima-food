<x-rimafood.layout>

    <div class="p-8">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold">📂 Categorias</h1>
                <p class="text-gray-500 mt-2">
                    Restaurante: <strong>{{ $restauranteAtual->nome }}</strong>
                </p>
            </div>

            <a href="{{ route('restaurante.categorias.create', $restauranteAtual->slug) }}" class="bg-green-500 text-white px-5 py-3 rounded-lg">
                + Nova Categoria
            </a>
        </div>

        <div class="bg-white rounded-xl shadow">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Nome</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($categorias as $categoria)
                        <tr class="border-b">
                            <td class="p-4">
                                {{ $categoria->nome }}
                            </td>

                            <td class="p-4">
                                @if($categoria->ativo)
                                    ✅ Ativa
                                @else
                                    ❌ Inativa
                                @endif
                            </td>

                            <td class="p-4 flex gap-2">
                                <a href="{{ route('restaurante.categorias.edit', [$restauranteAtual->slug, $categoria]) }}"
                                    class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded-lg">
                                    ✏️
                                </a>

                                <form action="{{ route('restaurante.categorias.status', [$restauranteAtual->slug, $categoria]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg">
                                        @if($categoria->ativo)
                                            🔴 Desativar
                                        @else
                                            🟢 Ativar
                                        @endif
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">
                                Nenhuma categoria cadastrada neste restaurante.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</x-rimafood.layout>