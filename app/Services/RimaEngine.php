<?php

namespace App\Services;

use App\Models\ConversaWhatsapp;
use App\Models\Restaurante;
use App\Services\AI\Context\AIContext;
use App\Services\AI\Context\AIContextBuilder;
use App\Services\AI\Conversation\ConversationContext;
use App\Services\AI\Conversation\ConversationEngine;
use App\Services\AI\Conversation\Order;
use App\Services\Delivery\Address;
use App\Services\Delivery\DeliveryCalculator;
use App\Services\Delivery\ViaCepService;
use App\Models\Cliente;
use Illuminate\Support\Str;


class RimaEngine
{
    public function __construct(
        private readonly ConversationEngine $conversationEngine,
        private readonly PedidoAutomaticoService $pedidoService,
        private readonly AIContextBuilder $aiContextBuilder,
        private readonly ViaCepService $viaCep,
        private readonly DeliveryCalculator $deliveryCalculator,
    ) {
    }

    public function processar(
        ConversaWhatsapp $conversa,
        string $mensagem
    ): string {
        $mensagem = trim($mensagem);

        $historico = $conversa->historico ?? [];

        $historico[] = [
            'autor' => 'cliente',
            'mensagem' => $mensagem,
            'hora' => now()->format('H:i'),
        ];

        $resposta = $this->gerarResposta(
            $conversa,
            $mensagem
        );

        $historico[] = [
            'autor' => 'rima',
            'mensagem' => $resposta,
            'hora' => now()->format('H:i'),
        ];

        $conversa->ultima_mensagem = $mensagem;
        $conversa->ultima_interacao = now();
        $conversa->historico = $historico;
        $conversa->save();

        return $resposta;
    }

