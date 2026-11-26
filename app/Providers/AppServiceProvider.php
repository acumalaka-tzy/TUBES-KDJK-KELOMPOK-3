<?php

namespace App\Providers;

use App\Http\Middleware\ResolveBroadcastUser;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes([
            'middleware' => ['web', ResolveBroadcastUser::class],
        ]);
    }
}
