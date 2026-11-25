<?php

use App\Http\Controllers\UserIdentityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for SecureLink
|--------------------------------------------------------------------------
*/

Route::prefix('identity')->group(function () {
    Route::get('/me', [UserIdentityController::class, 'me'])->name('api.identity.me');
    Route::post('/setup', [UserIdentityController::class, 'setup'])->name('api.identity.setup');
    Route::put('/', [UserIdentityController::class, 'update'])->name('api.identity.update');
    Route::post('/heartbeat', [UserIdentityController::class, 'heartbeat'])->name('api.identity.heartbeat');
    Route::post('/logout', [UserIdentityController::class, 'logout'])->name('api.identity.logout');
});
