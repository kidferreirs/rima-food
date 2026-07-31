<?php

namespace App\Services\AI\Context;

use App\Models\Cliente;
use App\Models\ConversaWhatsapp;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Restaurante;
use App\Services\AI\Knowledge\KnowledgeEngine;

class AIContextBuilder
{
    public function __construct(
        protected KnowledgeEngine $knowledge
    ) {
    }

    public function build(
        Restaurante $restaurante,
        ConversaWhatsapp $conversa
    ): AIContext {
        $telefone = $this->normalizarTelefone(
            $conversa->telefone
        );

        $historico = collect($conversa->historico ?? [])
            ->take(-20)
            ->values()
            ->all();

        $cliente = $this->buscarCliente(
            $restaurante,
            $telefone
        );

        $totalPedidos = 0;
        $totalGasto = 0.0;
        $ultimoPedido = null;
        $preferencias = [];

        if ($cliente !== null) {
            $totalPedidos = Pedido::query()
                ->where('restaurante_id', $restaurante->id)
                ->where('cliente_id', $cliente->id)
                ->count();

            $totalGasto = (float) Pedido::query()
                ->where('restaurante_id', $restaurante->id)
                ->where('cliente_id', $cliente->id)
                ->sum('total');

            $ultimoPedidoModel = Pedido::query()
                ->with([
                    'itens.produto',
                ])
                ->where('restaurante_id', $restaurante->id)
                ->where('cliente_id', $cliente->id)
                ->latest('created_at')
                ->first();

            $ultimoPedido = $this->montarUltimoPedido(
                $ultimoPedidoModel
            );

            $preferencias = $this->montarPreferencias(
                $restaurante,
                $cliente
            );
        }

        $dadosCliente = [
            'cadastrado' => $cliente !== null,
            'id' => $cliente?->id,
            'nome' => $cliente?->nome
                ?: $conversa->nome_cliente
                ?: 'Cliente WhatsApp',
            'telefone' => $telefone,
            'email' => $cliente?->email,
            'observacao' => $cliente?->observacao,
            'total_pedidos' => $totalPedidos,
            'total_gasto' => $totalGasto,
            'ultima_compra_em' => $ultimoPedido['data'] ?? null,
        ];

        $resumo = $this->montarResumo(
            $dadosCliente,
            $preferencias,
            $ultimoPedido
        );

        return new AIContext(
            restaurante: [
                'id' => $restaurante->id,
                'nome' => $restaurante->nome,
                'slug' => $restaurante->slug,
                'delivery' => (bool) $restaurante->delivery,
                'retirada' => (bool) $restaurante->retirada,
                'consumo_local' => (bool) $restaurante->consumo_local,
                'tempo_medio' => (int) $restaurante->tempo_medio,
                'abre_as' => $restaurante->abre_as,
                'fecha_as' => $restaurante->fecha_as,
            ],

            cliente: $dadosCliente,

            categorias: $this->knowledge
                ->categorias()
                ->listar($restaurante),

            produtos: $this->knowledge
                ->produtos()
                ->disponiveis($restaurante),

            historico: $historico,

            pedidoAtual: $conversa->carrinho ?? [],

            ultimoPedido: $ultimoPedido,

            preferencias: $preferencias,

            resumo: $resumo,
        );
    }

