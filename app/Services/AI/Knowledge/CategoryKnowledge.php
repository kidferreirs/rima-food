<?php

namespace App\Services\AI\Knowledge;

use App\Models\Categoria;
use App\Models\Restaurante;
use Illuminate\Support\Str;

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
                'sinonimos',
                'palavras_chave',
            ])
            ->map(fn(Categoria $categoria): array => [
                'id' => $categoria->id,
                'nome' => $categoria->nome,
                'sinonimos' => $categoria->sinonimos,
                'palavras_chave' => $categoria->palavras_chave,
            ])
            ->all();
    }

    /**
     * Procura uma categoria usando nome, sinônimos
     * e palavras-chave cadastradas pelo estabelecimento.
     */
    public function buscarPorTermo(
        Restaurante $restaurante,
        string $termo
    ): ?array {
        $termoNormalizado = $this->normalizar($termo);

        if ($termoNormalizado === '') {
            return null;
        }

        $categorias = $this->listar($restaurante);

        /*
         * Primeiro procura uma correspondência exata.
         */
        foreach ($categorias as $categoria) {
            foreach ($this->termosCategoria($categoria) as $candidato) {
                if ($candidato === $termoNormalizado) {
                    return $categoria;
                }
            }
        }

        /*
         * Depois procura correspondência parcial.
         */
        foreach ($categorias as $categoria) {
            foreach ($this->termosCategoria($categoria) as $candidato) {
                if (
                    str_contains($candidato, $termoNormalizado)
                    || str_contains($termoNormalizado, $candidato)
                ) {
                    return $categoria;
                }
            }
        }

        return null;
    }

    /**
     * Busca uma categoria pelo ID.
     */
    public function encontrar(
        Restaurante $restaurante,
        int $categoriaId
    ): ?array {
        $categoria = Categoria::query()
            ->where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->find($categoriaId);

        if (!$categoria) {
            return null;
        }

        return [
            'id' => $categoria->id,
            'nome' => $categoria->nome,
            'sinonimos' => $categoria->sinonimos,
            'palavras_chave' => $categoria->palavras_chave,
        ];
    }

    public function existe(
        Restaurante $restaurante,
        int $categoriaId
    ): bool {
        return Categoria::query()
            ->where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->where('id', $categoriaId)
            ->exists();
    }

    private function termosCategoria(array $categoria): array
    {
        $termos = [
            $categoria['nome'] ?? '',
        ];

        foreach (['sinonimos', 'palavras_chave'] as $campo) {
            $valores = preg_split(
                '/[,;\n]+/',
                (string) ($categoria[$campo] ?? '')
            );

            foreach ($valores ?: [] as $valor) {
                $termos[] = $valor;
            }
        }

        return collect($termos)
            ->map(fn(string $termo): string => $this->normalizar($termo))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizar(string $texto): string
    {
        return Str::of($texto)
            ->lower()
            ->ascii()
            ->trim()
            ->replaceMatches('/[^\pL\pN\s\-]/u', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
}