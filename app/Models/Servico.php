<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'recorrente',
        'ativo',
    ];

    protected $casts = [
        'recorrente' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function clientes()
    {
        return $this->belongsToMany(
            Account::class,
            'cliente_servicos'
        )
            ->withPivot([
                'status',
                'valor',
                'tipo_cobranca',
                'data_inicio',
                'data_fim',
                'observacoes',
            ])
            ->withTimestamps();
    }
}