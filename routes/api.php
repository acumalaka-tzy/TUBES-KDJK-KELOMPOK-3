<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserIdentityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for SecureLink
|--------------------------------------------------------------------------
*/

// Identity routes
Route::prefix('identity')->group(function () {
    Route::get('/me', [UserIdentityController::class, 'me'])->name('api.identity.me');
    Route::post('/setup', [UserIdentityController::class, 'setup'])->name('api.identity.setup');
    Route::put('/', [UserIdentityController::class, 'update'])->name('api.identity.update');
    Route::post('/heartbeat', [UserIdentityController::class, 'heartbeat'])->name('api.identity.heartbeat');
    Route::post('/logout', [UserIdentityController::class, 'logout'])->name('api.identity.logout');
});

// Room routes
Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('api.rooms.index');
    Route::post('/', [RoomController::class, 'store'])->name('api.rooms.store');
    Route::post('/join', [RoomController::class, 'join'])->name('api.rooms.join');
    Route::get('/{roomId}', [RoomController::class, 'show'])->name('api.rooms.show');
    Route::post('/{roomId}/leave', [RoomController::class, 'leave'])->name('api.rooms.leave');
    Route::post('/{roomId}/kick', [RoomController::class, 'kick'])->name('api.rooms.kick');
    Route::post('/{roomId}/lock', [RoomController::class, 'toggleLock'])->name('api.rooms.lock');
    Route::put('/{roomId}/settings', [RoomController::class, 'updateSettings'])->name('api.rooms.settings');
    Route::post('/{roomId}/invite', [RoomController::class, 'createInvite'])->name('api.rooms.invite');
});
