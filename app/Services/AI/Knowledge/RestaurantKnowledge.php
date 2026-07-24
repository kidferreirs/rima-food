<?php

namespace App\Services\AI\Knowledge;

use App\Models\Restaurante;

class RestaurantKnowledge
{
    /**
     * Retorna as principais informações do restaurante.
     */
    public function dados(Restaurante $restaurante): array
    {
        return [
            'id' => $restaurante->id,
            'nome' => $restaurante->nome,
            'telefone' => $restaurante->telefone,
            'email' => $restaurante->email,

            'endereco' => trim(
                "{$restaurante->endereco}, {$restaurante->numero} - {$restaurante->bairro}, {$restaurante->cidade}/{$restaurante->estado}"
            ),

            'horario' => [
                'abre' => $restaurante->abre_as,
                'fecha' => $restaurante->fecha_as,
            ],

            'delivery' => (bool) $restaurante->delivery,
            'retirada' => (bool) $restaurante->retirada,
            'consumo_local' => (bool) $restaurante->consumo_local,

            'taxa_entrega' => (float) $restaurante->taxa_entrega,
            'tempo_medio' => $restaurante->tempo_medio,

            'instagram' => $restaurante->instagram,
            'site' => $restaurante->site,
        ];
    }

    /**
     * Restaurante aceita delivery?
     */
    public function aceitaDelivery(Restaurante $restaurante): bool
    {
        return (bool) $restaurante->delivery;
    }

    /**
     * Restaurante aceita retirada?
     */
    public function aceitaRetirada(Restaurante $restaurante): bool
    {
        return (bool) $restaurante->retirada;
    }

    /**
     * Restaurante possui consumo no local?
     */
    public function aceitaConsumoLocal(Restaurante $restaurante): bool
    {
        return (bool) $restaurante->consumo_local;
    }

    /**
     * Horário de funcionamento.
     */
    public function horario(Restaurante $restaurante): array
    {
        return [
            'abre' => $restaurante->abre_as,
            'fecha' => $restaurante->fecha_as,
        ];
    }
}