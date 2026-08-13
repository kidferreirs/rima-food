<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Subscription;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalClientes = Account::count();

        $trialsAtivos = Subscription::where('status', 'trial')
            ->where('trial_ends_at', '>=', now())
            ->count();

        $assinaturasAtivas = Subscription::where('status', 'active')
            ->count();

        $cadastrosHoje = Account::whereDate('created_at', today())
            ->count();

        $clientesRecentes = Account::query()
            ->with([
                'subscription.plan',
                'restaurantes',
            ])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalClientes',
            'trialsAtivos',
            'assinaturasAtivas',
            'cadastrosHoje',
            'clientesRecentes'
        ));
    }
}