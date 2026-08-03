<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <h1 class="text-4xl font-bold mb-2">📂 Nova Categoria</h1>

        <p class="text-gray-500 mb-8">
            Restaurante: <strong>{{ $restaurante->nome }}</strong>
        </p>

        <form action="{{ route('restaurante.categorias.store', $restaurante->slug) }}" method="POST" class="space-y-4">
            @csrf

            <input type="text" name="nome" placeholder="Nome da categoria" class="w-full border rounded-lg p-3"
                required>

            <div>
                <label class="block font-semibold mb-2">
                    Sinônimos
                </label>

                <textarea name="sinonimos" rows="3" placeholder="Ex.: doce, doces, sobremesa, sobremesas, torta"
                    class="w-full border rounded-lg p-3">{{ old('sinonimos') }}</textarea>

                <p class="text-sm text-gray-500 mt-1">
                    Separe as formas comuns de pedir por vírgulas.
                </p>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Palavras-chave
                </label>

                <textarea name="palavras_chave" rows="3" placeholder="Ex.: bolo, chocolate, pudim, petit gateau"
                    class="w-full border rounded-lg p-3">{{ old('palavras_chave') }}</textarea>

                <p class="text-sm text-gray-500 mt-1">
                    Informe palavras relacionadas aos produtos desta categoria.
                </p>
            </div>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar
            </button>
        </form>
    </div>
</x-rimafood.layout>