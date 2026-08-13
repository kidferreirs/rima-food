<?php

namespace Database\Seeders;

use App\Models\Servico;
use Illuminate\Database\Seeder;

class ServicoSeeder extends Seeder
{
    public function run(): void
    {
        $servicos = [
            [
                'nome' => 'Rima Food',
                'slug' => 'rima-food',
                'descricao' => 'Sistema completo para restaurantes com cardápio digital, pedidos, cozinha, relatórios e atendimento inteligente.',
                'recorrente' => true,
                'ativo' => true,
            ],
            [
                'nome' => 'Rima IA',
                'slug' => 'rima-ia',
                'descricao' => 'Assistente inteligente para atendimento automatizado via WhatsApp.',
                'recorrente' => true,
                'ativo' => true,
            ],
            [
                'nome' => 'Jimmy',
                'slug' => 'jimmy',
                'descricao' => 'Prospecção inteligente e automação comercial para busca e abordagem de clientes.',
                'recorrente' => true,
                'ativo' => true,
            ],
            [
                'nome' => 'Landing Page',
                'slug' => 'landing-page',
                'descricao' => 'Criação de páginas profissionais para divulgação, captação de clientes e conversão.',
                'recorrente' => false,
                'ativo' => true,
            ],
            [
                'nome' => 'Vídeo Animado',
                'slug' => 'video-animado',
                'descricao' => 'Criação de vídeos promocionais e animações para redes sociais.',
                'recorrente' => false,
                'ativo' => true,
            ],
        ];

        foreach ($servicos as $servico) {
            Servico::updateOrCreate(
                ['slug' => $servico['slug']],
                $servico
            );
        }
    }
}