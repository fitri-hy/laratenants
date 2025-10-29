<?php

namespace MultiTenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use MultiTenant\Services\TenantManager;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $path = $request->path();

        $tenant = TenantManager::resolveTenant($host, $path);

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        if ($tenant->isWildcard()) {
            $subdomain = explode('.', $host)[0];
            $tenant->name = "Tenant " . ucfirst($subdomain);
            $tenant->domain = str_replace('{tenant}', $subdomain, $tenant->domain);
        }

        if (config('multi-tenant.auto_create_database') && method_exists($tenant, 'createDatabase')) {
            $tenant->createDatabase();
        }

        TenantManager::setTenant($tenant);

        return $next($request);
    }
}
