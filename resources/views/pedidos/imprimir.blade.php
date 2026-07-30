<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $pedido->numero_pedido ?? $pedido->id  }}</title>

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
        #{{ $pedido->numero_pedido ?? $pedido->id  }}
    </p>

    <p>
        <strong>Cliente:</strong>
        {{ $pedido->cliente->nome }}
    </p>

    @if($pedido->cliente?->telefone)

        <p>
            <strong>Telefone:</strong>

            {{ $pedido->cliente->telefone }}

        </p>

    @endif

    <p>
        <strong>Data:</strong>
        {{ $pedido->created_at->format('d/m/Y H:i') }}
    </p>

    <p>

        <strong>Entrega:</strong>

        {{ ucfirst($pedido->tipo_entrega) }}

    </p>

    @if($pedido->endereco_entrega)

        <p>

            {{ $pedido->endereco_entrega }}

        </p>

    @endif

    <p>

        <strong>Pagamento:</strong>

        {{ ucfirst($pedido->forma_pagamento) }}

    </p>

    <hr>

    @foreach($pedido->itens as $item)

        <div style="margin-bottom:12px;">

            <strong>
                {{ $item->quantidade }}x
                {{ $item->produto->nome }}
            </strong>

            <span style="float:right">
                R$ {{ number_format($item->preco_unitario * $item->quantidade, 2, ',', '.') }}
            </span>

            @if($item->observacao)

                <div style="
                                                    margin-left:12px;
                                                    margin-top:4px;
                                                    white-space:pre-line;
                                                    font-size:13px;
                                                ">
                    {{ $item->observacao }}
                </div>

            @endif

        </div>

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

    <hr>

    <p>

        Subtotal

        <span style="float:right">

            R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}

        </span>

    </p>

    @if($pedido->taxa_entrega > 0)

        <p>

            Entrega

            <span style="float:right">

                R$ {{ number_format($pedido->taxa_entrega, 2, ',', '.') }}

            </span>

        </p>

    @endif

    <hr>

    <div class="total">

        TOTAL

        <span style="float:right">

            R$ {{ number_format($pedido->total, 2, ',', '.') }}

        </span>

    </div>

    <hr>

    @if($pedido->token)

        <hr>

        <p>

            Token:

            <strong>{{ $pedido->token }}</strong>

        </p>

    @endif

    <div class="center">Obrigado ❤️</div>
</body>

</html>