<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTenantMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:make-migration {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration file for tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');

        $this->call('make:migration', [
            'name' => "{$table}",
            '--path' => 'database/migrations/tenant',
        ]);

        $this->info("Migration for table '{$table}' created in database/migrations/tenant.");
    }
}
