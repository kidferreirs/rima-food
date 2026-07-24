<?php

namespace App\Services\AI\Knowledge;

use App\Models\Categoria;
use App\Models\Restaurante;

class CategoryKnowledge
{
    /**
     * Lista todas as categorias ativas do restaurante.
     */
    public function listar(Restaurante $restaurante): array
    {
        return Categoria::query()
            ->where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get([
                'id',
                'nome',
            ])
            ->map(fn ($categoria) => [
                'id'   => $categoria->id,
                'nome' => $categoria->nome,
            ])
            ->toArray();
    }

    /**
     * Busca uma categoria pelo ID.
     */
    public function encontrar(Restaurante $restaurante, int $categoriaId): ?array
    {
        $categoria = Categoria::query()
            ->where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->find($categoriaId);

        if (!$categoria) {
            return null;
        }

        return [
            'id'   => $categoria->id,
            'nome' => $categoria->nome,
        ];
    }

    /**
     * Verifica se uma categoria existe para este restaurante.
     */
    public function existe(Restaurante $restaurante, int $categoriaId): bool
    {
        return Categoria::query()
            ->where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->where('id', $categoriaId)
            ->exists();
    }
}