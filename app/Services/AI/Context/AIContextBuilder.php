<?php

namespace App\Services\AI\Context;

use App\Models\ConversaWhatsapp;
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

        $historico = collect($conversa->historico ?? [])
            ->take(-20)
            ->values()
            ->all();

        return new AIContext(

            restaurante: [

                'id' => $restaurante->id,
                'nome' => $restaurante->nome,
                'slug' => $restaurante->slug,
                'delivery' => $restaurante->delivery,
                'retirada' => $restaurante->retirada,
                'tempo_medio' => $restaurante->tempo_medio,

            ],

            cliente: [

                'nome' => $conversa->nome_cliente,
                'telefone' => $conversa->telefone,

            ],

            categorias:
            $this->knowledge
                ->categorias()
                ->listar($restaurante),

            produtos:
            $this->knowledge
                ->produtos()
                ->disponiveis($restaurante),

            historico: $historico,

            pedidoAtual:
            $conversa->carrinho ?? [],

            ultimoPedido: null,

            preferencias: [],

            resumo: '',
        );
    }
}