<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanModuleSeeder extends Seeder
{
    public function run(): void
    {
        $starter = [
            'cardapio-digital',
            'qr-code',
            'clientes',
            'produtos',
            'categorias',
        ];

        $pro = array_merge($starter, [
            'pedidos',
            'cozinha',
            'whatsapp',
        ]);

        $business = array_merge($pro, [
            'rima-ia',
            'delivery',
            'relatorios',
            'campanhas',
            'cupons',
            'fidelidade',
        ]);

        $enterprise = Module::pluck('slug')->toArray();

        $this->syncPlan('starter', $starter);
        $this->syncPlan('pro', $pro);
        $this->syncPlan('business', $business);
        $this->syncPlan('enterprise', $enterprise);
    }

    private function syncPlan(string $planSlug, array $moduleSlugs): void
    {
        $plan = Plan::where('slug', $planSlug)->firstOrFail();

        $moduleIds = Module::whereIn('slug', $moduleSlugs)
            ->pluck('id')
            ->toArray();

        $plan->modules()->sync($moduleIds);
    }
}