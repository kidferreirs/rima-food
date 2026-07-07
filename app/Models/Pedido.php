<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'restaurante_id',
        'cliente_id',
        'total',
        'status',
        'observacao',
        'origem',
        'forma_pagamento',
        'prioritario',
        'novo_em',
        'preparando_em',
        'pronto_em',
        'finalizado_em',
        'tipo_entrega',
        'taxa_entrega',
        'endereco_entrega',
        'token',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }
}