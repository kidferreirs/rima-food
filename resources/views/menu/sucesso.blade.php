<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido recebido</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100">

    <div class="max-w-md mx-auto bg-white min-h-screen pb-10">

        <div class="relative">
            <div class="h-40 overflow-hidden">
                @if($pedido->restaurante->banner)
                    <img src="{{ Storage::url($pedido->restaurante->banner) }}" class="w-full h-full object-cover">
                @else
                    <img src="{{ asset('images/menu/cover1.png') }}" class="w-full h-full object-cover">
                @endif
            </div>

            <div
                class="absolute -bottom-8 left-6 w-20 h-20 bg-white rounded-3xl shadow-lg flex items-center justify-center overflow-hidden">
                @if($pedido->restaurante->logo)
                    <img src="{{ Storage::url($pedido->restaurante->logo) }}" class="w-full h-full object-cover">
                @else
                    🍔
                @endif
            </div>
        </div>

        <div class="px-6 pt-12">

            <h1 class="text-3xl font-extrabold text-slate-900"> {{ $pedido->restaurante->nome }} </h1>
            <p class="text-slate-500 mt-1"> 🎉 Pedido recebido com sucesso! </p>

            <div class="bg-slate-50 rounded-3xl p-5 mt-6 border">
                <div class="text-sm text-slate-500 font-bold"> Pedido </div>

                <div class="text-3xl font-extrabold text-slate-900 mt-1"> #{{ $pedido->token }} </div>

                <div class="mt-5 flex justify-between">
                    <span class="text-slate-500">Status</span>
                    <span class="font-bold text-green-600">🟢 Recebido</span>
                </div>

                <div class="mt-3 flex justify-between">
                    <span class="text-slate-500">Tipo</span>
                    <span class="font-bold">
                        @if($pedido->tipo_entrega === 'retirada')
                            Retirada no balcão
                        @elseif($pedido->tipo_entrega === 'balcao')
                            Consumo no local
                        @else
                            Delivery
                        @endif
                    </span>
                </div>

                <div class="mt-3 flex justify-between">
                    <span class="text-slate-500">Pagamento</span>
                    <span class="font-bold">
                        @if($pedido->forma_pagamento === 'pix')
                            Pix
                        @elseif($pedido->forma_pagamento === 'dinheiro')
                            Dinheiro
                        @elseif($pedido->forma_pagamento === 'credito')
                            Cartão de Crédito
                        @elseif($pedido->forma_pagamento === 'debito')
                            Cartão de Débito
                        @else
                            Não informado
                        @endif
                    </span>
                </div>

                <div class="mt-3 flex justify-between">
                    <span class="text-slate-500">Tempo estimado</span>
                    <span class="font-bold">{{ $pedido->restaurante->tempo_medio ?? 30 }} min</span>
                </div>

                <div class="mt-3 flex justify-between">
                    <span class="text-slate-500">Total</span>
                    <span class="font-bold">
                        R$ {{ number_format($pedido->total, 2, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ route('menu.show', $pedido->restaurante->slug) }}"
                    class="block text-center bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl py-4">
                    🍔 Voltar ao Cardápio
                </a>

                <button disabled class="w-full border font-bold rounded-2xl py-4 text-slate-400 bg-slate-50">
                    Acompanhar Pedido em breve
                </button>
            </div>

            <p class="text-center text-xs text-slate-400 mt-8"> Desenvolvido por Rimatech </p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const cartKey = 'rima-cart-{{ $pedido->restaurante->slug }}';

            localStorage.removeItem(cartKey);

        });
    </script>
</body>

</html>