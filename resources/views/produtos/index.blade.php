<x-rimafood.layout>

    <div class="p-8">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">🍔 Produtos</h1>

            <a href="{{ route('produtos.create') }}" class="bg-green-500 text-white px-5 py-3 rounded-lg">
                + Novo Produto
            </a>
        </div>

        <div class="bg-white rounded-xl shadow">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Nome</th>
                        <th class="p-4 text-left">Categoria</th>
                        <th class="p-4 text-left">Preço</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($produtos as $produto)
                        <tr class="border-b">
                            <td class="p-4">{{ $produto->nome }}</td>
                            <td class="p-4">{{ $produto->categoria->nome }}</td>
                            <td class="p-4">R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                            <td class="p-4">
                                @if($produto->ativo)
                                    ✅ Ativo
                                @else
                                    🔴 Inativo
                                @endif
                            </td>

                            <td class="p-4 flex gap-2">

                                <a href="{{ route('produtos.edit', $produto) }}"
                                    class="bg-white-100 text-white-700 px-3 py-2 rounded-lg">

                                    ✏️

                                </a>

                                <form action="{{ route('produtos.status', $produto) }}" method="POST">

                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg">

                                        @if($produto->ativo)

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
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                Nenhum produto cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</x-rimafood.layout>