<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'nome' => 'Starter',
                'slug' => 'starter',
                'valor' => 39.90,
                'trial_dias' => 15,
                'max_restaurantes' => 1,
                'max_usuarios' => 1,
                'ativo' => true,
            ],
            [
                'nome' => 'Pro',
                'slug' => 'pro',
                'valor' => 79.90,
                'trial_dias' => 15,
                'max_restaurantes' => 1,
                'max_usuarios' => 3,
                'ativo' => true,
            ],
            [
                'nome' => 'Business',
                'slug' => 'business',
                'valor' => 149.90,
                'trial_dias' => 15,
                'max_restaurantes' => 3,
                'max_usuarios' => 10,
                'ativo' => true,
            ],
            [
                'nome' => 'Enterprise',
                'slug' => 'enterprise',
                'valor' => 0,
                'trial_dias' => 15,
                'max_restaurantes' => 999,
                'max_usuarios' => 999,
                'ativo' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}