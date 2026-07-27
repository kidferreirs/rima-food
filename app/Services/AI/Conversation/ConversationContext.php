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
    public const ESTADO_FINALIZADO = 'finalizado';

    public string $intent = 'unknown';

    public string $estado = self::ESTADO_INICIO;

    public ?array $produto = null;

    // Mantemos por compatibilidade temporária
    public array $itens = [];

    // NOVO
    public Order $pedido;

    public array $faltando = [];

    public bool $pedidoFinalizado = false;

    public function __construct()
    {
        $this->pedido = new Order();
    }
}