<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantSchema
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('x-tenant');
        
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 422);
        }

        $tenant = Tenant::where('id', $tenantId)->first();

        if (! $tenant) {
            return response()->json(['message' => 'Invalid Tenant'], 422);
        }

        Config::set('database.connections.tenant.search_path', $tenant->schema_name);

        DB::purge('tenant');

        return $next($request);
    }
}
