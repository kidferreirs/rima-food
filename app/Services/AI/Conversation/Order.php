<?php

namespace App\Services\AI\Conversation;

class Order
{
    private array $itens = [];

    public function adicionarProduto(array $produto, int $quantidade = 1): void
    {
        $quantidade = max(1, $quantidade);

        foreach ($this->itens as $item) {
            if ($item->produtoId() === $produto['id']) {
                $item->adicionarQuantidade($quantidade);
                return;
            }
        }

        $this->itens[] = new OrderItem(
            $produto,
            $quantidade
        );
    }

    public function ultimoItem(): ?OrderItem
    {
        if ($this->estaVazio()) {
            return null;
        }

        $indice = array_key_last($this->itens);

        return $this->itens[$indice];
    }

    public function adicionarObservacaoUltimoProduto(string $observacao): bool
    {
        $item = $this->ultimoItem();

        if ($item === null) {
            return false;
        }

        $item->adicionarObservacao($observacao);

        return true;
    }

    public function removerIngredienteUltimoProduto(
        string $ingrediente
    ): bool {
        $item = $this->ultimoItem();

        if ($item === null) {
            return false;
        }

        $item->removerIngrediente($ingrediente);

        return true;
    }

    public function adicionarOpcoesUltimoProduto(array $opcoes): bool
    {
        $item = $this->ultimoItem();

        if ($item === null || empty($opcoes)) {
            return false;
        }

        foreach ($opcoes as $opcao) {
            $item->adicionarOpcao($opcao);
        }

        return true;
    }

    public function removerUltimoProduto(): ?array
    {
        if ($this->estaVazio()) {
            return null;
        }

        /** @var OrderItem $itemRemovido */
        $itemRemovido = array_pop($this->itens);

        return $itemRemovido->toArray();
    }
    public function estaVazio(): bool
    {
        return empty($this->itens);
    }

    public function itens(): array
    {
        return array_map(
            fn(OrderItem $item) => $item->toArray(),
            $this->itens
        );
    }
    public function quantidadeItens(): int
    {
        return array_reduce(
            $this->itens,
            fn(int $total, OrderItem $item): int =>
            $total + $item->quantidade(),
            0
        );
    }

    public function subtotal(): float
    {
        $subtotal = array_reduce(
            $this->itens,
            fn(float $total, OrderItem $item): float =>
            $total + $item->subtotal(),
            0.0
        );

        return round($subtotal, 2);
    }

    public function total(): float
    {
        return $this->subtotal();
    }

    public function resumo(): string
    {
        if ($this->estaVazio()) {
            return 'Seu pedido está vazio.';
        }

        $linhas = [];

        foreach ($this->itens as $item) {

            $dados = $item->toArray();

            $linha = sprintf(
                '%dx %s - R$ %s',
                $dados['quantidade'],
                $dados['nome'],
                number_format(
                    $dados['subtotal'],
                    2,
                    ',',
                    '.'
                )
            );

            if (!empty($dados['observacao'])) {
                $linha .= ' (' . $dados['observacao'] . ')';
            }

            $linhas[] = $linha;
        }

        $linhas[] = sprintf(
            'Subtotal: R$ %s',
            number_format(
                $this->subtotal(),
                2,
                ',',
                '.'
            )
        );

        return implode(PHP_EOL, $linhas);
    }

    public function toArray(): array
    {
        return [
            'itens' => $this->itens(),
        ];
    }

    public static function fromArray(array $dados): self
    {
        $pedido = new self();

        foreach ($dados['itens'] ?? [] as $dadosItem) {
            $pedido->itens[] = OrderItem::fromArray($dadosItem);
        }

        return $pedido;
    }
}