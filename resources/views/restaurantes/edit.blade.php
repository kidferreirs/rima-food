<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">✏️ Editar Restaurante</h1>

            <a href="{{ route('restaurantes.index') }}" class="text-gray-600 hover:underline">
                ← Voltar
            </a>
        </div>

        <form action="{{ route('restaurantes.update', $restaurante) }}" method="POST" class="space-y-4">
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

            <h2 class="text-2xl font-bold mb-5">
                📱 Cardápio Digital
            </h2>

            <div class="bg-white border rounded-2xl shadow-sm p-6">

                <p class="text-gray-500 mb-2">Link do seu cardápio</p>

                <div class="flex gap-2">
                    <input id="linkMenu" type="text" readonly value="{{ $linkMenu }}"
                        class="flex-1 border rounded-xl p-3 bg-gray-50">
                    <button type="button" onclick="copiarLink()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 rounded-xl">
                        Copiar
                    </button>
                </div>

                <div class="flex justify-center my-8">

                    {!! QrCode::size(220)->margin(2)->generate($linkMenu) !!}

                </div>

                <div class="grid grid-cols-2 gap-4">

                    <button type="button" onclick="compartilharMenu()"
                        class="bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl">
                        📲 Compartilhar
                    </button>

                    <button type="button" onclick="baixarQRCode()"
                        class="bg-slate-700 hover:bg-slate-800 text-white py-3 rounded-xl">
                        ⬇️ Baixar QR
                    </button>
                </div>
            </div>

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

        function copiarLink() {
            const campo = document.getElementById('linkMenu');
            campo.select();
            campo.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(campo.value);
            alert('✅ Link copiado!');
        }

        function compartilharMenu() {

            if (navigator.share) {
                navigator.share({
                    title: 'Cardápio Digital',
                    text: 'Confira nosso cardápio!',
                    url: document.getElementById('linkMenu').value
                });

            } else {
                copiarLink();
            }
        }

        function baixarQRCode() {

            const svg = document.querySelector('svg');
            const svgData = new XMLSerializer().serializeToString(svg);
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = function () {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                const a = document.createElement('a');
                a.download = 'qrcode-cardapio.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
        }
    </script>

</x-rimafood.layout>