<?php

use App\Http\Controllers\WhatsAppDeviceController;
use Illuminate\Support\Facades\Route;

// API Routes for external integrations (no CSRF protection needed)
// MPWA Compatible API endpoints
Route::get('/send-message', [WhatsAppDeviceController::class, 'sendMessageApi'])->name('api.send-message');
Route::post('/send-message', [WhatsAppDeviceController::class, 'sendMessageApi'])->name('api.send-message-post');

// API Routes for Node.js WhatsApp server
Route::post('/whatsapp/save-incoming-message', [WhatsAppDeviceController::class, 'saveIncomingMessage'])->name('api.save-incoming-message');

