<?php

use App\Http\Controllers\Api\RimaWhatsappWebhookController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/whatsapp/rima',
    RimaWhatsappWebhookController::class
)
    ->middleware('throttle:120,1')
    ->name('api.whatsapp.rima');