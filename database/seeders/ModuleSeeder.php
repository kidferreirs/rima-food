<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'nome' => 'Cardápio Digital',
                'slug' => 'cardapio-digital',
                'categoria' => 'Atendimento',
                'descricao' => 'Catálogo online para compartilhar com clientes.',
            ],
            [
                'nome' => 'QR Code',
                'slug' => 'qr-code',
                'categoria' => 'Atendimento',
                'descricao' => 'QR Code para acesso rápido ao cardápio.',
            ],
            [
                'nome' => 'Mesas',
                'slug' => 'mesas',
                'categoria' => 'Atendimento',
                'descricao' => 'Pedidos vinculados a mesas ou locais de atendimento.',
            ],
            [
                'nome' => 'Pedidos',
                'slug' => 'pedidos',
                'categoria' => 'Operação',
                'descricao' => 'Gestão de pedidos internos e online.',
            ],
            [
                'nome' => 'Cozinha',
                'slug' => 'cozinha',
                'categoria' => 'Operação',
                'descricao' => 'Central da cozinha para acompanhamento dos pedidos.',
            ],
            [
                'nome' => 'Delivery',
                'slug' => 'delivery',
                'categoria' => 'Operação',
                'descricao' => 'Configurações e operação de entrega.',
            ],
            [
                'nome' => 'Clientes',
                'slug' => 'clientes',
                'categoria' => 'Gestão',
                'descricao' => 'Cadastro e gestão de clientes.',
            ],
            [
                'nome' => 'Produtos',
                'slug' => 'produtos',
                'categoria' => 'Gestão',
                'descricao' => 'Cadastro e gestão de produtos.',
            ],
            [
                'nome' => 'Categorias',
                'slug' => 'categorias',
                'categoria' => 'Gestão',
                'descricao' => 'Organização dos produtos em categorias.',
            ],
            [
                'nome' => 'Relatórios',
                'slug' => 'relatorios',
                'categoria' => 'Gestão',
                'descricao' => 'Relatórios financeiros e operacionais.',
            ],
            [
                'nome' => 'WhatsApp',
                'slug' => 'whatsapp',
                'categoria' => 'IA',
                'descricao' => 'Atendimento e conversas via WhatsApp.',
            ],
            [
                'nome' => 'Rima IA',
                'slug' => 'rima-ia',
                'categoria' => 'IA',
                'descricao' => 'Inteligência artificial integrada ao atendimento.',
            ],
            [
                'nome' => 'Campanhas',
                'slug' => 'campanhas',
                'categoria' => 'Marketing',
                'descricao' => 'Campanhas e ações comerciais.',
            ],
            [
                'nome' => 'Cupons',
                'slug' => 'cupons',
                'categoria' => 'Marketing',
                'descricao' => 'Cupons de desconto e promoções.',
            ],
            [
                'nome' => 'Fidelidade',
                'slug' => 'fidelidade',
                'categoria' => 'Marketing',
                'descricao' => 'Programa de fidelidade para clientes.',
            ],
            [
                'nome' => 'Pagamentos',
                'slug' => 'pagamentos',
                'categoria' => 'Financeiro',
                'descricao' => 'Controle e integração de pagamentos.',
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}