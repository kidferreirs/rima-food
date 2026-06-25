<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $pedido->id }}</title>

    <style>
        body {
            font-family: Arial;
            width: 300px;
            margin: auto;
            padding: 20px;
        }

        hr {
            margin: 15px 0;
        }

        .center {
            text-align: center;
        }

        .total {
            font-size: 24px;
            font-weight: bold;
        }
    </style>

    <script>

        window.onload = function () {
            window.print();
        }
    </script>

</head>

<body>

    <div class="center">

        <h1>🍔 Rima Food</h1>

        <h3>{{ $pedido->restaurante->nome }}</h3>

        @if($pedido->restaurante->documento)

            <p> 🧾 {{ $pedido->restaurante->documento }} </p>

        @endif

        @if($pedido->restaurante->cidade)
            <p>
                📍 {{ $pedido->restaurante->cidade }}

                @if($pedido->restaurante->estado)

                    - {{ $pedido->restaurante->estado }}

                @endif
            </p>
        @endif

    </div>

    <hr>

    <p>
        <strong>Pedido:</strong>
        #{{ $pedido->id }}
    </p>

    <p>
        <strong>Cliente:</strong>
        {{ $pedido->cliente->nome }}
    </p>

    <p>
        <strong>Data:</strong>
        {{ $pedido->created_at->format('d/m/Y H:i') }}
    </p>

    <hr>

    @foreach($pedido->itens as $item)

        <p>
            {{ $item->quantidade }}x
            {{ $item->produto->nome }}
        </p>

    @endforeach

    <hr>

    @if($pedido->observacao)

        <p>
            <strong>Observação:</strong>
        </p>

        <p>
            {{ $pedido->observacao }}
        </p>

        <hr>

    @endif

    <div class="total">R$ {{ number_format($pedido->total, 2, ',', '.') }} </div>

    <hr>
    
    <div class="center">Obrigado ❤️</div>
</body>
</html>