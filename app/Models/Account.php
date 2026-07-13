<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'documento',
        'telefone',
        'email',
        'ativo',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function restaurantes()
    {
        return $this->hasMany(Restaurante::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function hasModule(string $slug): bool
    {
        $subscription = $this->subscription;

        if (!$subscription || !$subscription->plan) {
            return false;
        }

        return $subscription->plan
            ->modules()
            ->where('slug', $slug)
            ->exists();
    }
}