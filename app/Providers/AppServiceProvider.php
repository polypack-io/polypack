<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        // if (config('app.env') === 'production') {
        URL::forceScheme('https');
        // }

        if (config('app.env') === 'local') {
            Event::listen(MigrationsEnded::class, function () {
                Artisan::call('ide-helper:models -N');
            });
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
