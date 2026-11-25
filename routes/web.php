<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserIdentityController;
use App\Http\Controllers\WebViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes for SecureLink Application
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [WebViewController::class, 'landing'])->name('landing');
Route::get('/demo', [WebViewController::class, 'demo'])->name('demo');

// Identity setup
Route::get('/setup', [WebViewController::class, 'setup'])->name('identity.setup');
Route::post('/setup', [UserIdentityController::class, 'setup'])->name('identity.setup.submit');
Route::post('/logout', [UserIdentityController::class, 'logout'])->name('identity.logout.web');

// Authenticated user web routes
Route::middleware(['identity'])->group(function () {
    Route::get('/dashboard', [WebViewController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [WebViewController::class, 'settings'])->name('settings');
    Route::post('/settings/profile', [UserIdentityController::class, 'update'])->name('identity.update.web');

    // Room creation & joining
    Route::get('/rooms/create', [WebViewController::class, 'createRoom'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store.web');
    Route::get('/rooms/join', [WebViewController::class, 'joinRoom'])->name('rooms.join');
    Route::post('/rooms/join', [RoomController::class, 'join'])->name('rooms.join.submit');

    // Interactive Chat Room
    Route::get('/rooms/{roomId}', [WebViewController::class, 'chatRoom'])->name('rooms.show');
    Route::post('/rooms/{roomId}/leave', [RoomController::class, 'leave'])->name('rooms.leave.web');
});
