<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user from environment variables (for production)
        // Falls back to test values for development
        // Uses create() directly instead of factory() to avoid Faker dependency in production
        $user = User::create([
            'name' => env('ADMIN_NAME', 'Test User'),
            'email' => env('ADMIN_EMAIL', 'test@example.com'),
            'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
            'role' => 'Administrator',
        ]);

        // Create a company associated with the user
        $company = Company::create([
            'name' => 'Test Company',
            'entity_type' => 'UMKM',
            'fiscal_start' => now()->startOfYear(),
            'user_id' => $user->id,
        ]);

        // Update the user with the company_id
        $user->update([
            'company_id' => $company->id,
        ]);
    }
}
