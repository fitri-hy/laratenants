<?php

namespace MultiTenant;

use Illuminate\Support\ServiceProvider;
use MultiTenant\Middleware\TenantMiddleware;

class MultiTenantServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/multi-tenant.php' => config_path('multi-tenant.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $router = $this->app['router'];
        $router->aliasMiddleware('tenant', TenantMiddleware::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/multi-tenant.php', 'multi-tenant');
    }
}
