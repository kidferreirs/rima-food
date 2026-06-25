<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversaWhatsapp extends Model
{
    protected $fillable = [
        'restaurante_id',
        'telefone',
        'nome_cliente',
        'estado',
        'carrinho',
        'tipo_entrega',
        'endereco_entrega',
        'forma_pagamento',
        'pedido_confirmado',
        'pedido_id',
        'ultima_mensagem',
        'ultima_interacao',
        'atendimento_humano',
        'historico',
    ];

    protected $casts = [
        'carrinho' => 'array',
        'pedido_confirmado' => 'boolean',
        'ultima_interacao' => 'datetime',
        'atendimento_humano' => 'boolean',
        'historico' => 'array',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}