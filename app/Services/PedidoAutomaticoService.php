<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ConversaWhatsapp;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Support\Str;

class PedidoAutomaticoService
{
    public function criar(ConversaWhatsapp $conversa): Pedido
    {
        /*
        |--------------------------------------------------------------------------
        | Evitar pedido duplicado
        |--------------------------------------------------------------------------
        */

        if ($conversa->pedido_id) {
            return Pedido::findOrFail($conversa->pedido_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Criar ou localizar cliente
        |--------------------------------------------------------------------------
        */

        $cliente = Cliente::firstOrCreate(
            [
                'restaurante_id' => $conversa->restaurante_id,
                'telefone' => $conversa->telefone,
            ],
            [
                'nome' => $conversa->nome_cliente
                    ?? 'Cliente WhatsApp',

                'email' => null,

                'observacao' =>
                    'Cliente criado automaticamente pela Rima.',

                'ativo' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Montar itens usando o novo contexto inteligente
        |--------------------------------------------------------------------------
        */

        $itensContexto = data_get(
            $conversa->contexto_ia,
            'pedido.itens',
            []
        );

        $itensPedido = [];
        $subtotalPedido = 0;

        if (!empty($itensContexto)) {
            foreach ($itensContexto as $itemContexto) {
                $produtoId = data_get(
                    $itemContexto,
                    'produto_id'
                );

                $produto = Produto::where('id', $produtoId)
                    ->whereHas(
                        'categoria',
                        function ($query) use ($conversa) {
                            $query->where(
                                'restaurante_id',
                                $conversa->restaurante_id
                            );
                        }
                    )
                    ->first();

                if (!$produto) {
                    continue;
                }

                $quantidade = max(
                    1,
                    (int) data_get(
                        $itemContexto,
                        'quantidade',
                        1
                    )
                );

                $subtotalItem = (float) data_get(
                    $itemContexto,
                    'subtotal',
                    $produto->preco * $quantidade
                );

                /*
                 * O preço unitário já inclui opções e adicionais.
                 */
                $precoUnitarioFinal =
                    $subtotalItem / $quantidade;

                $observacao = $this->montarObservacaoItem(
                    $itemContexto
                );

                $itensPedido[] = [
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoUnitarioFinal,
                    'observacao' => $observacao,
                ];

                $subtotalPedido += $subtotalItem;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Segurança para conversas antigas
        |--------------------------------------------------------------------------
        |
        | Se não existir pedido no contexto_ia, usamos o carrinho antigo.
        |
        */

        if (empty($itensPedido)) {
            foreach (
                ($conversa->carrinho ?? [])
                as $itemCarrinho
            ) {
                $nomeProduto = preg_replace(
                    '/^\d+x\s*/i',
                    '',
                    $itemCarrinho
                );

                $nomeProduto = trim($nomeProduto);

                $produto = Produto::whereHas(
                    'categoria',
                    function ($query) use ($conversa) {
                        $query->where(
                            'restaurante_id',
                            $conversa->restaurante_id
                        );
                    }
                )
                    ->where(
                        'nome',
                        'like',
                        "%{$nomeProduto}%"
                    )
                    ->first();

                if (!$produto) {
                    continue;
                }

                preg_match(
                    '/^(\d+)x/i',
                    $itemCarrinho,
                    $matches
                );

                $quantidade = isset($matches[1])
                    ? (int) $matches[1]
                    : 1;

                $subtotalItem =
                    (float) $produto->preco
                    * $quantidade;

                $itensPedido[] = [
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' =>
                        (float) $produto->preco,

                    'observacao' => null,
                ];

                $subtotalPedido += $subtotalItem;
            }
        }

        if (empty($itensPedido)) {
            throw new \RuntimeException(
                'Não foi possível criar o pedido: '
                . 'nenhum item válido foi encontrado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Taxa de entrega
        |--------------------------------------------------------------------------
        */

        $taxaEntrega = 0;

        $totalPedido =
            $subtotalPedido + $taxaEntrega;

        /*
        |--------------------------------------------------------------------------
        | Criar pedido
        |--------------------------------------------------------------------------
        */

        $pedido = Pedido::create([
            'restaurante_id' => $conversa->restaurante_id,
            'cliente_id' => $cliente->id,
            'subtotal' => $subtotalPedido,
            'taxa_entrega' => $taxaEntrega,
            'total' => $totalPedido,
            'status' => 'novo',
            'origem' => 'whatsapp',
            'forma_pagamento' => $conversa->forma_pagamento,
            'tipo_entrega' => $conversa->tipo_entrega,
            'endereco_entrega' => $conversa->endereco_entrega,
            'observacao' => 'Pedido criado automaticamente pela Rima.',
            'token' => strtoupper(Str::random(10)),
            'novo_em' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Criar itens do pedido
        |--------------------------------------------------------------------------
        */

        foreach ($itensPedido as $itemPedido) {
            $pedido->itens()->create($itemPedido);
        }

        /*
        |--------------------------------------------------------------------------
        | Disparar evento do pedido
        |--------------------------------------------------------------------------
        */

        (new PedidoEventService())->novoPedido($pedido);

        return $pedido->load('itens');
    }

    private function montarObservacaoItem(
        array $item
    ): ?string {
        $linhas = [];

        /*
        |--------------------------------------------------------------------------
        | Opções selecionadas
        |--------------------------------------------------------------------------
        */

        $opcoes = data_get(
            $item,
            'opcoes_selecionadas',
            []
        );

        foreach ($opcoes as $opcao) {
            if (is_string($opcao)) {
                $linhas[] = $opcao;
                continue;
            }

            $grupo = data_get($opcao, 'grupo');
            $nome = data_get($opcao, 'nome');
            $valor = (float) data_get(
                $opcao,
                'valor',
                0
            );

            if (!$nome) {
                continue;
            }

            $texto = $grupo
                ? "{$grupo}: {$nome}"
                : $nome;

            if ($valor > 0) {
                $texto .= ' (+R$ '
                    . number_format(
                        $valor,
                        2,
                        ',',
                        '.'
                    )
                    . ')';
            }

            $linhas[] = $texto;
        }

        /*
        |--------------------------------------------------------------------------
        | Adicionais
        |--------------------------------------------------------------------------
        */

        $adicionais = data_get(
            $item,
            'adicionais',
            []
        );

        foreach ($adicionais as $adicional) {
            if (
                is_array($adicional)
                && (
                    data_get($adicional, 'grupo')
                    || data_get($adicional, 'grupo_id')
                )
            ) {
                continue;
            }

            if (is_string($adicional)) {
                $linhas[] = "Adicional: {$adicional}";
                continue;
            }

            $nome = data_get(
                $adicional,
                'nome'
            );

            $valor = (float) data_get(
                $adicional,
                'valor',
                0
            );

            if (!$nome) {
                continue;
            }

            $texto = "Adicional: {$nome}";

            if ($valor > 0) {
                $texto .= ' (+R$ '
                    . number_format(
                        $valor,
                        2,
                        ',',
                        '.'
                    )
                    . ')';
            }

            $linhas[] = $texto;
        }

        /*
        |--------------------------------------------------------------------------
        | Ingredientes removidos
        |--------------------------------------------------------------------------
        */

        $removidos = data_get(
            $item,
            'ingredientes_removidos',
            []
        );

        foreach ($removidos as $removido) {
            $nome = is_array($removido)
                ? data_get($removido, 'nome')
                : $removido;

            if ($nome) {
                $linhas[] = "Sem: {$nome}";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Observação livre
        |--------------------------------------------------------------------------
        */

        $observacao = trim(
            (string) data_get(
                $item,
                'observacao',
                ''
            )
        );

        if ($observacao !== '') {
            $linhas[] =
                "Observação: {$observacao}";
        }

        if (empty($linhas)) {
            return null;
        }

        return implode("\n", $linhas);
    }
}