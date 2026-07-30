<?php

use App\Http\Controllers\Api\RimaWhatsappWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AI\RestaurantKnowledgeController;

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