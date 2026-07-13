<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">✏️ Editar Restaurante</h1>

            <a href="{{ route('restaurantes.index') }}" class="text-gray-600 hover:underline">
                ← Voltar
            </a>
        </div>

        <form action="{{ route('restaurantes.update', $restaurante) }}" method="POST" enctype="multipart/form-data"
            class="space-y-4">

            @csrf
            @method('PUT')

            <input type="text" name="nome" value="{{ $restaurante->nome }}" placeholder="Nome do restaurante"
                class="w-full border rounded-lg p-3" required>

            <input type="text" name="telefone" value="{{ $restaurante->telefone }}" placeholder="Telefone"
                class="w-full border rounded-lg p-3">

            <input type="text" name="documento" id="documento" value="{{ $restaurante->documento }}"
                placeholder="CPF ou CNPJ" maxlength="18" oninput="mascaraDocumento(this)"
                class="w-full border rounded-lg p-3">

            <input type="email" name="email" value="{{ $restaurante->email }}" placeholder="Email"
                class="w-full border rounded-lg p-3">

            <input type="text" id="cep" name="cep" value="{{ $restaurante->cep }}" placeholder="CEP"
                class="w-full border rounded-lg p-3">

            <input type="text" id="endereco" name="endereco" value="{{ $restaurante->endereco }}" placeholder="Endereço"
                class="w-full border rounded-lg p-3">

            <input type="text" id="numero" name="numero" value="{{ $restaurante->numero }}" placeholder="Número"
                class="w-full border rounded-lg p-3">

            <input type="text" name="complemento" value="{{ $restaurante->complemento }}" placeholder="Complemento"
                class="w-full border rounded-lg p-3">

            <input type="text" id="bairro" name="bairro" value="{{ $restaurante->bairro }}" placeholder="Bairro"
                class="w-full border rounded-lg p-3">

            <input type="text" id="cidade" name="cidade" value="{{ $restaurante->cidade }}" placeholder="Cidade"
                class="w-full border rounded-lg p-3">

            <input type="text" id="estado" name="estado" value="{{ $restaurante->estado }}" placeholder="Estado"
                class="w-full border rounded-lg p-3">

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4">⭐ Avaliação Google</h2>

            <input type="number" step="0.1" min="0" max="5" name="google_rating"
                value="{{ old('google_rating', $restaurante->google_rating) }}" placeholder="Nota Google. Ex: 4.8"
                class="w-full border rounded-lg p-3">

            <input type="number" name="google_reviews_total"
                value="{{ old('google_reviews_total', $restaurante->google_reviews_total) }}"
                placeholder="Total de avaliações. Ex: 123" class="w-full border rounded-lg p-3">

            <input type="text" name="google_maps_url"
                value="{{ old('google_maps_url', $restaurante->google_maps_url) }}" placeholder="Link do Google Maps"
                class="w-full border rounded-lg p-3">

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4">🎨 Visual do Cardápio</h2>

            <label class="font-semibold">Logo</label>
            <input type="file" name="logo" accept="image/*" class="w-full border rounded-lg p-3">

            <label class="font-semibold">Banner</label>
            <input type="file" name="banner" accept="image/*" class="w-full border rounded-lg p-3">

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4">⏰ Horário de Atendimento</h2>

            <div>
                <label class="font-semibold">Abre às</label>

                <input type="text" name="abre_as"
                    value="{{ old('abre_as', $restaurante->abre_as ? \Carbon\Carbon::parse($restaurante->abre_as)->format('H:i') : '') }}"
                    placeholder="18:00" maxlength="5" inputmode="numeric" oninput="mascaraHora(this)"
                    class="w-full border rounded-lg p-3">

                <small class="text-gray-500"> Formato 24h. Ex.: 08:00, 15:00, 23:30 </small>
            </div>

            <div>
                <label class="font-semibold">Fecha às</label>

                <input type="text" name="fecha_as"
                    value="{{ old('fecha_as', $restaurante->fecha_as ? \Carbon\Carbon::parse($restaurante->fecha_as)->format('H:i') : '') }}"
                    placeholder="00:00" maxlength="5" inputmode="numeric" oninput="mascaraHora(this)"
                    class="w-full border rounded-lg p-3">

                <small class="text-gray-500"> Formato 24h. Ex.: 12:00, 00:00, 02:00 </small>
            </div>

            <h2 class="text-2xl font-bold mb-4">
                🍽 Atendimento
            </h2>

            <p class="text-gray-500 mb-5">
                Como este estabelecimento atende seus clientes?
            </p>

            <div class="space-y-4">

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="delivery" value="1" {{ old('delivery', $restaurante->delivery) ? 'checked' : '' }}>
                    🚚 Delivery
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="retirada" value="1" {{ old('retirada', $restaurante->retirada) ? 'checked' : '' }}>
                    🛍️ Retirada no balcão
                </label>

                <label class="flex items-center gap-3">
                    <input id="consumo_local" type="checkbox" name="consumo_local" value="1" {{ old('consumo_local', $restaurante->consumo_local) ? 'checked' : '' }}>
                    🍽️ Consumo no local
                </label>

            </div>

            <div id="mesas" class="mt-5 {{ old('consumo_local', $restaurante->consumo_local) ? '' : 'hidden' }}">

                <label class="font-semibold">
                    Quantidade de mesas
                </label>

                <input type="number" name="quantidade_mesas" min="1" class="w-full border rounded-lg p-3 mt-2"
                    value="{{ old('quantidade_mesas', $restaurante->quantidade_mesas) }}">

            </div>

            <hr class="my-8">

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar Alterações
            </button>
        </form>

    </div>
    <script>
        document.getElementById('cep').addEventListener('blur', async function () {
            let cep = this.value.replace(/\D/g, '');

            if (cep.length !== 8) return;

            let response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            let data = await response.json();

            if (data.erro) return;

            document.getElementById('endereco').value = data.logradouro;
            document.getElementById('bairro').value = data.bairro;
            document.getElementById('cidade').value = data.localidade;
            document.getElementById('estado').value = data.uf;
        });

        function mascaraDocumento(campo) {
            let valor = campo.value.replace(/\D/g, '');

            if (valor.length <= 11) {
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
                valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
                valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
            }

            campo.value = valor.substring(0, 18);
        }
        const consumo = document.getElementById('consumo_local');
        const mesas = document.getElementById('mesas');

        if (consumo) {
            consumo.addEventListener('change', function () {
                mesas.classList.toggle('hidden', !this.checked);
            });
        }

        function mascaraHora(campo) {
            let valor = campo.value.replace(/\D/g, '');

            if (valor.length > 4) {
                valor = valor.substring(0, 4);
            }

            if (valor.length >= 3) {
                valor = valor.substring(0, 2) + ':' + valor.substring(2);
            }

            campo.value = valor;
        }
    </script>

</x-rimafood.layout>