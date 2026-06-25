<?php

namespace App\Services;

use App\Models\Notificacao;
use App\Models\Pedido;

class PedidoEventService
{
    public function novoPedido(Pedido $pedido): void
    {
        Notificacao::create([
            'restaurante_id' => $pedido->restaurante_id,
            'pedido_id' => $pedido->id,
            'tipo' => 'novo_pedido',
            'titulo' => 'Novo pedido recebido',
            'mensagem' => "Pedido #{$pedido->id} recebido via {$pedido->origem}.",
            'lida' => false,
        ]);
    }
}