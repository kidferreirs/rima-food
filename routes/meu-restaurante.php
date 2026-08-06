// Adicione dentro do grupo com prefix('/r/{slug}'):
Route::get('/meu-restaurante', [RestauranteController::class, 'meuRestaurante'])
    ->name('restaurante.meu-restaurante.edit');

Route::put('/meu-restaurante', [RestauranteController::class, 'atualizarMeuRestaurante'])
    ->name('restaurante.meu-restaurante.update');
