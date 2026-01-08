<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WhatsAppDeviceController;
use App\Http\Controllers\SystemUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'check.user.active'])->name('dashboard');

Route::middleware(['auth', 'check.user.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin Only Routes - User Management
    Route::middleware(['admin'])->group(function () {
        Route::resource('users', UserManagementController::class);
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // User Approval Routes
        Route::get('/users-pending-approval', [UserManagementController::class, 'pendingApproval'])->name('users.pending-approval');
        Route::post('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [UserManagementController::class, 'reject'])->name('users.reject');
        
        // System Update Routes
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/update', [SystemUpdateController::class, 'index'])->name('update');
            Route::post('/check-updates', [SystemUpdateController::class, 'checkUpdates'])->name('check-updates');
            Route::post('/perform-update', [SystemUpdateController::class, 'performUpdate'])->name('perform-update');
            Route::post('/create-backup', [SystemUpdateController::class, 'createBackup'])->name('create-backup');
            Route::post('/restore-backup', [SystemUpdateController::class, 'restoreBackup'])->name('restore-backup');
            Route::delete('/delete-backup', [SystemUpdateController::class, 'deleteBackup'])->name('delete-backup');
        });
    });
    
    // WhatsApp Device Management Routes
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::resource('devices', WhatsAppDeviceController::class);
        Route::post('/devices/{device}/connect', [WhatsAppDeviceController::class, 'connect'])->name('devices.connect');
        Route::post('/devices/{device}/disconnect', [WhatsAppDeviceController::class, 'disconnect'])->name('devices.disconnect');
        Route::get('/devices/{device}/qr-code', [WhatsAppDeviceController::class, 'qrCode'])->name('devices.qr-code');
        Route::get('/devices/{device}/status', [WhatsAppDeviceController::class, 'status'])->name('devices.status');
        Route::post('/devices/{device}/send-message', [WhatsAppDeviceController::class, 'sendMessage'])->name('devices.send-message');
        Route::post('/devices/{device}/sync-contacts', [WhatsAppDeviceController::class, 'syncContacts'])->name('devices.sync-contacts');
        Route::get('/devices/{device}/contacts', [WhatsAppDeviceController::class, 'contacts'])->name('devices.contacts');
        Route::get('/devices/{device}/api-info', [WhatsAppDeviceController::class, 'apiInfo'])->name('devices.api-info');
        Route::get('/devices/{device}/web-interface', [WhatsAppDeviceController::class, 'webInterface'])->name('devices.web-interface');
        Route::get('/devices/{device}/recent-chats', [WhatsAppDeviceController::class, 'getRecentChats'])->name('devices.recent-chats');
        Route::get('/devices/{device}/messages/{chatId}', [WhatsAppDeviceController::class, 'getChatMessages'])->name('devices.messages');
    });
});

require __DIR__.'/auth.php';

// API Routes for external integrations (MPWA Compatible) - No CSRF needed
// These routes are in web.php but excluded from CSRF in bootstrap/app.php
Route::get('/send-message', [WhatsAppDeviceController::class, 'sendMessageApi'])->name('api.send-message');
Route::post('/send-message', [WhatsAppDeviceController::class, 'sendMessageApi'])->name('api.send-message-post');
Route::get('/api/send-message', [WhatsAppDeviceController::class, 'sendMessageApi'])->name('api.send-message-alt');
Route::post('/api/send-message', [WhatsAppDeviceController::class, 'sendMessageApi'])->name('api.send-message-post-alt');

// API Route moved to routes/api.php (no CSRF protection needed)

// Mock WhatsApp Server Routes (for testing when Node.js server is not available)
Route::prefix('mock-whatsapp')->group(function () {
    Route::get('/health', [App\Http\Controllers\MockWhatsAppController::class, 'health']);
    Route::post('/api/device/{deviceKey}/connect', [App\Http\Controllers\MockWhatsAppController::class, 'connect']);
    Route::post('/api/device/{deviceKey}/disconnect', [App\Http\Controllers\MockWhatsAppController::class, 'disconnect']);
    Route::get('/api/device/{deviceKey}/status', [App\Http\Controllers\MockWhatsAppController::class, 'status']);
    Route::get('/api/qr/{deviceKey}', [App\Http\Controllers\MockWhatsAppController::class, 'qrCode']);
});
