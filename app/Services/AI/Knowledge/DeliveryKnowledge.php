<?php

namespace App\Services\AI\Knowledge;

use App\Models\ConfiguracaoEntrega;
use App\Models\Restaurante;

class DeliveryKnowledge
{
    /**
     * Retorna as configurações de entrega do restaurante.
     */
    public function dados(Restaurante $restaurante): array
    {
        $configuracao = ConfiguracaoEntrega::query()
            ->where('restaurante_id', $restaurante->id)
            ->first();

        return [
            'aceita_delivery' => (bool) $restaurante->delivery,

            'taxa_padrao' => (float) ($restaurante->taxa_entrega ?? 0),

            'tempo_medio' => $restaurante->tempo_medio,

            'faixas' => [
                'ate_5km' => $configuracao
                    ? (float) $configuracao->ate_5km
                    : null,

                'ate_10km' => $configuracao
                    ? (float) $configuracao->ate_10km
                    : null,

                'acima_10km' => $configuracao
                    ? (float) $configuracao->acima_10km
                    : null,
            ],
        ];
    }

    /**
     * Retorna a taxa conforme a distância informada.
     */
    public function taxaPorDistancia(
        Restaurante $restaurante,
        float $distanciaKm
    ): ?float {
        if (!$restaurante->delivery) {
            return null;
        }

        $configuracao = ConfiguracaoEntrega::query()
            ->where('restaurante_id', $restaurante->id)
            ->first();

        if (!$configuracao) {
            return (float) ($restaurante->taxa_entrega ?? 0);
        }

        if ($distanciaKm <= 5) {
            return (float) $configuracao->ate_5km;
        }

        if ($distanciaKm <= 10) {
            return (float) $configuracao->ate_10km;
        }

        return (float) $configuracao->acima_10km;
    }
}