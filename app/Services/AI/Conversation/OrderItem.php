<?php

namespace App\Services\AI\Conversation;

class OrderItem
{
    private array $produto;

    private int $quantidade;

    private array $opcoesSelecionadas = [];

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

    public function adicionarOpcao(array $opcao): bool
    {
        $nome = trim((string) ($opcao['nome'] ?? ''));

        if ($nome === '') {
            return false;
        }

        $opcaoNormalizada = [
            'id' => isset($opcao['id'])
                ? (int) $opcao['id']
                : null,

            'grupo_id' => isset($opcao['grupo_id'])
                ? (int) $opcao['grupo_id']
                : (
                    isset($opcao['product_option_group_id'])
                    ? (int) $opcao['product_option_group_id']
                    : null
                ),

            'grupo' => $opcao['grupo'] ?? null,

            'nome' => $nome,

            'valor' => round(
                (float) ($opcao['valor'] ?? $opcao['preco'] ?? 0),
                2
            ),
        ];

        foreach ($this->opcoesSelecionadas as $selecionada) {
            $mesmoId = $opcaoNormalizada['id'] !== null
                && $selecionada['id'] === $opcaoNormalizada['id'];

            $mesmoNome = mb_strtolower($selecionada['nome'])
                === mb_strtolower($opcaoNormalizada['nome']);

            if ($mesmoId || $mesmoNome) {
                return false;
            }
        }

        $this->opcoesSelecionadas[] = $opcaoNormalizada;

        return true;
    }

    /**
     * Mantém compatibilidade com o nome usado anteriormente.
     */
    public function adicionarAdicional(array $adicional): void
    {
        $this->adicionarOpcao($adicional);
    }

    public function removerOpcao(int|string $identificador): bool
    {
        foreach ($this->opcoesSelecionadas as $indice => $opcao) {
            $identificadorPorId = is_numeric($identificador)
                && $opcao['id'] !== null
                && $opcao['id'] === (int) $identificador;

            $identificadorPorNome = mb_strtolower($opcao['nome'])
                === mb_strtolower(trim((string) $identificador));

            if ($identificadorPorId || $identificadorPorNome) {
                array_splice($this->opcoesSelecionadas, $indice, 1);

                return true;
            }
        }

        return false;
    }

    public function opcoesSelecionadas(): array
    {
        return $this->opcoesSelecionadas;
    }

    public function opcoesDoGrupo(int $grupoId): array
    {
        return array_values(
            array_filter(
                $this->opcoesSelecionadas,
                fn(array $opcao): bool =>
                (int) ($opcao['grupo_id'] ?? 0) === $grupoId
            )
        );
    }
    public function removerOpcoesGrupo(int $grupoId): void
    {
        $this->opcoesSelecionadas = array_values(
            array_filter(
                $this->opcoesSelecionadas,
                fn(array $opcao): bool =>
                (int) ($opcao['grupo_id'] ?? 0) !== $grupoId
            )
        );
    }

    public function removerIngrediente(string $ingrediente): void
    {
        $ingrediente = trim($ingrediente);

        if ($ingrediente === '') {
            return;
        }

        foreach ($this->ingredientesRemovidos as $removido) {
            if (mb_strtolower($removido) === mb_strtolower($ingrediente)) {
                return;
            }
        }

        $this->ingredientesRemovidos[] = $ingrediente;
    }

    public function valorOpcoes(): float
    {
        return round(
            array_reduce(
                $this->opcoesSelecionadas,
                fn(float $total, array $opcao): float =>
                $total + (float) $opcao['valor'],
                0.0
            ),
            2
        );
    }

    public function subtotal(): float
    {
        return round(
            ($this->precoUnitario() + $this->valorOpcoes())
            * $this->quantidade,
            2
        );
    }

    public function toArray(): array
    {
        return [
            'produto_id' => $this->produtoId(),
            'nome' => $this->nome(),
            'quantidade' => $this->quantidade(),
            'preco_unitario' => $this->precoUnitario(),
            'valor_opcoes' => $this->valorOpcoes(),
            'subtotal' => $this->subtotal(),
            'observacao' => $this->observacao,

            // Nome genérico usado daqui para frente.
            'opcoes_selecionadas' => $this->opcoesSelecionadas,

            // Mantido temporariamente por compatibilidade.
            'adicionais' => $this->opcoesSelecionadas,

            'ingredientes_removidos' => $this->ingredientesRemovidos,
        ];
    }

    public function quantidadeOpcoesGrupo(int $grupoId): int
    {
        return count(array_filter(
            $this->opcoesSelecionadas,
            fn($opcao) => $opcao['grupo_id'] === $grupoId
        ));
    }

    public function possuiOpcaoGrupo(int $grupoId): bool
    {
        return $this->quantidadeOpcoesGrupo($grupoId) > 0;
    }

    public static function fromArray(array $dados): self
    {
        $item = new self(
            [
                'id' => (int) ($dados['produto_id'] ?? 0),
                'nome' => (string) ($dados['nome'] ?? ''),
                'preco' => (float) ($dados['preco_unitario'] ?? 0),
            ],
            (int) ($dados['quantidade'] ?? 1)
        );

        $opcoes = $dados['opcoes_selecionadas']
            ?? $dados['adicionais']
            ?? [];

        foreach ($opcoes as $opcao) {
            $item->adicionarOpcao($opcao);
        }

        foreach ($dados['ingredientes_removidos'] ?? [] as $ingrediente) {
            $item->removerIngrediente($ingrediente);
        }

        if (!empty($dados['observacao'])) {
            $item->adicionarObservacao(
                (string) $dados['observacao']
            );
        }

        return $item;
    }
}