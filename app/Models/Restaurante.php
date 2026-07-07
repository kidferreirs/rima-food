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
}