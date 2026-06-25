<x-rimafood.layout>

    <div class="p-8">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">👥 Clientes</h1>
            <a href="{{ route('clientes.create') }}" class="bg-green-500 text-white px-5 py-3 rounded-lg">+ Novo
                Cliente</a>
        </div>
        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Nome</th>
                        <th class="p-4 text-left">Telefone</th>
                        <th class="p-4 text-left">Email</th>
                        <th class="p-4 text-left">Restaurante</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clientes as $cliente)
                        <tr class="border-b">
                            <td class="p-4">{{ $cliente->nome ?? 'Sem nome' }}</td>
                            <td class="p-4">{{ $cliente->telefone }}</td>
                            <td class="p-4">{{ $cliente->email ?? '-' }}</td>
                            <td class="p-4">{{ $cliente->restaurante->nome }}</td>
                            <td class="p-4">
                                @if($cliente->ativo)
                                    ✅ Ativo
                                @else
                                    🔴 Inativo
                                @endif
                            </td>

                            <td class="p-4 flex gap-2">
                                <a href="{{ route('clientes.edit', $cliente) }}"
                                    class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded-lg">
                                    🖊️
                                </a>

                                <form action="{{ route('clientes.status', $cliente) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg">
                                        @if($cliente->ativo)
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
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                Nenhum cliente cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</x-rimafood.layout>