<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
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
        $tenant = Tenant::where('id', $request->header('x-tenant'))->first();

        if (! $tenant) {
            return response()->json(['message' => 'Invalid Tenant'], 422);
        }

        DB::statement("SET search_path TO {$tenant->schema_name}, public");

        return $next($request);
    }
}
