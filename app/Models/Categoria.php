<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = [
        'restaurante_id',
        'nome',
        'ativo',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}