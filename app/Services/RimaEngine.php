<?php

namespace App\Services;

use App\Models\ConversaWhatsapp;
use App\Services\PedidoAutomaticoService;

class RimaEngine
{
    public function processar(ConversaWhatsapp $conversa, string $mensagem): string
    {
        $mensagem = strtolower(trim($mensagem));

        $historico = $conversa->historico ?? [];

        $historico[] = [
            'autor' => 'cliente',
            'mensagem' => $mensagem,
            'hora' => now()->format('H:i'),
        ];

        $resposta = $this->gerarResposta($conversa, $mensagem);

        $historico[] = [
            'autor' => 'rima',
            'mensagem' => $resposta,
            'hora' => now()->format('H:i'),
        ];

        $conversa->update([
            'ultima_mensagem' => $mensagem,
            'ultima_interacao' => now(),
            'historico' => $historico,
        ]);

        return $resposta;
    }
    private function gerarResposta(ConversaWhatsapp $conversa, string $mensagem): string
    {
        // FINALIZAR PEDIDO
        if (str_contains($mensagem, 'finalizar')) {
            $conversa->update([
                'estado' => 'tipo_entrega',
            ]);
            return "Como deseja receber seu pedido?\n\n🏪 Balcão\n🛍️ Retirada\n🚚 Entrega";
        }

        // TIPO ENTREGA

        if (
            $conversa->estado === 'tipo_entrega'
            &&
            str_contains($mensagem, 'entrega')
        ) {
            $conversa->update([
                'estado' => 'endereco',
                'tipo_entrega' => 'entrega',
            ]);

            return "📍 Perfeito!\n\n Me envie o endereço.";
        }


        // ENDEREÇO

        if ($conversa->estado === 'endereco') {

            $conversa->update([
                'estado' => 'forma_pagamento',

                'endereco_entrega' => $mensagem,
            ]);

            return "Qual a forma de pagamento?\n\n💵 Dinheiro\n💳 Cartão\n🏦 Pix";
        }


        // PAGAMENTO

        if (str_contains($mensagem, 'pix')) {

            $conversa->update([
                'estado' => 'confirmacao',

                'forma_pagamento' => 'pix',
            ]);

            $itens = '';

            if ($conversa->carrinho) {

                foreach ($conversa->carrinho as $item) {

                    $itens .= "🍔 {$item}\n";
                }
            }

            return
                "📋 Resumo do pedido\n\n"

                . "🛒 Itens:\n"

                . $itens . "\n"

                . "🚚 {$conversa->endereco_entrega}\n\n"

                . "💳 Pix\n\n"

                . "Deseja confirmar o pedido?\n\n"

                . "✅ Sim\n"

                . "❌ Não";
        }


        // ESTADO INICIAL

        if ($conversa->estado === 'montando_pedido') {

            return 'Perfeito 😄 Deseja adicionar mais algum item ou quer finalizar o pedido?';
        }

if (
    in_array($conversa->estado, ['confirmacao', 'pedido_finalizado'])
    && $mensagem === 'sim'
) {
    if ($conversa->pedido_id) {
        return "✅ Pedido já confirmado!\n\n🆔 Pedido #{$conversa->pedido_id}";
    }

    $pedidoService = new PedidoAutomaticoService();

    $pedido = $pedidoService->criar($conversa);

    $conversa->update([
        'estado' => 'pedido_finalizado',
        'pedido_confirmado' => true,
        'pedido_id' => $pedido->id,
    ]);

    return
        "🎉 Pedido realizado com sucesso!\n\n"
        ."🆔 Pedido #{$pedido->id}\n\n"
        ."⏱️ Tempo médio: 30 minutos\n\n"
        ."🍔 Obrigado por escolher a Hamburgueria do Amir 💙";
}

        return 'Entendi 😄';
    }
}