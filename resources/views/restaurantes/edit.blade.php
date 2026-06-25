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
    </script>

</x-rimafood.layout>