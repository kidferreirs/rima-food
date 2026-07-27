<?php

namespace App\Services\AI\Conversation;

class Order
{
    private array $itens = [];

    public function adicionarProduto(array $produto, int $quantidade = 1): void
    {
        $quantidade = max(1, $quantidade);

        foreach ($this->itens as &$item) {
            if ($item['produto_id'] === $produto['id']) {
                $item['quantidade'] += $quantidade;
                $item['subtotal'] = round(
                    $item['quantidade'] * $item['preco_unitario'],
                    2
                );

                return;
            }
        }

        unset($item);

        $preco = (float) $produto['preco'];

        $this->itens[] = [
            'produto_id' => $produto['id'],
            'nome' => $produto['nome'],
            'quantidade' => $quantidade,
            'preco_unitario' => $preco,
            'subtotal' => round($preco * $quantidade, 2),
            'observacao' => null,
        ];
    }

    public function adicionarObservacaoUltimoProduto(string $observacao): bool
    {
        if ($this->estaVazio()) {
            return false;
        }

        $indice = array_key_last($this->itens);

        $this->itens[$indice]['observacao'] = trim($observacao);

        return true;
    }

    public function removerUltimoProduto(): ?array
    {
        if ($this->estaVazio()) {
            return null;
        }

        return array_pop($this->itens);
    }

    public function estaVazio(): bool
    {
        return empty($this->itens);
    }

    public function itens(): array
    {
        return $this->itens;
    }

    public function quantidadeItens(): int
    {
        return array_sum(
            array_column($this->itens, 'quantidade')
        );
    }

    public function subtotal(): float
    {
        return round(
            array_sum(array_column($this->itens, 'subtotal')),
            2
        );
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
            $linha = sprintf(
                '%dx %s - R$ %s',
                $item['quantidade'],
                $item['nome'],
                number_format($item['subtotal'], 2, ',', '.')
            );

            if (!empty($item['observacao'])) {
                $linha .= ' (' . $item['observacao'] . ')';
            }

            $linhas[] = $linha;
        }

        $linhas[] = sprintf(
            'Subtotal: R$ %s',
            number_format($this->subtotal(), 2, ',', '.')
        );

        return implode(PHP_EOL, $linhas);
    }
}