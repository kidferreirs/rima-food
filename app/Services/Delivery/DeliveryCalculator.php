<?php

namespace App\Services\Delivery;

use App\Models\Restaurante;
use RuntimeException;

class DeliveryCalculator
{
    public function __construct(
        private readonly GoogleMapsService $routeService
    ) {

    }

    public function calcular(
        Restaurante $restaurante,
        Address $enderecoCliente
    ): DeliveryResult {
        $restaurante->loadMissing('configuracaoEntrega');

        $configuracao = $restaurante->configuracaoEntrega;

        if (!$configuracao) {
            throw new RuntimeException(
                'O restaurante ainda não configurou as taxas de entrega.'
            );
        }

        if (
            blank($restaurante->latitude) ||
            blank($restaurante->longitude)
        ) {
            throw new RuntimeException(
                'O restaurante ainda não possui coordenadas cadastradas.'
            );
        }

        $origem = [
            'latitude' => (float) $restaurante->latitude,
            'longitude' => (float) $restaurante->longitude,
        ];

        $destino = $this->routeService->geocodificar(
            $enderecoCliente
        );

        $rota = $this->routeService->calcularRota(
            $origem,
            $destino
        );

        $distanciaKm = $rota['distanciaKm'];

        if ($distanciaKm <= 5) {
            $taxa = (float) $configuracao->ate_5km;
            $faixa = 'Até 5 km';
        } elseif ($distanciaKm <= 10) {
            $taxa = (float) $configuracao->ate_10km;
            $faixa = 'De 5 a 10 km';
        } else {
            $taxa = (float) $configuracao->acima_10km;
            $faixa = 'Acima de 10 km';
        }

        return new DeliveryResult(
            distanciaKm: $distanciaKm,
            taxa: $taxa,
            faixa: $faixa,
        );
    }

    private function enderecoRestaurante(
        Restaurante $restaurante
    ): Address {
        $camposObrigatorios = [
            'endereco',
            'numero',
            'bairro',
            'cidade',
            'estado',
        ];

        foreach ($camposObrigatorios as $campo) {
            if (blank($restaurante->{$campo})) {
                throw new RuntimeException(
                    'O endereço do restaurante está incompleto. '
                    . 'Preencha o campo: ' . $campo . '.'
                );
            }
        }

        return new Address(
            logradouro: $restaurante->endereco,
            numero: $restaurante->numero,
            complemento: $restaurante->complemento,
            bairro: $restaurante->bairro,
            cidade: $restaurante->cidade,
            estado: $restaurante->estado,
            cep: $restaurante->cep,
        );
    }
}