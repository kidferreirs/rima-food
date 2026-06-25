<x-rimafood.layout>

    <div class="p-8">

        <div class="flex justify-between items-center mb-8">

            <h1 class="text-4xl font-bold">
                🏪 Restaurantes
            </h1>

            <a href="{{ route('restaurantes.create') }}" class="bg-green-500 text-white px-5 py-3 rounded-lg">

                + Novo Restaurante

            </a>

        </div>

        <div class="bg-white rounded-xl shadow">

            <table class="w-full">

                <thead>

                    <tr class="border-b">

                        <th class="p-4 text-left">Nome</th>

                        <th class="p-4 text-left">Cidade</th>

                        <th class="p-4 text-left">Estado</th>

                        <th class="p-4 text-left">Status</th>

                        <th class="p-4 text-left">Ações</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($restaurantes as $restaurante)

                        <tr class="border-b">

                            <td class="p-4">

                                {{ $restaurante->nome }}

                            </td>

                            <td class="p-4">

                                {{ $restaurante->cidade }}

                            </td>

                            <td class="p-4">

                                {{ $restaurante->estado }}

                            </td>

                            <td class="p-4">

                                @if($restaurante->ativo)

                                    ✅ Ativo

                                @else

                                    ❌ Inativo

                                @endif

                            </td>

                            <td class="p-4 flex gap-2">

                                <a href="{{ route('restaurantes.edit', $restaurante) }}"
                                    class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded-lg">
                                    ✏️
                                </a>

                                <form action="{{ route('restaurantes.status', $restaurante) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg">
                                        @if($restaurante->ativo)
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

                                Nenhum restaurante cadastrado.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</x-rimafood.layout>