<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <h1 class="text-4xl font-bold mb-8">

            🏪 Novo Restaurante

        </h1>

        <form action="{{ route('restaurantes.store') }}" method="POST" class="space-y-4">

            @csrf

            <input type="text" name="nome" placeholder="Nome do restaurante" class="w-full border rounded-lg p-3"
                required>

            <input type="text" name="telefone" placeholder="Telefone" class="w-full border rounded-lg p-3">

            <input type="text" name="documento" id="documento" placeholder="CPF ou CNPJ" maxlength="18"
                oninput="mascaraDocumento(this)" class="w-full border rounded-lg p-3">

            <input type="email" name="email" placeholder="Email" class="w-full border rounded-lg p-3">

            <input type="text" id="cep" name="cep" placeholder="CEP" class="w-full border rounded-lg p-3">

            <input type="text" id="endereco" name="endereco" placeholder="Endereço"
                class="w-full border rounded-lg p-3">

            <input type="text" id="numero" name="numero" placeholder="Número" class="w-full border rounded-lg p-3">

            <input type="text" id="complemento" name="complemento" placeholder="Complemento"
                class="w-full border rounded-lg p-3">

            <input type="text" id="bairro" name="bairro" placeholder="Bairro" class="w-full border rounded-lg p-3">

            <input type="text" id="cidade" name="cidade" placeholder="Cidade" class="w-full border rounded-lg p-3">

            <input type="text" id="estado" name="estado" placeholder="Estado" class="w-full border rounded-lg p-3">

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4"> 🍽 Atendimento </h2>

            <p class="text-gray-500 mb-5"> Como este estabelecimento atende seus clientes? </p>

            <div class="space-y-4">

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="delivery" value="1" checked> 🚚 Delivery
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="retirada" value="1" checked>
                    🛍 Retirada no balcão
                </label>

                <label class="flex items-center gap-3">
                    <input id="consumo_local" type="checkbox" name="consumo_local" value="1">

                    🍽 Consumo no local
                </label>

            </div>

            <div id="mesas" class="mt-5 hidden">

                <label class="font-semibold">
                    Quantidade de mesas
                </label>

                <input type="number" name="quantidade_mesas" class="w-full border rounded-lg p-3 mt-2" value="20"
                    min="1">
            </div>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">Salvar</button>

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

        consumo.addEventListener('change', () => {

            mesas.classList.toggle('hidden', !consumo.checked);

        });
    </script>

</x-rimafood.layout>