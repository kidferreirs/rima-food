<?php

namespace App\Services\AI\Conversation;

final class ConversationAction
{
    public const SAUDACAO = 'saudacao';

    public const ENVIAR_CARDAPIO = 'enviar_cardapio';

    public const LISTAR_CATEGORIAS = 'listar_categorias';

    public const CATEGORIAS_ENCONTRADAS = 'categorias_encontradas';

    public const INFORMAR_HORARIO = 'informacao_horario';

    public const INFORMAR_DELIVERY = 'informacao_delivery';

    public const INFORMAR_RETIRADA = 'informacao_retirada';

    public const INFORMAR_ENDERECO = 'informacao_endereco';

    public const INFORMAR_RESTAURANTE = 'informacao_restaurante';

    public const PRODUTO_ADICIONADO = 'produto_adicionado';

    public const MULTIPLOS_PRODUTOS = 'multiplos_produtos';

    public const PRODUTO_NAO_ENCONTRADO = 'produto_nao_encontrado';

    public const PEDIDO_VAZIO = 'pedido_vazio';

    public const AGUARDANDO_CONFIRMACAO = 'aguardando_confirmacao';

    public const ALTERAR_PEDIDO = 'alterar_pedido';

    public const PEDIDO_CONFIRMADO = 'pedido_confirmado';

    public const RESPOSTA_DESCONHECIDA = 'resposta_desconhecida';
}