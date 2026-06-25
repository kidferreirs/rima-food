<x-rimafood.layout>

<div class="p-8 max-w-3xl">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold">🖊️ Editar Cliente</h1>

        <a href="{{ route('clientes.index') }}" class="text-gray-600 hover:underline">
            ← Voltar
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('clientes.update', $cliente) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <input
            type="text"
            name="nome"
            value="{{ old('nome', $cliente->nome) }}"
            class="w-full border rounded-lg p-3"
            required
        >

        <input
            type="text"
            name="telefone"
            value="{{ old('telefone', $cliente->telefone) }}"
            class="w-full border rounded-lg p-3"
            required
        >

        <input
            type="email"
            name="email"
            value="{{ old('email', $cliente->email) }}"
            class="w-full border rounded-lg p-3"
        >

        <textarea
            name="observacao"
            class="w-full border rounded-lg p-3"
            rows="4"
        >{{ old('observacao', $cliente->observacao) }}</textarea>

        <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
            Salvar Alterações
        </button>
    </form>

</div>

</x-rimafood.layout>