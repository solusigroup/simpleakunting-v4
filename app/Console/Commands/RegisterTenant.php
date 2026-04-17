<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class RegisterTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:register {id : The ID of the tenant (slug)} {domain : The full domain for the tenant} {--name= : The name of the company}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register an existing manually created database as a tenant without attempting to create it.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $domain = $this->argument('domain');
        $name = $this->option('name') ?? $id;

        $this->info("Registering tenant: {$id} with domain: {$domain}");

        // Check if already exists
        if (DB::table('tenants')->where('id', $id)->exists()) {
            $this->error("Tenant with ID {$id} already exists in the central database.");
            return 1;
        }

        try {
            DB::transaction(function () use ($id, $domain, $name) {
                // 1. Insert into tenants table manually to bypass creation events
                // Stancl Tenancy by default stores everything in a 'data' JSON column 
                // but custom columns are at the top level.
                DB::table('tenants')->insert([
                    'id' => $id,
                    'name' => $name,
                    'data' => json_encode([
                        'name' => $name,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Insert into domains table
                DB::table('domains')->insert([
                    'domain' => $domain,
                    'tenant_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $this->info("Successfully registered tenant '{$id}'.");
            $this->info("Database expected: " . config('tenancy.database.prefix') . $id);
            $this->warn("Next steps:");
            $this->line("1. Ensure the database " . config('tenancy.database.prefix') . $id . " exists on your shared hosting.");
            $this->line("2. Run: php artisan tenants:migrate --tenant={$id}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to register tenant: " . $e->getMessage());
            return 1;
        }
    }
}
