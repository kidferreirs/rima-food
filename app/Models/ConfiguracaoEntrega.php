<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoEntrega extends Model
{
    protected $fillable = [
        'restaurante_id',
        'ate_5km',
        'ate_10km',
        'acima_10km',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }
}