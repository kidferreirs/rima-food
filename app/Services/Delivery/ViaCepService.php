<?php

namespace App\Services\Delivery;

use Illuminate\Support\Facades\Http;

class ViaCepService
{
    public function buscar(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        $response = Http::timeout(10)
            ->get("https://viacep.com.br/ws/{$cep}/json/");

        if (!$response->successful()) {
            return null;
        }

        $dados = $response->json();

        if (isset($dados['erro'])) {
            return null;
        }

        return [
            'cep' => $dados['cep'],
            'logradouro' => $dados['logradouro'],
            'bairro' => $dados['bairro'],
            'cidade' => $dados['localidade'],
            'estado' => $dados['uf'],
        ];
    }
}