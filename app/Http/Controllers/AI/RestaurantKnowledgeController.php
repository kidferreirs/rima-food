<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use App\Services\AI\Knowledge\RestaurantKnowledgeBuilder;

class RestaurantKnowledgeController extends Controller
{
    public function __construct(
        protected RestaurantKnowledgeBuilder $builder
    ) {
    }

    public function __invoke(string $instance)
    {
        $restaurante = Restaurante::query()
            ->where('evolution_instance', $instance)
            ->firstOrFail();

        return response()->json(
            $this->builder->build($restaurante)
        );
    }
}