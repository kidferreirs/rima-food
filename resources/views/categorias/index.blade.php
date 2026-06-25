<x-rimafood.layout>

    <div class="p-8">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">📂 Categorias</h1>

            <a href="{{ route('categorias.create') }}" class="bg-green-500 text-white px-5 py-3 rounded-lg">
                + Nova Categoria
            </a>
        </div>

        <div class="bg-white rounded-xl shadow">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Nome</th>
                        <th class="p-4 text-left">Restaurante</th>
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
                                {{ $categoria->restaurante->nome }}
                            </td>

                            <td class="p-4">

                                @if($categoria->ativo)
                                    ✅ Ativa
                                @else
                                    ❌ Inativa
                                @endif

                            </td>

                            <td class="p-4 flex gap-2">

                                <a href="{{ route('categorias.edit', $categoria) }}"
                                    class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded-lg">
                                    ✏️
                                </a>

                                <form action="{{ route('categorias.status', $categoria) }}" method="POST">

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

                            <td colspan="4" class="p-8 text-center text-gray-500">
                                Nenhuma categoria cadastrada.
                            </td>

                        </tr>

                    @endforelse

                </tbody>


            </table>
        </div>

    </div>

</x-rimafood.layout>