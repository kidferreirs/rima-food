<x-rimafood.layout>

    <div class="max-w-5xl mx-auto p-8">

        <div class="flex justify-between items-center mb-8">

            <div>

                <h1 class="text-4xl font-bold">📱 Cardápio Digital</h1>

                <div class="text-center mt-3">

                    <p class="text-gray-500 mt-4">
                        Compartilhe este QR Code nas mesas, balcão ou redes sociais.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-10">

            <h2 class="text-3xl font-bold text-center">{{ $restaurante->nome }}</h2>

            <p class="text-center text-gray-500 mt-2">
                Escaneie o QR Code para acessar o cardápio.
            </p>

            <div class="flex justify-center my-10">

                <div class="bg-gray-50 rounded-2xl shadow-inner p-8 inline-block">

                    {!! QrCode::size(320)->margin(2)->generate($linkMenu) !!}

                </div>

            </div>

            <div class="mb-8">

                <label class="font-semibold">
                    Link do Cardápio
                </label>

                <div class="flex gap-3 mt-2">

                    <input id="linkMenu" readonly value="{{ $linkMenu }}"
                        class="flex-1 border rounded-xl p-3 bg-gray-50">

                    <button onclick="copiarLink()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded-xl">
                        Copiar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <button onclick="compartilharMenu()"
                    class="bg-green-500 hover:bg-green-600 text-white py-4 rounded-xl font-bold">
                    📲 Compartilhar
                </button>

                <button onclick="baixarQRCode()"
                    class="bg-slate-800 hover:bg-slate-900 text-white py-4 rounded-xl font-bold">
                    ⬇️ Baixar QR Code
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mt-10">

                <div class="bg-slate-50 rounded-xl p-5 text-center">

                    <p class="text-gray-500">🍔 Produtos</p>

                    <p class="text-3xl font-bold">

                        {{ $restaurante->produtos()->count() }}

                    </p>

                </div>

                <div class="bg-slate-50 rounded-xl p-5 text-center">

                    <p class="text-gray-500">📂 Categorias</p>

                    <p class="text-3xl font-bold">
                        {{ $restaurante->categorias()->count() }}
                    </p>
                </div>

                <div class="bg-slate-50 rounded-xl p-5 text-center">

                    <p class="text-gray-500">🌐 Status</p>

                    <p class="font-bold text-green-600">
                        Ativo
                    </p>
                </div>

                <div class="bg-slate-50 rounded-xl p-5 text-center">
                    <p class="text-gray-500">🔗 Link</p>

                    <p class="font-bold text-blue-600">
                        Público
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow mt-10 p-8">

                <h2 class="text-2xl font-bold">

                    👀 Pré-visualização

                </h2>

                <p class="text-gray-500 mt-2">

                    Veja exatamente como seu cliente visualizará o cardápio.

                </p>

                <a href="{{ $linkMenu }}" target="_blank"
                    class="mt-6 inline-block bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl">
                    🍔 Abrir Cardápio
                </a>

            </div>

            <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 text-white mt-10">

                <h2 class="text-2xl font-bold">

                    🚀 Em breve

                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-6">

                    <div>📄 Cartaz A4</div>

                    <div>🪧 Display de Mesa</div>

                    <div>🏷️ Etiquetas QR</div>

                    <div>🤖 Cardápio com IA</div>

                </div>

            </div>
        </div>
    </div>

    <script>

        function copiarLink() {

            navigator.clipboard.writeText(
                document.getElementById('linkMenu').value
            );

            alert('✅ Link copiado!');

        }

        function compartilharMenu() {

            if (navigator.share) {

                navigator.share({

                    title: '{{ $restaurante->nome }}',

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

                a.download = '{{ $restaurante->slug }}-qrcode.png';

                a.href = canvas.toDataURL('image/png');

                a.click();

            };

            img.src = 'data:image/svg+xml;base64,' + btoa(svgData);

        }

    </script>

</x-rimafood.layout>