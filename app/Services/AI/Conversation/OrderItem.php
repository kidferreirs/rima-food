<?php

namespace App\Services\AI\Conversation;

class OrderItem
{
    private array $produto;

    private int $quantidade;

    private array $adicionais = [];

    private array $ingredientesRemovidos = [];

    private ?string $observacao = null;

    public function __construct(
        array $produto,
        int $quantidade = 1
    ) {
        $this->produto = $produto;
        $this->quantidade = max(1, $quantidade);
    }

    public function produtoId(): int
    {
        return (int) $this->produto['id'];
    }

    public function nome(): string
    {
        return (string) $this->produto['nome'];
    }

    public function quantidade(): int
    {
        return $this->quantidade;
    }

    public function precoUnitario(): float
    {
        return (float) $this->produto['preco'];
    }

    public function adicionarQuantidade(int $quantidade = 1): void
    {
        if ($quantidade <= 0) {
            return;
        }

        $this->quantidade += $quantidade;
    }

    public function definirQuantidade(int $quantidade): void
    {
        $this->quantidade = max(1, $quantidade);
    }

    public function adicionarObservacao(string $observacao): void
    {
        $observacao = trim($observacao);

        if ($observacao === '') {
            return;
        }

        $this->observacao = $observacao;
    }

    public function adicionarAdicional(array $adicional): void
    {
        $this->adicionais[] = $adicional;
    }

    public function removerIngrediente(string $ingrediente): void
    {
        $ingrediente = trim($ingrediente);

        if ($ingrediente === '') {
            return;
        }

        if (!in_array($ingrediente, $this->ingredientesRemovidos, true)) {
            $this->ingredientesRemovidos[] = $ingrediente;
        }
    }

    public function subtotal(): float
    {
        $totalAdicionais = collect($this->adicionais)
            ->sum(fn(array $adicional) => (float) ($adicional['preco'] ?? 0));

        return (
            $this->precoUnitario() + $totalAdicionais
        ) * $this->quantidade;
    }

    public function toArray(): array
    {
        return [
            'produto_id' => $this->produtoId(),
            'nome' => $this->nome(),
            'quantidade' => $this->quantidade(),
            'preco_unitario' => $this->precoUnitario(),
            'subtotal' => $this->subtotal(),
            'observacao' => $this->observacao,
            'adicionais' => $this->adicionais,
            'ingredientes_removidos' => $this->ingredientesRemovidos,
        ];
    }
}