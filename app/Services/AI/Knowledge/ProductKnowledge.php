<?php

namespace App\Services\AI\Knowledge;

use App\Models\Produto;
use App\Models\Restaurante;

class ProductKnowledge
{
    /**
     * Buscar produtos pelo nome.
     */
    public function buscar(Restaurante $restaurante, string $termo): array
    {
        return Produto::query()
            ->select([
                'produtos.id',
                'produtos.nome',
                'produtos.descricao',
                'produtos.preco',
                'produtos.categoria_id',
            ])
            ->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
            ->where('categorias.restaurante_id', $restaurante->id)
            ->where('categorias.ativo', true)
            ->where('produtos.ativo', true)
            ->where(function ($query) use ($termo) {
                $query
                    ->where('produtos.nome', 'like', "%{$termo}%")
                    ->orWhere('produtos.descricao', 'like', "%{$termo}%")
                    ->orWhere('produtos.palavras_chave', 'like', "%{$termo}%")
                    ->orWhere('produtos.sinonimos', 'like', "%{$termo}%")
                    ->orWhere('produtos.tags', 'like', "%{$termo}%")
                    ->orWhere('produtos.ingredientes', 'like', "%{$termo}%")
                    ->orWhere('categorias.nome', 'like', "%{$termo}%")
                    ->orWhere('categorias.sinonimos', 'like', "%{$termo}%")
                    ->orWhere('categorias.palavras_chave', 'like', "%{$termo}%");
            })
            ->with('categoria:id,nome')
            ->orderBy('produtos.nome')
            ->limit(10)
            ->get()
            ->map(fn($produto) => [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'descricao' => $produto->descricao,
                'preco' => (float) $produto->preco,
                'categoria' => $produto->categoria?->nome,
            ])
            ->toArray();
    }

    /**
     * Lista todos os produtos ativos.
     */
    public function disponiveis(Restaurante $restaurante): array
    {
        return Produto::query()
            ->select([
                'produtos.id',
                'produtos.nome',
                'produtos.descricao',
                'produtos.preco',
                'produtos.categoria_id',
            ])
            ->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
            ->where('categorias.restaurante_id', $restaurante->id)
            ->where('categorias.ativo', true)
            ->where('produtos.ativo', true)
            ->with('categoria:id,nome')
            ->orderBy('produtos.nome')
            ->get()
            ->map(fn($produto) => [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'descricao' => $produto->descricao,
                'preco' => (float) $produto->preco,
                'categoria' => $produto->categoria?->nome,
            ])
            ->toArray();
    }

    /**
     * Produtos de uma categoria.
     */
    public function porCategoria(Restaurante $restaurante, int $categoriaId): array
    {
        return Produto::query()
            ->select([
                'produtos.id',
                'produtos.nome',
                'produtos.descricao',
                'produtos.preco',
                'produtos.categoria_id',
            ])
            ->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
            ->where('categorias.restaurante_id', $restaurante->id)
            ->where('categorias.id', $categoriaId)
            ->where('categorias.ativo', true)
            ->where('produtos.ativo', true)
            ->with('categoria:id,nome')
            ->orderBy('produtos.nome')
            ->get()
            ->map(fn($produto) => [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'descricao' => $produto->descricao,
                'preco' => (float) $produto->preco,
                'categoria' => $produto->categoria?->nome,
            ])
            ->toArray();
    }

    /**
     * Buscar um produto específico.
     */
    public function encontrar(Restaurante $restaurante, int $produtoId): ?array
    {
        $produto = Produto::query()
            ->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
            ->where('categorias.restaurante_id', $restaurante->id)
            ->where('categorias.ativo', true)
            ->where('produtos.ativo', true)
            ->where('produtos.id', $produtoId)
            ->select('produtos.*')
            ->with('categoria:id,nome')
            ->first();

        if (!$produto) {
            return null;
        }

        return [
            'id' => $produto->id,
            'nome' => $produto->nome,
            'descricao' => $produto->descricao,
            'preco' => (float) $produto->preco,
            'categoria' => $produto->categoria?->nome,
            'ingredientes' => $produto->ingredientes,
            'restricoes' => $produto->restricoes,
            'tags' => $produto->tags,
        ];
    }
}