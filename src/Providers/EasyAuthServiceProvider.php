<?php

namespace Paverblock\Easyauth\Providers;

use Illuminate\Support\ServiceProvider;

class EasyAuthServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../routes.php';
        }

        $this->publishes([
            __DIR__.'/../config/easyauth.php' => config_path('easyauth.php'),
        ],'config');

        $this->publishes([
            __DIR__.'/../views' => base_path('resources/views/'),
        ]);

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('Tymon\JWTAuth\Providers\JWTAuthServiceProvider');
        $this->app->register('EvanDarwin\JSend\Laravel\ServiceProvider');
    }
}