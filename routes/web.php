<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestauranteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ConfiguracaoEntregaController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\CozinhaController;
use App\Http\Controllers\PedidoStatusController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('restaurantes', RestauranteController::class);
    Route::patch('/restaurantes/{restaurante}/status', [RestauranteController::class, 'alterarStatus'])->name('restaurantes.status');

    Route::resource('categorias', CategoriaController::class);
    Route::patch('/categorias/{categoria}/status', [CategoriaController::class, 'alterarStatus'])->name('categorias.status');

    Route::resource('produtos', ProdutoController::class);
    Route::patch('/produtos/{produto}/status', [ProdutoController::class, 'alterarStatus'])->name('produtos.status');
    
    Route::resource('clientes', ClienteController::class);
    Route::patch('/clientes/{cliente}/status', [ClienteController::class, 'alterarStatus'])->name('clientes.status');

    Route::resource('pedidos', PedidoController::class);
    Route::patch('/pedidos/{pedido}/status', [PedidoController::class, 'alterarStatus'])->name('pedidos.status');
    Route::patch('/pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])->name('pedidos.imprimir');
    Route::patch('/pedidos/{pedido}/status', [PedidoStatusController::class, 'update'])->middleware('auth')->name('pedidos.status');
    
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/exportar', [RelatorioController::class, 'exportar'])->name('relatorios.exportar');

    Route::get('/configuracoes/entregas', [ConfiguracaoEntregaController::class, 'index'])->name('configuracoes.entregas.index');
    Route::post('/configuracoes/entregas', [ConfiguracaoEntregaController::class, 'salvar'])->name('configuracoes.entregas.salvar');

    Route::get('/whatsapp/conversas', [WhatsappController::class, 'index'])->name('whatsapp.conversas.index');
    Route::get('/whatsapp/{conversa}',[WhatsappController::class, 'show'])->name('whatsapp.show');
    Route::post('/whatsapp/{conversa}/simular',[WhatsappController::class, 'simular'])->name('whatsapp.simular');
    
    Route::get('/cozinha', [CozinhaController::class, 'index'])->middleware('auth')->name('cozinha.index');
});

require __DIR__.'/auth.php';



