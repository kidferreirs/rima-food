<?php

namespace App\Services\Delivery;

class DeliveryResult
{
    public function __construct(
        public readonly float $distanciaKm,
        public readonly float $taxa,
        public readonly string $faixa,
    ) {
    }

    public function toArray(): array
    {
        return [
            'distancia_km' => round($this->distanciaKm, 2),
            'taxa' => round($this->taxa, 2),
            'faixa' => $this->faixa,
        ];
    }
}