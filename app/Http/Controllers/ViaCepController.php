<?php

namespace App\Http\Controllers;

use App\Services\Delivery\ViaCepService;

class ViaCepController extends Controller
{
    public function __invoke(string $cep, ViaCepService $service)
    {
        $endereco = $service->buscar($cep);

        if (!$endereco) {
            return response()->json([
                'success' => false
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $endereco
        ]);
    }
}