<?php

namespace App\Services\Delivery;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenRouteService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.openrouteservice.org';

    public function __construct()
    {
        $this->apiKey = (string) config('services.openrouteservice.key');

        if (blank($this->apiKey)) {
            throw new RuntimeException(
                'A chave OPENROUTESERVICE_API_KEY não foi configurada.'
            );
        }
    }

    /**
     * Retorna no formato exigido pela API:
     * [longitude, latitude]
     */
    public function geocodificar(Address $endereco): array
    {
        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->retry(2, 500)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->get($this->baseUrl . '/geocode/search', [
                    'text' => $endereco->textoCompleto(),
                    'boundary.country' => 'BR',
                    'size' => 1,
                ]);

            $response->throw();

            $coordenadas = data_get(
                $response->json(),
                'features.0.geometry.coordinates'
            );

            if (
                !is_array($coordenadas) ||
                count($coordenadas) !== 2
            ) {
                throw new RuntimeException(
                    'Não foi possível localizar o endereço: '
                    . $endereco->textoCompleto()
                );
            }

            return [
                (float) $coordenadas[0],
                (float) $coordenadas[1],
            ];
        } catch (ConnectionException $exception) {
            report($exception);

            throw new RuntimeException(
                'Não foi possível conectar ao serviço de localização.'
            );
        } catch (RequestException $exception) {
            report($exception);


            logger()->error('Erro OpenRouteService GEOCODE', [
                'status' => $exception->response?->status(),
                'body' => $exception->response?->body(),
                'url' => $exception->response?->effectiveUri()?->__toString(),
            ]);

            report($exception);

            throw new RuntimeException(
                $this->mensagemErroApi(
                    $exception->response?->status()
                )
            );






            throw new RuntimeException(
                $this->mensagemErroApi(
                    $exception->response?->status()
                )
            );
        }
    }

    /**
     * Recebe coordenadas como [longitude, latitude]
     * e devolve a distância real da rota em quilômetros.
     */
    public function calcularDistancia(
        array $origem,
        array $destino
    ): float {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(20)
                ->retry(2, 500)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->post(
                    $this->baseUrl
                    . '/v2/directions/driving-car/json',
                    [
                        'coordinates' => [
                            $origem,
                            $destino,
                        ],
                        'instructions' => false,
                    ]
                );

            $response->throw();

            $distanciaMetros = data_get(
                $response->json(),
                'routes.0.summary.distance'
            );

            if (!is_numeric($distanciaMetros)) {
                throw new RuntimeException(
                    'Não foi possível calcular a rota de entrega.'
                );
            }

            return round(((float) $distanciaMetros) / 1000, 2);
        } catch (ConnectionException $exception) {
            report($exception);

            throw new RuntimeException(
                'Não foi possível conectar ao serviço de rotas.'
            );
        } catch (RequestException $exception) {
            report($exception);


            logger()->error('Erro OpenRouteService DIRECTIONS', [
                'status' => $exception->response?->status(),
                'body' => $exception->response?->body(),
                'url' => $exception->response?->effectiveUri()?->__toString(),
            ]);

            report($exception);

            throw new RuntimeException(
                $this->mensagemErroApi(
                    $exception->response?->status()
                )
            );



            throw new RuntimeException(
                $this->mensagemErroApi(
                    $exception->response?->status()
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            if ($exception instanceof RuntimeException) {
                throw $exception;
            }

            throw new RuntimeException(
                'Ocorreu um erro ao calcular a entrega.'
            );
        }
    }

    private function mensagemErroApi(?int $status): string
    {
        return match ($status) {
            401, 403 => 'A chave do OpenRouteService é inválida ou não possui permissão.',
            404 => 'O serviço de rotas não foi encontrado.',
            429 => 'O limite gratuito do serviço de rotas foi atingido. Tente novamente em alguns minutos.',
            500, 502, 503, 504 => 'O serviço de rotas está temporariamente indisponível.',
            default => 'Não foi possível consultar o serviço de rotas.',
        };
    }
}