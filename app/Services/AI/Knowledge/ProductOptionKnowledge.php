<?php

namespace App\Services\AI\Knowledge;

use App\Models\ProductOption;
use App\Models\ProductOptionGroup;

class ProductOptionKnowledge
{
    public function gruposDoProduto(int $produtoId): array
    {
        return ProductOptionGroup::query()
            ->where('produto_id', $produtoId)
            ->where('ativo', true)
            ->with([
                'opcoesAtivas' => fn ($query) => $query->orderBy('ordem'),
            ])
            ->orderBy('ordem')
            ->get()
            ->map(function (ProductOptionGroup $grupo): array {
                return [
                    'id' => $grupo->id,
                    'produto_id' => $grupo->produto_id,
                    'nome' => $grupo->nome,
                    'tipo' => $grupo->tipo,
                    'minimo' => $grupo->minimo,
                    'maximo' => $grupo->maximo,
                    'obrigatorio' => $grupo->minimo > 0,
                    'opcoes' => $grupo->opcoesAtivas
                        ->map(fn (ProductOption $opcao): array => [
                            'id' => $opcao->id,
                            'grupo_id' => $grupo->id,
                            'grupo' => $grupo->nome,
                            'nome' => $opcao->nome,
                            'valor' => (float) $opcao->valor,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function opcoesDoGrupo(int $grupoId): array
    {
        return ProductOption::query()
            ->where('product_option_group_id', $grupoId)
            ->where('ativo', true)
            ->orderBy('ordem')
            ->get()
            ->map(fn (ProductOption $opcao): array => [
                'id' => $opcao->id,
                'grupo_id' => $opcao->product_option_group_id,
                'nome' => $opcao->nome,
                'valor' => (float) $opcao->valor,
            ])
            ->values()
            ->all();
    }

    public function buscarOpcao(
        int $produtoId,
        string $nome
    ): ?array {
        $nome = trim($nome);

        if ($nome === '') {
            return null;
        }

        $opcao = ProductOption::query()
            ->where('ativo', true)
            ->whereHas('grupo', function ($query) use ($produtoId): void {
                $query
                    ->where('produto_id', $produtoId)
                    ->where('ativo', true);
            })
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->with('grupo')
            ->first();

        if (!$opcao) {
            return null;
        }

        return [
            'id' => $opcao->id,
            'grupo_id' => $opcao->product_option_group_id,
            'grupo' => $opcao->grupo->nome,
            'nome' => $opcao->nome,
            'valor' => (float) $opcao->valor,
        ];
    }

    public function produtoPossuiOpcoes(int $produtoId): bool
    {
        return ProductOptionGroup::query()
            ->where('produto_id', $produtoId)
            ->where('ativo', true)
            ->whereHas('opcoesAtivas')
            ->exists();
    }
}