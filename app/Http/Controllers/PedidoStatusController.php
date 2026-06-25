<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoStatusController extends Controller
{
    public function update(Request $request, Pedido $pedido)
    {
        $pedido->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status atualizado.');
    }
}