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

    public function nomeCliente(): string
    {
        return $this->cliente['nome'] ?? 'Cliente';
    }

    public function clienteRecorrente(): bool
    {
        return ($this->cliente['total_pedidos'] ?? 0) > 0;
    }

    public function totalPedidos(): int
    {
        return (int) ($this->cliente['total_pedidos'] ?? 0);
    }

    public function totalGasto(): float
    {
        return (float) ($this->cliente['total_gasto'] ?? 0);
    }

    public function possuiHistorico(): bool
    {
        return count($this->historico) > 0;
    }

    public function historicoRecente(int $limite = 10): array
    {
        return array_slice($this->historico, -$limite);
    }

    public function ultimoPedido(): ?array
    {
        return $this->ultimoPedido;
    }

    public function possuiUltimoPedido(): bool
    {
        return $this->ultimoPedido !== null;
    }

    public function formaPagamentoFavorita(): ?string
    {
        return $this->preferencias['forma_pagamento'] ?? null;
    }

    public function tipoEntregaFavorita(): ?string
    {
        return $this->preferencias['tipo_entrega'] ?? null;
    }

    public function produtoFavorito(): ?array
    {
        return $this->preferencias['produto_favorito'] ?? null;
    }

    public function resumoCliente(): string
    {
        return $this->resumo;
    }

    public function possuiProdutoFavorito(): bool
    {
        return $this->produtoFavorito() !== null;
    }

    public function buscarCategoria(string $nome): ?array
    {
        $nomeNormalizado = $this->normalizarTexto($nome);

        foreach ($this->categorias as $categoria) {
            $categoriaNormalizada = $this->normalizarTexto(
                (string) ($categoria['nome'] ?? '')
            );

            if ($categoriaNormalizada === $nomeNormalizado) {
                return $categoria;
            }
        }
        return null;
    }

    public function buscarProduto(string $texto): ?array
    {
        $textoNormalizado = $this->normalizarTexto($texto);

        foreach ($this->produtos as $produto) {
            $nomeNormalizado = $this->normalizarTexto(
                (string) ($produto['nome'] ?? '')
            );

            if (
                str_contains($nomeNormalizado, $textoNormalizado)
                || str_contains($textoNormalizado, $nomeNormalizado)
            ) {
                return $produto;
            }
        }

        return null;
    }

    public function possuiProduto(string $texto): bool
    {
        return $this->buscarProduto($texto) !== null;
    }

    public function quantidadeProdutos(): int
    {
        return count($this->produtos);
    }

    public function quantidadeCategorias(): int
    {
        return count($this->categorias);
    }

    public function restauranteNome(): string
    {
        return $this->restaurante['nome'] ?? '';
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        $texto = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $texto
        ) ?: $texto;

        return preg_replace(
            '/[^a-z0-9]/',
            '',
            $texto
        ) ?? '';
    }
}