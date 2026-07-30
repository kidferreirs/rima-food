<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\ConversaWhatsapp;

class PedidoAutomaticoService
{
    public function criar(ConversaWhatsapp $conversa)
    {
        $cliente = Cliente::firstOrCreate(
            [
                'restaurante_id' => $conversa->restaurante_id,
                'telefone' => $conversa->telefone,
            ],
            [
                'nome' => $conversa->nome_cliente ?? 'Cliente WhatsApp',
                'email' => null,
                'observacao' => 'Cliente criado automaticamente pela Rima.',
                'ativo' => true,
            ]
        );

        $total = 0;
        $itensPedido = [];

        foreach (($conversa->carrinho ?? []) as $itemCarrinho) {
            $nomeProduto = preg_replace('/^\d+x\s*/i', '', $itemCarrinho);
            $nomeProduto = trim($nomeProduto);

            $produto = Produto::whereHas('categoria', function ($query) use ($conversa) {
                    $query->where('restaurante_id', $conversa->restaurante_id);
                })
                ->where('nome', 'like', "%{$nomeProduto}%")
                ->first();

            if (!$produto) {
                continue;
            }

            preg_match('/^(\d+)x/i', $itemCarrinho, $matches);

            $quantidade = isset($matches[1])
                ? (int) $matches[1]
                : 1;

            $subtotal = $produto->preco * $quantidade;
            $total += $subtotal;

            $itensPedido[] = [
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'preco_unitario' => $produto->preco,
            ];
        }

        $taxaEntrega = $conversa->tipo_entrega === 'entrega'
            ? 0
            : 0;

        $total += $taxaEntrega;

        $pedido = Pedido::create([
            'restaurante_id' => $conversa->restaurante_id,
            'cliente_id' => $cliente->id,
            'total' => $total,
            'status' => 'novo',
            'origem' => 'whatsapp',
            'forma_pagamento' => $conversa->forma_pagamento,
            'tipo_entrega' => $conversa->tipo_entrega,
            'taxa_entrega' => $taxaEntrega,
            'endereco_entrega' => $conversa->endereco_entrega,
            'observacao' => 'Pedido criado automaticamente pela Rima',
            'novo_em' => now(),
        ]);

        foreach ($itensPedido as $item) {
            $pedido->itens()->create($item);
        }

        (new PedidoEventService())->novoPedido($pedido);

        return $pedido;
    }
}