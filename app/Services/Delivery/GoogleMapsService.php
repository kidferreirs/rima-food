<?php

namespace App\Services\Delivery;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleMapsService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.key');
    }

    public function geocodificar(Address $address): array
    {
        foreach ($address->tentativasGeocodificacao() as $tentativa) {

            $response = Http::get(
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'address' => $tentativa,
                    'key' => $this->apiKey,
                ]
            );

            if (!$response->successful()) {
                continue;
            }

            $json = $response->json();

            if (($json['status'] ?? '') !== 'OK') {
                continue;
            }

            $result = $json['results'][0];

            return [
                'latitude' => $result['geometry']['location']['lat'],
                'longitude' => $result['geometry']['location']['lng'],
                'place_id' => $result['place_id'],
            ];
        }

        throw new RuntimeException(
            'Não foi possível localizar o endereço informado.'
        );
    }

    public function calcularRota(
        array $origem,
        array $destino
    ): array {

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration',
        ])->post(
                'https://routes.googleapis.com/directions/v2:computeRoutes',
                [
                    'origin' => [
                        'location' => [
                            'latLng' => [
                                'latitude' => $origem['latitude'],
                                'longitude' => $origem['longitude'],
                            ],
                        ],
                    ],
                    'destination' => [
                        'location' => [
                            'latLng' => [
                                'latitude' => $destino['latitude'],
                                'longitude' => $destino['longitude'],
                            ],
                        ],
                    ],
                    'travelMode' => 'DRIVE',
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Erro ao calcular rota no Google Maps.'
            );
        }

        $json = $response->json();

        logger()->info('GOOGLE ROUTES', $json);

        if (empty($json['routes'])) {
            throw new RuntimeException(
                'Nenhuma rota encontrada.'
            );
        }

        return [
            'distanciaKm' => $json['routes'][0]['distanceMeters'] / 1000,
        ];
    }
}