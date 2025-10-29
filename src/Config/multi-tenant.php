<?php

return [
    'tenant_model' => \MultiTenant\Models\Tenant::class,
    'default_tenant' => null,
    'auto_create_database' => true,
    'tenant_connection_name' => 'tenant',
    'session_prefix' => 'tenant_',
];
