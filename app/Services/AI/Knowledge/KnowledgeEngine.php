<?php

namespace App\Services\AI\Knowledge;

class KnowledgeEngine
{
    public function __construct(
        private readonly ProductKnowledge $productKnowledge,
        private readonly CategoryKnowledge $categoryKnowledge,
        private readonly RestaurantKnowledge $restaurantKnowledge,
    ) {
    }

    /**
     * Acesso às informações dos produtos.
     */
    public function produtos(): ProductKnowledge
    {
        return $this->productKnowledge;
    }

    /**
     * Acesso às informações das categorias.
     */
    public function categorias(): CategoryKnowledge
    {
        return $this->categoryKnowledge;
    }

    /**
     * Acesso às informações do restaurante.
     */
    public function restaurante(): RestaurantKnowledge
    {
        return $this->restaurantKnowledge;
    }
}