<?php

use App\Http\Controllers\Api\RimaWhatsappWebhookController;
use App\Http\Controllers\AI\RestaurantKnowledgeController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/whatsapp/rima',
    RimaWhatsappWebhookController::class
)
    ->middleware('throttle:120,1')
    ->name('api.whatsapp.rima');

Route::get(
    '/ia/restaurantes/{instance}',
    RestaurantKnowledgeController::class
);