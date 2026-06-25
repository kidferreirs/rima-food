<?php

namespace App\Http\Controllers;

use App\Models\ConversaWhatsapp;
use App\Models\Restaurante;
use App\Services\RimaEngine;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index()
    {
        $restaurante = Restaurante::where(
            'user_id',
            auth()->id()
        )->first();

        $busca = request('busca');

        $filtro = request('filtro');

        $conversas = collect();

        $aguardando = 0;

        $atendimento = 0;

        $finalizadas = 0;

        $conversas = ConversaWhatsapp::where('restaurante_id', $restaurante->id);

            if ($busca) {

                $conversas->where(function ($query) use ($busca) {

                    $query->where('nome_cliente', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%" );
                });
            }

            if ($filtro) {

                if ($filtro === 'aguardando') {

                    $conversas->where( 'estado', 'inicio');
                }

                elseif ($filtro === 'finalizadas') {

                    $conversas->where('estado', 'finalizado');
                }

                elseif ($filtro === 'atendimento') {

                    $conversas->whereNotIn('estado',
                        [
                            'inicio',
                            'finalizado'
                        ]
                    );
                }
            }

            $conversas = $conversas
                ->latest()
                ->get();

            $aguardando = ConversaWhatsapp::where( 'restaurante_id', $restaurante->id)
            ->where('estado', 'inicio')
            ->count();

            $atendimento = ConversaWhatsapp::where( 'restaurante_id', $restaurante->id)
            ->whereNotIn('estado', [
                'inicio',
                'finalizado'
            ])
            ->count();

            $finalizadas = ConversaWhatsapp::where('restaurante_id', $restaurante->id)
            ->where('estado', 'finalizado')
            ->count();
    
        return view(
            'whatsapp.conversas',
            compact(
                'conversas',
                'aguardando',
                'atendimento',
                'finalizadas',
                'busca',
                'filtro'
            )
        );
    }

    public function show(ConversaWhatsapp $conversa)
    {
        return view(
            'whatsapp.show',
            compact('conversa')
        );
    }

    public function simular(Request $request, ConversaWhatsapp $conversa, RimaEngine $rima)
    {
        $request->validate([
            'mensagem' => 'required|string',
        ]);
        $rima->processar($conversa, $request->mensagem);
        return back();  
    }
}