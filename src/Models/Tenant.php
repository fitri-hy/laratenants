<?php

namespace MultiTenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'database',
    ];

    public function isWildcard(): bool
    {
        return str_contains($this->domain, '{tenant}');
    }

    public function users()
    {
        return $this->hasMany(TenantUser::class);
    }

    public function createDatabase(): void
    {
        if ($this->database) {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$this->database}`");
        }
    }
}
