<?php

namespace App\Observers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantObserver
{
    public function created(Tenant $tenant): void
    {
        // Create schema
        DB::statement("CREATE SCHEMA IF NOT EXISTS {$tenant->schema_name}");

        // Run tenant-specific migrations
        Artisan::call('tenants:migrate', [
            'schema' => $tenant->schema_name,
        ]);
    }
}
