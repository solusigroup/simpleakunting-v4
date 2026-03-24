<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database with default company and admin user.
     */
    public function run(): void
    {
        // Create default admin user first because companies table needs user_id
        $user = User::create([
            'name' => 'Administrator',
            'email' => tenant('email') ?: 'admin@' . tenant('id') . '.test',
            'password' => Hash::make('password'),
            'role' => 'Administrator',
            // company_id is nullable, will update below
        ]);

        // Create default company for this tenant
        $company = Company::create([
            'user_id' => $user->id,
            'name' => tenant('name') ?? 'My Company',
            'address' => '-',
            'phone' => '-',
            'email' => tenant('email') ?? '-',
            'entity_type' => 'UMKM',
            'fiscal_start' => date('Y-01-01'),
        ]);

        // Update user with company_id
        $user->update(['company_id' => $company->id]);
    }
}
