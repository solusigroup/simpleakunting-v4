<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name} {--domain=} {--email=}';
    protected $description = 'Create a new tenant with its own database';

    public function handle(): int
    {
        $name = $this->argument('name');
        $subdomain = $this->option('domain') ?: strtolower(str_replace(' ', '-', $name));
        $email = $this->option('email') ?: '';
        $centralDomain = env('CENTRAL_DOMAIN', 'simpleakunting4-0.test');

        $this->info("Creating tenant: {$name}");
        $this->info("Subdomain: {$subdomain}.{$centralDomain}");

        // Create the tenant in the central database
        $tenant = Tenant::create([
            'id' => strtolower(str_replace(' ', '-', $name)),
            'name' => $name,
            'email' => $email,
            'plan' => 'free',
        ]);

        // Store ONLY the subdomain (e.g. 'demo'), not the full FQDN
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        $this->info("Configuring tenant database...");

        try {
            $tenant->database()->makeCredentials();
            $manager = $tenant->database()->manager();
            $manager->createDatabase($tenant);
            $this->info("  ✓ Database created: " . $tenant->database()->getName());

            $this->info("  ✓ Running migrations...");
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
            ], $this->getOutput());

            $this->info("  ✓ Seeding database...");
            Artisan::call('tenants:seed', [
                '--tenants' => [$tenant->id],
            ], $this->getOutput());
        } catch (\Exception $e) {
            $this->error("Failed to provision database: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("✅ Tenant '{$name}' created successfully!");
        $this->info("   Tenant ID: {$tenant->id}");
        $this->info("   Database:  " . $tenant->database()->getName());
        $this->info("   URL:       http://{$subdomain}.{$centralDomain}");

        return Command::SUCCESS;
    }
}
