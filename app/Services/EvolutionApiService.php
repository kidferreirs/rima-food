<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvolutionApiService
{
    private string $url;

    private string $apiKey;

    public function __construct()
    {
        $this->url = rtrim(
            (string) config('services.evolution.url'),
            '/'
        );

        $this->apiKey = (string) config(
            'services.evolution.key'
        );

        if ($this->url === '') {
            throw new RuntimeException(
                'EVOLUTION_API_URL não configurada.'
            );
        }

        if ($this->apiKey === '') {
            throw new RuntimeException(
                'EVOLUTION_API_KEY não configurada.'
            );
        }
    }

    public function criarInstancia(
        string $nomeInstancia
    ): array {
        $response = $this->request()
            ->post('/instance/create', [
                'instanceName' => $nomeInstancia,
                'integration' => 'WHATSAPP-BAILEYS',
                'qrcode' => true,
            ]);

        return $this->processarResposta(
            $response,
            'Não foi possível criar a instância.'
        );
    }

    public function conectar(
        string $nomeInstancia
    ): array {
        $response = $this->request()
            ->get(
                "/instance/connect/{$nomeInstancia}"
            );

        return $this->processarResposta(
            $response,
            'Não foi possível gerar o QR Code.'
        );
    }

    public function consultarEstado(
        string $nomeInstancia
    ): array {
        $response = $this->request()
            ->get(
                "/instance/connectionState/{$nomeInstancia}"
            );

        return $this->processarResposta(
            $response,
            'Não foi possível consultar o estado.'
        );
    }

    public function buscarInstancia(
        string $nomeInstancia
    ): array {
        $response = $this->request()
            ->get('/instance/fetchInstances', [
                'instanceName' => $nomeInstancia,
            ]);

        return $this->processarResposta(
            $response,
            'Não foi possível localizar a instância.'
        );
    }

    public function desconectar(
        string $nomeInstancia
    ): array {
        $response = $this->request()
            ->delete(
                "/instance/logout/{$nomeInstancia}"
            );

        return $this->processarResposta(
            $response,
            'Não foi possível desconectar a instância.'
        );
    }

    public function excluir(
        string $nomeInstancia
    ): array {
        $response = $this->request()
            ->delete(
                "/instance/delete/{$nomeInstancia}"
            );

        return $this->processarResposta(
            $response,
            'Não foi possível excluir a instância.'
        );
    }

    public function configurarWebhook(
        string $nomeInstancia,
        string $webhookUrl
    ): array {
        $response = $this->request()
            ->post("/webhook/set/{$nomeInstancia}", [
                'webhook' => [
                    'enabled' => true,
                    'url' => $webhookUrl,
                    'webhookByEvents' => false,
                    'webhookBase64' => false,
                    'events' => [
                        'MESSAGES_UPSERT',
                    ],
                ],
            ]);

        return $this->processarResposta(
            $response,
            'Não foi possível configurar o webhook da instância.'
        );
    }

    public function enviarTexto(
        string $nomeInstancia,
        string $numero,
        string $texto
    ): array {
        $response = $this->request()
            ->post("/message/sendText/{$nomeInstancia}", [
                'number' => $numero,
                'text' => $texto,
            ]);

        return $this->processarResposta(
            $response,
            'Não foi possível enviar a mensagem.'
        );
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->url)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => $this->apiKey,
            ])
            ->timeout(30)
            ->retry(
                2,
                500,
                throw: false
            );
    }

    private function processarResposta(
        Response $response,
        string $mensagemErro
    ): array {
        if (!$response->successful()) {
            throw new RuntimeException(
                $mensagemErro
                . ' HTTP '
                . $response->status()
                . ': '
                . $response->body()
            );
        }

        $dados = $response->json();

        return is_array($dados)
            ? $dados
            : [];
    }
}
