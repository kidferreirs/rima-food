<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanModuleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Rima Menu
        |--------------------------------------------------------------------------
        */
        $menu = [
            'cardapio-digital',
            'qr-code',
            'clientes',
            'produtos',
            'categorias',
            'delivery',
        ];

        /*
        |--------------------------------------------------------------------------
        | Rima Menu + IA
        |--------------------------------------------------------------------------
        */
        $menuIa = array_merge($menu, [
            'pedidos',
            'whatsapp',
            'rima-ia',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Rima Food
        |--------------------------------------------------------------------------
        */
        $food = array_merge($menuIa, [
            'mesas',
            'cozinha',
            'relatorios',
            'campanhas',
            'cupons',
            'fidelidade',
            'pagamentos',
        ]);

        $this->syncPlan('menu', $menu);
        $this->syncPlan('menu-ia', $menuIa);
        $this->syncPlan('food', $food);
    }

    private function syncPlan(
        string $planSlug,
        array $moduleSlugs
    ): void {
        $plan = Plan::where('slug', $planSlug)->firstOrFail();

        $moduleIds = Module::whereIn('slug', $moduleSlugs)
            ->pluck('id')
            ->toArray();

        $plan->modules()->sync($moduleIds);
    }
}