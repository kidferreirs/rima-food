<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;

abstract class BaseRestaurantController extends Controller
{
    protected function restaurante(): Restaurante
    {
        return app('restauranteAtual');
    }
}