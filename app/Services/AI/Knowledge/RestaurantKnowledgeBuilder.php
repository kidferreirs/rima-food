<?php

namespace App\Services\AI\Knowledge;

use App\Models\Restaurante;

class RestaurantKnowledgeBuilder
{
    public function __construct(
        protected RestaurantKnowledge $restaurantKnowledge,
        protected CategoryKnowledge $categoryKnowledge,
        protected ProductKnowledge $productKnowledge,
        protected DeliveryKnowledge $deliveryKnowledge,
    ) {}

    public function build(Restaurante $restaurante): array
    {
        return [
            'version' => 1,

            'generated_at' => now()->toIso8601String(),

            'restaurant' => $this->restaurantKnowledge->dados($restaurante),

            'categories' => $this->categoryKnowledge->listar($restaurante),

            'products' => $this->productKnowledge->disponiveis($restaurante),

            'delivery' => $this->deliveryKnowledge->dados($restaurante),
        ];
    }
}