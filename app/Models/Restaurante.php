<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'telefone',
        'documento',
        'email',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'instagram',
        'site',
        'taxa_entrega',
        'tempo_medio',
        'logo',
        'ativo',
        'delivery',
        'retirada',
        'consumo_local',
        'quantidade_mesas',
        'slug',
        'banner',
        'abre_as',
        'fecha_as',
        'google_rating',
        'google_reviews_total',
        'google_maps_url',
        'plano',
    ];

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function produtos()
    {
        return $this->hasManyThrough(
            Produto::class,
            Categoria::class
        );
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function campanhas()
    {
        return $this->hasMany(Campanha::class);
    }

    public function configuracaoEntrega()
    {
        return $this->hasOne(ConfiguracaoEntrega::class);
    }

    public function temIA(): bool
    {
        return in_array($this->plano, [
            'MENU_IA',
            'FOOD'
        ]);
    }

    public function ehFood(): bool
    {
        return $this->plano === 'FOOD';
    }

    public function ehMenu(): bool
    {
        return $this->plano === 'MENU';
    }
}