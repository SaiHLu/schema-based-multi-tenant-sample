<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantsMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {schema}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for a specific tenant schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schema = $this->argument('schema');

        DB::statement("SET search_path TO {$schema}");

        $this->call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'pgsql',
        ]);

        $this->info("Migrations for schema '{$schema}' completed.");
    }
}
