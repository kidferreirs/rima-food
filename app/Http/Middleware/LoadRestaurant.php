<?php

namespace App\Http\Middleware;

use App\Models\Restaurante;
use Closure;
use Illuminate\Http\Request;

class LoadRestaurant
{
    public function handle(Request $request, Closure $next)
    {
        $restaurante = Restaurante::where('slug', $request->route('slug'))
            ->where('user_id', auth()->id())
            ->firstOrFail();

        app()->instance('restauranteAtual', $restaurante);

        view()->share('restauranteAtual', $restaurante);

        return $next($request);
    }
}