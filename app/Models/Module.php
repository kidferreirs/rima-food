<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'categoria',
        'ativo',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_modules')
            ->withTimestamps();
    }
}