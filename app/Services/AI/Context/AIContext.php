<?php

namespace App\Services\AI\Context;

class AIContext
{
    public function __construct(
        public readonly array $restaurante,
        public readonly array $cliente,
        public readonly array $categorias,
        public readonly array $produtos,
        public readonly array $historico,
        public readonly array $pedidoAtual,
        public readonly ?array $ultimoPedido,
        public readonly array $preferencias,
        public readonly string $resumo,
    ) {
    }
}