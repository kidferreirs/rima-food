<?php

namespace App\Services\AI\Conversation;

class ConversationContext
{
    public const ESTADO_INICIO = 'inicio';
    public const ESTADO_OFERECENDO_CARDAPIO = 'oferecendo_cardapio';
    public const ESTADO_ATENDIMENTO = 'atendimento';
    public const ESTADO_ESCOLHENDO_PRODUTO = 'escolhendo_produto';
    public const ESTADO_MONTANDO_PEDIDO = 'montando_pedido';
    public const ESTADO_AGUARDANDO_CONFIRMACAO = 'aguardando_confirmacao';
    public const ESTADO_AGUARDANDO_REPETIR_PEDIDO =
        'aguardando_repetir_pedido';
    public const ESTADO_FINALIZADO = 'finalizado';

    public string $intent = 'unknown';

    public string $estado = self::ESTADO_INICIO;

    public ?array $produto = null;

    public array $itens = [];

    public Order $pedido;

    public array $faltando = [];

    public bool $pedidoFinalizado = false;

    public function __construct()
    {
        $this->pedido = new Order();
    }

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'estado' => $this->estado,
            'produto' => $this->produto,
            'itens' => $this->pedido->itens(),
            'pedido' => $this->pedido->toArray(),
            'faltando' => $this->faltando,
            'pedido_finalizado' => $this->pedidoFinalizado,
        ];
    }

    public static function fromArray(?array $dados): self
    {
        $contexto = new self();

        if (empty($dados)) {
            return $contexto;
        }

        $contexto->intent = (string) (
            $dados['intent'] ?? 'unknown'
        );

        $contexto->estado = (string) (
            $dados['estado'] ?? self::ESTADO_INICIO
        );

        $contexto->produto = isset($dados['produto'])
            && is_array($dados['produto'])
            ? $dados['produto']
            : null;

        $contexto->faltando = is_array(
            $dados['faltando'] ?? null
        )
            ? $dados['faltando']
            : [];

        $contexto->pedidoFinalizado = (bool) (
            $dados['pedido_finalizado'] ?? false
        );

        $dadosPedido = $dados['pedido'] ?? [
            'itens' => $dados['itens'] ?? [],
        ];

        $contexto->pedido = Order::fromArray(
            is_array($dadosPedido) ? $dadosPedido : []
        );

        $contexto->itens = $contexto->pedido->itens();

        return $contexto;
    }
}