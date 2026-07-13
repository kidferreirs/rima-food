<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'valor',
        'trial_dias',
        'max_restaurantes',
        'max_usuarios',
        'ativo',
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'plan_modules')
            ->withTimestamps();
    }
}