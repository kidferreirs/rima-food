<?php

namespace App\Services\AI\Knowledge;

class KnowledgeEngine
{
    public function __construct(
        private readonly ProductKnowledge $productKnowledge,
        private readonly ProductOptionKnowledge $productOptionKnowledge,
        private readonly CategoryKnowledge $categoryKnowledge,
        private readonly RestaurantKnowledge $restaurantKnowledge,
    ) {
    }

    public function produtos(): ProductKnowledge
    {
        return $this->productKnowledge;
    }

    public function opcoes(): ProductOptionKnowledge
    {
        return $this->productOptionKnowledge;
    }

    public function categorias(): CategoryKnowledge
    {
        return $this->categoryKnowledge;
    }

    public function restaurante(): RestaurantKnowledge
    {
        return $this->restaurantKnowledge;
    }
}