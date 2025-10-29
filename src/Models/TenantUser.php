<?php

namespace MultiTenant\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class TenantUser extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'tenant_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
