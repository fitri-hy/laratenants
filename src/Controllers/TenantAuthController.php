<?php

namespace MultiTenant\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MultiTenant\Models\TenantUser;
use MultiTenant\Services\TenantManager;

class TenantAuthController
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $tenant = TenantManager::getTenant();

        if (!$tenant) return response()->json(['error'=>'Tenant not found'], 404);

        if (Auth::guard('tenant')->attempt($credentials)) {
            return response()->json(['message'=>'Logged in']);
        }

        return response()->json(['error'=>'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();
        return response()->json(['message'=>'Logged out']);
    }
}
