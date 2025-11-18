<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantFromCustomer
{
    /**
     * Handle an incoming request.
     *
     * For API requests, we ensure the tenant context is set based on the authenticated customer
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authenticated customer (from Sanctum)
        $customer = $request->user();

        if ($customer && $customer->tenant_id) {
            // Set the current tenant based on customer's tenant_id
            // This ensures all database queries are scoped to the correct tenant
            $request->attributes->set('tenant_id', $customer->tenant_id);
        }

        return $next($request);
    }
}
