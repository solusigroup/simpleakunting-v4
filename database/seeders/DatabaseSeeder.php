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
        // Create central admin user from environment variables
        // This user is for the main management portal
        \App\Models\AdminUser::create([
            'name' => env('ADMIN_NAME', 'SimpleAkunting Admin'),
            'email' => env('ADMIN_EMAIL', 'admin@simpleakunting.biz.id'),
            'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
            'role' => 'superadmin',
        ]);
    }
}
