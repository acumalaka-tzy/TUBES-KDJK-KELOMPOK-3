<?php

use App\Http\Controllers\BroadcastComparisonController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SecurityInformationController;
use App\Http\Controllers\SecurityLogController;
use App\Http\Controllers\SignalingController;
use App\Http\Controllers\UserIdentityController;
use App\Http\Controllers\WebRTCConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for SecureLink
|--------------------------------------------------------------------------
*/

// Public/Session Identity routes
Route::prefix('identity')->group(function () {
    Route::get('/me', [UserIdentityController::class, 'me'])->name('api.identity.me');
    Route::post('/setup', [UserIdentityController::class, 'setup'])->name('api.identity.setup');
    Route::put('/', [UserIdentityController::class, 'update'])->name('api.identity.update');
    Route::post('/heartbeat', [UserIdentityController::class, 'heartbeat'])->name('api.identity.heartbeat');
    Route::post('/logout', [UserIdentityController::class, 'logout'])->name('api.identity.logout');
});

// WebRTC ICE (STUN/TURN) Configuration
Route::get('/webrtc/ice-servers', [WebRTCConfigController::class, 'iceServers'])
    ->middleware(['identity'])
    ->name('api.webrtc.ice');

// Protected Security Information route (requires user session identity)
Route::prefix('security')->middleware(['identity'])->group(function () {
    Route::get('/info', [SecurityInformationController::class, 'sessionInfo'])->name('api.security.info');
});

// Protected Room routes (requires user session identity)
Route::prefix('rooms')->middleware(['identity'])->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('api.rooms.index');
    Route::post('/', [RoomController::class, 'store'])->name('api.rooms.store');
    Route::post('/join', [RoomController::class, 'join'])->name('api.rooms.join');
    Route::post('/validate-invite', [RoomController::class, 'validateInvite'])->name('api.rooms.validate_invite');
    Route::get('/{roomId}', [RoomController::class, 'show'])->name('api.rooms.show');
    Route::post('/{roomId}/leave', [RoomController::class, 'leave'])->name('api.rooms.leave');

    // Room member routes
    Route::middleware(['room.member'])->group(function () {
        Route::get('/{roomId}/invites', [RoomController::class, 'listInvites'])->name('api.rooms.invites.index');
        Route::post('/{roomId}/invite', [RoomController::class, 'createInvite'])->name('api.rooms.invite');
        Route::post('/{roomId}/invites/{inviteId}/revoke', [RoomController::class, 'revokeInvite'])
            ->middleware('room.creator')
            ->name('api.rooms.invites.revoke');
        Route::post('/{roomId}/signal', [SignalingController::class, 'send'])
            ->middleware('throttle:120,1')
            ->name('api.rooms.signal');
        Route::post('/{roomId}/broadcast-message', [BroadcastComparisonController::class, 'send'])
            ->middleware('throttle:60,1')
            ->name('api.rooms.broadcast');
        Route::post('/{roomId}/receipt', [BroadcastComparisonController::class, 'receipt'])
            ->middleware('throttle:120,1')
            ->name('api.rooms.receipt');
        Route::get('/{roomId}/security-info', [SecurityInformationController::class, 'roomInfo'])->name('api.rooms.security_info');
        Route::get('/{roomId}/security-logs', [SecurityLogController::class, 'index'])->name('api.rooms.security_logs.index');
        Route::post('/{roomId}/security-logs', [SecurityLogController::class, 'store'])->name('api.rooms.security_logs.store');
    });

    // Room creator admin routes
    Route::middleware(['room.creator'])->group(function () {
        Route::post('/{roomId}/kick', [RoomController::class, 'kick'])->name('api.rooms.kick');
        Route::post('/{roomId}/lock', [RoomController::class, 'toggleLock'])->name('api.rooms.lock');
        Route::put('/{roomId}/settings', [RoomController::class, 'updateSettings'])->name('api.rooms.settings');
        Route::get('/{roomId}/dynamic-pin', [RoomController::class, 'dynamicPin'])->name('api.rooms.dynamic_pin');
        Route::post('/{roomId}/dynamic-pin/rotate', [RoomController::class, 'rotateDynamicPin'])->name('api.rooms.dynamic_pin.rotate');
    });
});
