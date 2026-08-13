<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteServico extends Model
{
    protected $table = 'cliente_servicos';

    protected $fillable = [
        'account_id',
        'servico_id',
        'status',
        'valor',
        'tipo_cobranca',
        'data_inicio',
        'data_fim',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}