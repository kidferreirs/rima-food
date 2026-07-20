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
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\MenuPedidoController;
use App\Http\Controllers\CardapioImportController;
use App\Http\Controllers\GarcomCardapioController;
use App\Http\Controllers\ViaCepController;
use App\Http\Controllers\DeliveryQuoteController;


Route::get('/cadastro', [OnboardingController::class, 'create'])->name('saas.cadastro');

Route::post('/cadastro', [OnboardingController::class, 'store'])->name('saas.cadastro.store');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $restaurante = \App\Models\Restaurante::where('user_id', auth()->id())
        ->latest()
        ->first();

    if (!$restaurante) {
        return redirect()->route('restaurantes.create');
    }

    return redirect()->route('restaurante.dashboard', $restaurante->slug);
})->middleware(['auth', 'verified'])->name('dashboard');

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

    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/exportar', [RelatorioController::class, 'exportar'])->name('relatorios.exportar');

    Route::get('/configuracoes/entregas', [ConfiguracaoEntregaController::class, 'index'])->name('configuracoes.entregas.index');
    Route::post('/configuracoes/entregas', [ConfiguracaoEntregaController::class, 'salvar'])->name('configuracoes.entregas.salvar');

    Route::get('/whatsapp/conversas', [WhatsappController::class, 'index'])->name('whatsapp.conversas.index');
    Route::get('/whatsapp/{conversa}', [WhatsappController::class, 'show'])->name('whatsapp.show');
    Route::post('/whatsapp/{conversa}/simular', [WhatsappController::class, 'simular'])->name('whatsapp.simular');

    Route::middleware(['auth', 'restaurante'])->prefix('/r/{slug}')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('restaurante.dashboard');
        Route::resource('categorias', CategoriaController::class)->names('restaurante.categorias');
        Route::resource('produtos', ProdutoController::class)->names('restaurante.produtos');
        Route::resource('clientes', ClienteController::class)->names('restaurante.clientes');

        Route::resource('pedidos', PedidoController::class)->names('restaurante.pedidos');
        Route::patch('/pedidos/{pedido}/status', [PedidoController::class, 'alterarStatus'])->name('restaurante.pedidos.status');
        Route::patch('/pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('restaurante.pedidos.cancelar');
        Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])->name('restaurante.pedidos.imprimir');


        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('restaurante.relatorios.index');
        Route::get('/cozinha', [CozinhaController::class, 'index'])->name('restaurante.cozinha.index');

        Route::patch('/categorias/{categoria}/status', [CategoriaController::class, 'alterarStatus'])->name('restaurante.categorias.status');
        Route::patch('/produtos/{produto}/status', [ProdutoController::class, 'alterarStatus'])->name('restaurante.produtos.status');
        Route::patch('/clientes/{cliente}/status', [ClienteController::class, 'alterarStatus'])->name('restaurante.clientes.status');

        Route::get('/relatorios/exportar', [RelatorioController::class, 'exportar'])->name('restaurante.relatorios.exportar');

        Route::get('/whatsapp/conversas', [WhatsappController::class, 'index'])->name('restaurante.whatsapp.index');
        Route::get('/whatsapp/{conversa}', [WhatsappController::class, 'show'])->name('restaurante.whatsapp.show');
        Route::post('/whatsapp/{conversa}/simular', [WhatsappController::class, 'simular'])->name('restaurante.whatsapp.simular');

        Route::get('/configuracoes/entregas', [ConfiguracaoEntregaController::class, 'index'])->name('restaurante.configuracoes.entregas.index');
        Route::post('/configuracoes/entregas', [ConfiguracaoEntregaController::class, 'salvar'])->name('restaurante.configuracoes.entregas.salvar');

        Route::get('/cardapio-digital', [RestauranteController::class, 'cardapioDigital'])->name('restaurante.cardapio');

        Route::get('/importar-cardapio', [CardapioImportController::class, 'index'])->name('restaurante.importacao.cardapio');
        Route::post('/importar-cardapio/preview', [CardapioImportController::class, 'preview'])->name('restaurante.importacao.preview');
        Route::post('/importar-cardapio/importar', [CardapioImportController::class, 'importar'])->name('restaurante.importacao.importar');

        Route::get('/importar-cardapio/modelo', [CardapioImportController::class, 'modelo'])->name('restaurante.importacao.modelo');
    });
});

    Route::get('/menu/{slug}', [MenuController::class, 'show'])->name('menu.show');
    Route::post('/menu/{slug}/garcom', [GarcomCardapioController::class, 'conversar'])->name('menu.garcom.conversar');
    Route::get('/menu/{slug}/checkout', [MenuPedidoController::class, 'checkout'])->name('menu.checkout');
    Route::post('/menu/{slug}/pedido', [MenuPedidoController::class, 'store'])->name('menu.pedido.store');
    Route::post('/menu/{slug}/delivery/quote', DeliveryQuoteController::class)->name('menu.delivery.quote');

    Route::get('/pedido/{token}/sucesso', [MenuPedidoController::class, 'sucesso'])->name('pedido.sucesso');

    Route::get('/viacep/{cep}', ViaCepController::class)->name('viacep.buscar');

require __DIR__ . '/auth.php';