    private function gerarResposta(
        ConversaWhatsapp $conversa,
        string $mensagem
    ): string {
        $texto = Str::of($mensagem)
            ->lower()
            ->ascii()
            ->trim()
            ->toString();

        /*
        |--------------------------------------------------------------------------
        | Confirmação do pedido
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $conversa->estado,
                ['confirmacao', 'pedido_finalizado'],
                true
            )
            && $this->ehConfirmacao($texto)
        ) {
            if ($conversa->pedido_id) {
                return
                    "✅ Pedido já confirmado!\n\n"
                    . "🆔 Pedido #{$conversa->pedido_id}";
            }

            $pedido = $this->pedidoService->criar($conversa);

            $conversa->estado = 'pedido_finalizado';
            $conversa->pedido_confirmado = true;
            $conversa->pedido_id = $pedido->id;
            $conversa->save();

            return
                "🎉 Pedido realizado com sucesso!\n\n"
                . "🆔 Pedido #{$pedido->id}\n\n"
                . "⏱️ Tempo médio: 30 minutos\n\n"
                . "🍔 Obrigado por escolher o restaurante 💙";
        }

        /*
        |--------------------------------------------------------------------------
        | Alterar pedido na confirmação
        |--------------------------------------------------------------------------
        */

        if (
            $conversa->estado === 'confirmacao'
            && $this->ehNegacao($texto)
        ) {
            $contexto = ConversationContext::fromArray(
                $conversa->contexto_ia
            );

            $contexto->estado =
                ConversationContext::ESTADO_MONTANDO_PEDIDO;

            $contexto->pedidoFinalizado = false;

            $conversa->estado = 'montando_pedido';
            $conversa->contexto_ia = $contexto->toArray();
            $conversa->save();

            return 'Certo! O que você deseja alterar no pedido?';
        }

        /*
        |--------------------------------------------------------------------------
        | Escolha do tipo de entrega
        |--------------------------------------------------------------------------
        */

        if ($conversa->estado === 'tipo_entrega') {
            if (Str::contains($texto, ['entrega', 'delivery'])) {
                $conversa->estado = 'cep_entrega';
                $conversa->tipo_entrega = 'entrega';
                $conversa->cep_entrega = null;
                $conversa->endereco_entrega = null;
                $conversa->taxa_entrega = 0;
                $conversa->save();

                return
                    "🚚 Perfeito! Vamos calcular sua entrega.\n\n"
                    . "📍 Informe seu CEP:";
            }

            if (Str::contains($texto, ['retirada', 'retirar', 'buscar'])) {
                $conversa->estado = 'forma_pagamento';
                $conversa->tipo_entrega = 'retirada';
                $conversa->cep_entrega = null;
                $conversa->endereco_entrega = null;
                $conversa->taxa_entrega = 0;
                $conversa->save();

                return $this->perguntaPagamento();
            }

            if (Str::contains($texto, ['balcao', 'local'])) {
                $conversa->estado = 'forma_pagamento';
                $conversa->tipo_entrega = 'balcao';
                $conversa->cep_entrega = null;
                $conversa->endereco_entrega = null;
                $conversa->taxa_entrega = 0;
                $conversa->save();

                return $this->perguntaPagamento();
            }

            return
                "Como deseja receber seu pedido?\n\n"
                . "🏪 Balcão\n"
                . "🛍️ Retirada\n"
                . "🚚 Entrega";
        }

        /*
        |--------------------------------------------------------------------------
        | CEP da entrega
        |--------------------------------------------------------------------------
        */

        if ($conversa->estado === 'cep_entrega') {
            $cep = preg_replace('/\D/', '', $mensagem);

            if (strlen($cep) !== 8) {
                return
                    "❌ Não consegui identificar esse CEP.\n\n"
                    . "Digite um CEP válido com 8 números.";
            }

            $dadosCep = $this->viaCep->buscar($cep);

            if ($dadosCep === null) {
                return
                    "❌ Não encontrei esse CEP.\n\n"
                    . "Confira e envie novamente.";
            }

            $conversa->cep_entrega = $dadosCep['cep'];
            $conversa->estado = 'numero_entrega';
            $conversa->save();

            $endereco = $dadosCep['logradouro'];

            if (!empty($dadosCep['bairro'])) {
                $endereco .= ' - ' . $dadosCep['bairro'];
            }

            return
                "📍 Encontrei:\n\n"
                . "{$endereco}\n"
                . "{$dadosCep['cidade']} - {$dadosCep['estado']}\n\n"
                . "Qual o número do endereço?";
        }

        /*
        |--------------------------------------------------------------------------
        | Número do endereço e cálculo da entrega
        |--------------------------------------------------------------------------
        */

        if ($conversa->estado === 'numero_entrega') {
            $numero = trim($mensagem);

            if ($numero === '') {
                return 'Informe o número do endereço.';
            }

            $dadosCep = $this->viaCep->buscar(
                (string) $conversa->cep_entrega
            );

            if ($dadosCep === null) {
                $conversa->estado = 'cep_entrega';
                $conversa->save();

                return
                    "❌ Não consegui recuperar os dados desse CEP.\n\n"
                    . "Informe o CEP novamente.";
            }

            $enderecoCliente = new Address(
                logradouro: $dadosCep['logradouro'],
                numero: $numero,
                complemento: null,
                bairro: $dadosCep['bairro'],
                cidade: $dadosCep['cidade'],
                estado: $dadosCep['estado'],
                cep: $dadosCep['cep'],
            );

            try {
                $restaurante = Restaurante::findOrFail(
                    $conversa->restaurante_id
                );

                $resultadoEntrega = $this->deliveryCalculator->calcular(
                    $restaurante,
                    $enderecoCliente
                );
            } catch (\RuntimeException $exception) {
                return
                    "❌ Não consegui calcular a entrega.\n\n"
                    . $exception->getMessage()
                    . "\n\nConfira o número do endereço ou informe outro CEP.";
            }

            $enderecoCompleto =
                $dadosCep['logradouro']
                . ', ' . $numero
                . ' - ' . $dadosCep['bairro']
                . ', ' . $dadosCep['cidade']
                . ' - ' . $dadosCep['estado']
                . ', CEP ' . $dadosCep['cep'];

            $conversa->endereco_entrega = $enderecoCompleto;
            $conversa->taxa_entrega = $resultadoEntrega->taxa;
            $conversa->estado = 'forma_pagamento';
            $conversa->save();

            /*
             * Salva o CEP no cadastro do cliente, se ele já existir.
             */
            Cliente::query()
                ->where('restaurante_id', $conversa->restaurante_id)
                ->where('telefone', $conversa->telefone)
                ->update([
                    'cep' => $dadosCep['cep'],
                ]);

            return
                "🚚 Entrega calculada!\n\n"
                . "📍 {$enderecoCompleto}\n"
                . "📏 Distância: "
                . number_format(
                    $resultadoEntrega->distanciaKm,
                    2,
                    ',',
                    '.'
                )
                . " km\n"
                . "💰 Taxa de entrega: R$ "
                . number_format(
                    $resultadoEntrega->taxa,
                    2,
                    ',',
                    '.'
                )
                . "\n\n"
                . $this->perguntaPagamento();
        }

        /*
        |--------------------------------------------------------------------------
        | Endereço
        |--------------------------------------------------------------------------
        */

        if ($conversa->estado === 'endereco') {
            $conversa->estado = 'forma_pagamento';
            $conversa->endereco_entrega = $mensagem;
            $conversa->save();

            return $this->perguntaPagamento();
        }

        /*
        |--------------------------------------------------------------------------
        | Forma de pagamento
        |--------------------------------------------------------------------------
        */

        if ($conversa->estado === 'forma_pagamento') {
            $formaPagamento =
                $this->identificarFormaPagamento($texto);

            if ($formaPagamento === null) {
                return $this->perguntaPagamento();
            }

            $conversa->estado = 'confirmacao';
            $conversa->forma_pagamento = $formaPagamento;
            $conversa->save();

            return $this->montarResumoFinal($conversa);
        }

        /*
        |--------------------------------------------------------------------------
        | Finalizar montagem do pedido
        |--------------------------------------------------------------------------
        */

        if ($this->querFinalizar($texto)) {
            $contexto = ConversationContext::fromArray(
                $conversa->contexto_ia
            );

            if (empty($contexto->pedido->itens())) {
                return
                    'Seu pedido ainda está vazio. '
                    . 'O que você gostaria de adicionar?';
            }

            if (!empty($contexto->faltando)) {
                $grupo = $contexto->faltando[0];

                return
                    "Antes de finalizar, precisamos escolher:\n\n"
                    . ($grupo['nome'] ?? 'uma opção do produto');
            }

            $this->sincronizarCarrinho(
                $conversa,
                $contexto
            );

            $conversa->estado = 'tipo_entrega';
            $conversa->save();

            return
                "Como deseja receber seu pedido?\n\n"
                . "🏪 Balcão\n"
                . "🛍️ Retirada\n"
                . "🚚 Entrega";
        }

        /*
        |--------------------------------------------------------------------------
        | Novo motor inteligente
        |--------------------------------------------------------------------------
        */

        $restaurante = Restaurante::findOrFail(
            $conversa->restaurante_id
        );

        $aiContext = $this->aiContextBuilder->build(
            $restaurante,
            $conversa
        );

        logger()->info('AI Context criado.', [
            'restaurante_id' => $aiContext->restaurante['id'] ?? null,
            'cliente' => $aiContext->cliente['nome'] ?? null,
            'telefone' => $aiContext->cliente['telefone'] ?? null,
            'categorias' => count($aiContext->categorias),
            'produtos' => count($aiContext->produtos),
            'historico' => count($aiContext->historico),
        ]);

        $contexto = ConversationContext::fromArray(
            $conversa->contexto_ia
        );

        /*
         * Uma conversa que já terminou pode começar um pedido novo.
         */
        if (
            $contexto->pedidoFinalizado
            || $contexto->estado === ConversationContext::ESTADO_FINALIZADO
        ) {
            $contexto = new ConversationContext();

            $conversa->pedido_confirmado = false;
            $conversa->pedido_id = null;
            $conversa->tipo_entrega = null;
            $conversa->cep_entrega = null;
            $conversa->endereco_entrega = null;
            $conversa->taxa_entrega = 0;
            $conversa->forma_pagamento = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Saudação personalizada pelo contexto do cliente
        |--------------------------------------------------------------------------
        */

        if ($this->ehSaudacao($texto)) {
            $contexto->intent = 'saudacao';

            $contexto->estado =
                ConversationContext::ESTADO_OFERECENDO_CARDAPIO;

            $conversa->contexto_ia = $contexto->toArray();
            $conversa->estado = $contexto->estado;

            return $this->saudacaoPersonalizada($aiContext);
        }

        /*
        |--------------------------------------------------------------------------
        | Repetir ou visualizar o último pedido
        |--------------------------------------------------------------------------
        */

        if ($this->querUltimoPedido($texto)) {
            return $this->mostrarUltimoPedido(
                $aiContext,
                $contexto,
                $conversa
            );
        }

        if (
            $contexto->estado
            === ConversationContext::ESTADO_AGUARDANDO_REPETIR_PEDIDO
            && $this->ehConfirmacao($texto)
        ) {
            return $this->restaurarUltimoPedido(
                $aiContext,
                $contexto,
                $conversa
            );
        }

        $resultado = $this->conversationEngine->processar(
            $restaurante,
            $mensagem,
            $contexto
        );

        /*
|--------------------------------------------------------------------------
| Transição do motor inteligente para o checkout
|--------------------------------------------------------------------------
|
| O ConversationEngine encerra a montagem do carrinho.
| A partir daqui, o RimaEngine assume entrega e pagamento.
|
*/

        if (
            ($resultado['acao'] ?? null) === 'pedido_confirmado'
            || $contexto->pedidoFinalizado
        ) {
            $contexto->pedidoFinalizado = false;
            $contexto->estado = 'tipo_entrega';
            $contexto->intent = 'tipo_entrega';

            $conversa->contexto_ia = $contexto->toArray();

            $this->sincronizarCarrinho(
                $conversa,
                $contexto
            );

            $conversa->estado = 'tipo_entrega';
            $conversa->pedido_confirmado = false;
            $conversa->tipo_entrega = null;
            $conversa->cep_entrega = null;
            $conversa->endereco_entrega = null;
            $conversa->taxa_entrega = 0;
            $conversa->forma_pagamento = null;

            $conversa->save();

            return
                "Pedido confirmado! 😊\n\n"
                . "Como deseja receber seu pedido?\n\n"
                . "🏪 Balcão\n"
                . "🛍️ Retirada\n"
                . "🚚 Entrega";
        }

        $conversa->contexto_ia = $contexto->toArray();
        $conversa->estado = $contexto->estado;

        $this->sincronizarCarrinho(
            $conversa,
            $contexto
        );

        $conversa->save();

        return $resultado['mensagem'];
    }

    private function sincronizarCarrinho(
        ConversaWhatsapp $conversa,
        ConversationContext $contexto
    ): void {
        $conversa->carrinho = collect(
            $contexto->pedido->itens()
        )
            ->map(function (array $item): string {
                $quantidade = (int) (
                    $item['quantidade'] ?? 1
                );

                $nome = (string) (
                    $item['nome'] ?? 'Produto'
                );

                return "{$quantidade}x {$nome}";
            })
            ->values()
            ->all();
    }

    private function montarResumoFinal(
        ConversaWhatsapp $conversa
    ): string {
        $itens = '';

        foreach ($conversa->carrinho ?? [] as $item) {
            $itens .= "🍔 {$item}\n";
        }

        $tipoEntrega = match ($conversa->tipo_entrega) {
            'entrega' =>
            "🚚 {$conversa->endereco_entrega}",

            'retirada' =>
            '🛍️ Retirada no restaurante',

            'balcao' =>
            '🏪 Consumo no balcão',

            default =>
            '📦 Recebimento não informado',
        };

        $pagamento = match ($conversa->forma_pagamento) {
            'pix' => 'Pix',
            'cartao' => 'Cartão',
            'dinheiro' => 'Dinheiro',
            default => ucfirst(
                (string) $conversa->forma_pagamento
            ),
        };

        return
            "📋 Resumo do pedido\n\n"
            . "🛒 Itens:\n"
            . $itens . "\n"
            . $tipoEntrega . "\n\n"
            . "💳 {$pagamento}\n\n"
            . "Deseja confirmar o pedido?\n\n"
            . "✅ Sim\n"
            . "❌ Não";
    }

    private function perguntaPagamento(): string
    {
        return
            "Qual a forma de pagamento?\n\n"
            . "💵 Dinheiro\n"
            . "💳 Cartão\n"
            . "🏦 Pix";
    }

    private function identificarFormaPagamento(
        string $texto
    ): ?string {
        if (Str::contains($texto, ['pix'])) {
            return 'pix';
        }

        if (
            Str::contains(
                $texto,
                ['cartao', 'credito', 'debito']
            )
        ) {
            return 'cartao';
        }

        if (
            Str::contains(
                $texto,
                ['dinheiro', 'especie']
            )
        ) {
            return 'dinheiro';
        }

        return null;
    }

    private function querFinalizar(string $texto): bool
    {
        return Str::contains($texto, [
            'finalizar',
            'fechar pedido',
            'concluir pedido',
            'terminar pedido',
            'so isso',
            'pode fechar',
        ]);
    }

    private function ehConfirmacao(string $texto): bool
    {
        return in_array($texto, [
            'sim',
            'confirmo',
            'confirmar',
            'pode confirmar',
            'pode fechar',
            'fechado',
        ], true);
    }

    private function ehNegacao(string $texto): bool
    {
        return Str::contains($texto, [
            'nao',
            'alterar',
            'mudar',
            'voltar',
            'cancelar',
        ]);
    }

    private function ehSaudacao(string $texto): bool
    {
        return in_array(
            trim($texto),
            [
                'oi',
                'ola',
                'bom dia',
                'boa tarde',
                'boa noite',
                'e ai',
            ],
            true
        );
    }

    private function querUltimoPedido(string $texto): bool
    {
        return Str::contains($texto, [
            'ultimo pedido',
            'meu ultimo pedido',
            'repetir ultimo pedido',
            'repetir meu pedido',
            'quero repetir',
            'quero o mesmo',
            'o de sempre',
            'mesmo pedido',
        ]);
    }

    private function mostrarUltimoPedido(
        AIContext $aiContext,
        ConversationContext $contexto,
        ConversaWhatsapp $conversa
    ): string {
        $ultimoPedido = $aiContext->ultimoPedido();

        if (
            $ultimoPedido === null
            || empty($ultimoPedido['itens'])
        ) {
            $contexto->estado =
                ConversationContext::ESTADO_OFERECENDO_CARDAPIO;

            $conversa->contexto_ia = $contexto->toArray();
            $conversa->estado = $contexto->estado;
            $conversa->save();

            return
                "Não encontrei um pedido anterior para repetir.\n\n"
                . "Você prefere ver o nosso cardápio?";
        }

        $contexto->intent = 'repetir_ultimo_pedido';

        $contexto->estado =
            ConversationContext::ESTADO_AGUARDANDO_REPETIR_PEDIDO;

        $conversa->contexto_ia = $contexto->toArray();
        $conversa->estado = $contexto->estado;
        $conversa->save();

        $mensagem = "Claro! Seu último pedido foi:\n\n";

        foreach ($ultimoPedido['itens'] as $item) {
            $quantidade = (int) (
                $item['quantidade'] ?? 1
            );

            $nome = (string) (
                $item['nome'] ?? 'Produto'
            );

            $mensagem .= "• {$quantidade}x {$nome}\n";
        }

        $mensagem .=
            "\nDeseja repetir exatamente esse pedido?";

        return $mensagem;
    }

    private function saudacaoPersonalizada(
        AIContext $contexto
    ): string {
        if (!$contexto->clienteRecorrente()) {
            return
                "Olá! 👋\n\n"
                . "Seja bem-vindo à "
                . $contexto->restauranteNome()
                . " 😊\n\n"
                . "Gostaria de ver nosso cardápio?";
        }

        return
            "Olá, {$contexto->nomeCliente()}! 😊\n\n"
            . "Que bom falar com você novamente.\n\n"
            . "Você gostaria de repetir seu último pedido "
            . "ou prefere ver o nosso cardápio?";
    }

    private function restaurarUltimoPedido(
        AIContext $aiContext,
        ConversationContext $contexto,
        ConversaWhatsapp $conversa
    ): string {
        $ultimoPedido = $aiContext->ultimoPedido();

        if (
            $ultimoPedido === null
            || empty($ultimoPedido['itens'])
        ) {
            $contexto->estado =
                ConversationContext::ESTADO_OFERECENDO_CARDAPIO;

            $conversa->contexto_ia = $contexto->toArray();
            $conversa->estado = $contexto->estado;
            $conversa->save();

            return
                "Não encontrei um pedido anterior para repetir.\n\n"
                . "Você prefere ver o nosso cardápio?";
        }

        $contexto->pedido = Order::fromArray([
            'itens' => $ultimoPedido['itens'],
        ]);

        $contexto->itens = $contexto->pedido->itens();
        $contexto->produto = null;
        $contexto->faltando = [];
        $contexto->intent = 'pedido_restaurado';
        $contexto->pedidoFinalizado = false;

        /*
         * O pedido já está montado. O próximo passo é escolher
         * a forma de recebimento.
         */
        $contexto->estado = 'tipo_entrega';

        $conversa->contexto_ia = $contexto->toArray();
        $conversa->estado = 'tipo_entrega';
        $conversa->carrinho = collect($contexto->pedido->itens())
            ->map(function (array $item): string {
                $quantidade = (int) ($item['quantidade'] ?? 1);
                $nome = (string) ($item['nome'] ?? 'Produto');

                return "{$quantidade}x {$nome}";
            })
            ->values()
            ->all();

        /*
         * Limpa dados de um eventual pedido anterior já finalizado.
         */
        $conversa->tipo_entrega = null;
        $conversa->cep_entrega = null;
        $conversa->endereco_entrega = null;
        $conversa->taxa_entrega = 0;
        $conversa->forma_pagamento = null;

        $conversa->save();

        return
            "Perfeito! 😊\n\n"
            . "Seu último pedido foi adicionado ao carrinho.\n\n"
            . "Como deseja receber?\n\n"
            . "🏪 Balcão\n"
            . "🛍️ Retirada\n"
            . "🚚 Entrega";
    }
}