    private function buscarCliente(
        Restaurante $restaurante,
        string $telefone
    ): ?Cliente {
        return Cliente::query()
            ->where('restaurante_id', $restaurante->id)
            ->where(function ($query) use ($telefone) {
                $query
                    ->where('telefone', $telefone)
                    ->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                        [$telefone]
                    );
            })
            ->first();
    }

    private function montarUltimoPedido(
        ?Pedido $pedido
    ): ?array {
        if ($pedido === null) {
            return null;
        }

        return [
            'id' => $pedido->id,
            'numero' => $pedido->numero_pedido,
            'status' => $pedido->status,
            'origem' => $pedido->origem,
            'tipo_entrega' => $pedido->tipo_entrega,
            'forma_pagamento' => $pedido->forma_pagamento,
            'endereco_entrega' => $pedido->endereco_entrega,
            'subtotal' => (float) $pedido->subtotal,
            'total' => (float) $pedido->total,
            'data' => $pedido->created_at?->toIso8601String(),

            'itens' => $pedido->itens
                ->map(function (ItemPedido $item): array {
                    return [
                        'produto_id' => $item->produto_id,
                        'nome' => $item->produto?->nome
                            ?? 'Produto não disponível',
                        'quantidade' => (int) $item->quantidade,
                        'preco_unitario' => (float) $item->preco_unitario,
                        'observacao' => $item->observacao,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function montarPreferencias(
        Restaurante $restaurante,
        Cliente $cliente
    ): array {
        $formaPagamento = Pedido::query()
            ->select('forma_pagamento')
            ->selectRaw('COUNT(*) AS total')
            ->where('restaurante_id', $restaurante->id)
            ->where('cliente_id', $cliente->id)
            ->whereNotNull('forma_pagamento')
            ->where('forma_pagamento', '!=', '')
            ->groupBy('forma_pagamento')
            ->orderByDesc('total')
            ->value('forma_pagamento');

        $tipoEntrega = Pedido::query()
            ->select('tipo_entrega')
            ->selectRaw('COUNT(*) AS total')
            ->where('restaurante_id', $restaurante->id)
            ->where('cliente_id', $cliente->id)
            ->whereNotNull('tipo_entrega')
            ->where('tipo_entrega', '!=', '')
            ->groupBy('tipo_entrega')
            ->orderByDesc('total')
            ->value('tipo_entrega');

        $produtoFavorito = ItemPedido::query()
            ->select([
                'produtos.id',
                'produtos.nome',
            ])
            ->selectRaw(
                'SUM(item_pedidos.quantidade) AS total_quantidade'
            )
            ->join(
                'pedidos',
                'pedidos.id',
                '=',
                'item_pedidos.pedido_id'
            )
            ->join(
                'produtos',
                'produtos.id',
                '=',
                'item_pedidos.produto_id'
            )
            ->where(
                'pedidos.restaurante_id',
                $restaurante->id
            )
            ->where(
                'pedidos.cliente_id',
                $cliente->id
            )
            ->groupBy(
                'produtos.id',
                'produtos.nome'
            )
            ->orderByDesc('total_quantidade')
            ->first();

        return [
            'forma_pagamento' => $formaPagamento,
            'tipo_entrega' => $tipoEntrega,

            'produto_favorito' => $produtoFavorito
                ? [
                    'id' => (int) $produtoFavorito->id,
                    'nome' => $produtoFavorito->nome,
                    'quantidade_pedida' => (int) $produtoFavorito
                        ->total_quantidade,
                ]
                : null,
        ];
    }

    private function montarResumo(
        array $cliente,
        array $preferencias,
        ?array $ultimoPedido
    ): string {
        $nome = $cliente['nome'] ?? 'Cliente';

        if (!$cliente['cadastrado']) {
            return "{$nome} ainda não possui cadastro ou pedidos anteriores neste estabelecimento.";
        }

        $totalPedidos = (int) (
            $cliente['total_pedidos'] ?? 0
        );

        if ($totalPedidos === 0) {
            return "{$nome} está cadastrado, mas ainda não possui pedidos anteriores.";
        }

        $partes = [];

        $partes[] = "{$nome} é um cliente recorrente e já realizou {$totalPedidos} "
            . ($totalPedidos === 1 ? 'pedido' : 'pedidos') . '.';

        $formaPagamento =
            $preferencias['forma_pagamento'] ?? null;

        if ($formaPagamento) {
            $partes[] =
                'Costuma pagar por '
                . $this->formatarTexto($formaPagamento)
                . '.';
        }

        $tipoEntrega =
            $preferencias['tipo_entrega'] ?? null;

        if ($tipoEntrega) {
            $partes[] =
                'Costuma escolher '
                . $this->formatarTexto($tipoEntrega)
                . '.';
        }

        $produtoFavorito =
            $preferencias['produto_favorito']['nome']
            ?? null;

        if ($produtoFavorito) {
            $partes[] =
                "Seu produto mais pedido é {$produtoFavorito}.";
        }

        if ($ultimoPedido !== null) {
            $nomesItens = collect(
                $ultimoPedido['itens'] ?? []
            )
                ->map(
                    fn(array $item): string =>
                    "{$item['quantidade']}x {$item['nome']}"
                )
                ->implode(', ');

            if ($nomesItens !== '') {
                $partes[] =
                    "No último pedido escolheu: {$nomesItens}.";
            }
        }

        $totalGasto = (float) (
            $cliente['total_gasto'] ?? 0
        );

        if ($totalGasto > 0) {
            $partes[] =
                'Total registrado em pedidos: R$ '
                . number_format(
                    $totalGasto,
                    2,
                    ',',
                    '.'
                )
                . '.';
        }

        return implode(' ', $partes);
    }

    private function normalizarTelefone(
        ?string $telefone
    ): string {
        return preg_replace(
            '/\D+/',
            '',
            (string) $telefone
        ) ?? '';
    }

    private function formatarTexto(
        string $valor
    ): string {
        return ucfirst(
            str_replace(
                '_',
                ' ',
                $valor
            )
        );
    }
}