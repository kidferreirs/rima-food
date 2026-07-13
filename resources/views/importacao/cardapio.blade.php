<x-rimafood.layout>

    <div class="p-8 max-w-6xl">

        <h1 class="text-4xl font-bold mb-2">📥 Importar Cardápio</h1>

        <p class="text-gray-500 mb-8">
            Restaurante: <strong>{{ $restaurante->nome }}</strong>
        </p>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">1. Enviar arquivo CSV ou Excel</h2>

            <p class="text-gray-500 mb-4">
                Use as colunas: <strong>categoria, produto, descricao, preco</strong>
            </p>

            <div class="bg-slate-50 border rounded-xl p-4 mb-5 text-sm text-gray-600">
                <p class="font-bold mb-2">📄 Modelo CSV</p>
                <p>categoria;produto;descricao;preco</p>
                <p>Bebidas;Coca Cola 350ml;Lata bem gelada;5,00</p>
                <p>Hambúrgueres;X Burguer;Pão, carne e queijo;24,90</p>
            </div>


            <a href="{{ route('restaurante.importacao.modelo', $restaurante->slug) }}"
                class="inline-block bg-slate-800 text-white px-5 py-3 rounded-xl font-bold mb-5">
                📄 Baixar Modelo CSV
            </a>

            <form action="{{ route('restaurante.importacao.preview', $restaurante->slug) }}" method="POST"
                enctype="multipart/form-data" class="flex gap-4 items-center">

                @csrf

                <input type="file" name="arquivo" accept=".csv,.txt,.xlsx" required class="border rounded-xl p-3">

                <button class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold">
                    👀 Pré-visualizar
                </button>
            </form>
        </div>

        @if(!empty($linhas))
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-2xl font-bold mb-4">2. Pré-visualização</h2>

                @if(!empty($estatisticas))
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-slate-50 rounded-xl p-4 text-center">
                            <p class="text-gray-500">📂 Categorias</p>
                            <p class="text-3xl font-bold">{{ $estatisticas['categorias_total'] }}</p>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-4 text-center">
                            <p class="text-gray-500">✨ Novas categorias</p>
                            <p class="text-3xl font-bold">{{ $estatisticas['categorias_novas'] }}</p>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-4 text-center">
                            <p class="text-gray-500">🍔 Produtos</p>
                            <p class="text-3xl font-bold">{{ $estatisticas['produtos_total'] }}</p>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-4 text-center">
                            <p class="text-gray-500">🆕 Novos</p>
                            <p class="text-3xl font-bold">{{ $estatisticas['produtos_novos'] }}</p>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-4 text-center">
                            <p class="text-gray-500">♻️ Atualizados</p>
                            <p class="text-3xl font-bold">{{ $estatisticas['produtos_atualizados'] }}</p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 mb-6">
                        ⚠️ Produtos já existentes serão atualizados. Categorias inexistentes serão criadas automaticamente.
                    </div>
                @endif

                <table class="w-full mb-6">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">Categoria</th>
                            <th class="p-3 text-left">Produto</th>
                            <th class="p-3 text-left">Descrição</th>
                            <th class="p-3 text-left">Preço</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($linhas as $linha)
                            <tr class="border-b">
                                <td class="p-3">{{ $linha['categoria'] }}</td>
                                <td class="p-3 font-bold">{{ $linha['nome'] }}</td>
                                <td class="p-3 text-gray-500">{{ $linha['descricao'] }}</td>
                                <td class="p-3">
                                    R$ {{ number_format($linha['preco'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <form action="{{ route('restaurante.importacao.importar', $restaurante->slug) }}" method="POST">
                    @csrf

                    <button class="bg-green-600 text-white px-8 py-4 rounded-xl font-bold">
                        ✅ Confirmar Importação
                    </button>
                </form>
            </div>
        @endif

    </div>

</x-rimafood.layout>