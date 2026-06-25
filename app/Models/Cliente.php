<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [

        'restaurante_id',

        'nome',

        'telefone',

        'email',

        'observacao',

        'ultimo_pedido',

        'total_gasto',

        'ativo',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}