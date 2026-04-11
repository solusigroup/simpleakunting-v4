<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin_users table with the superadmin user.
     */
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'kurniawan@simpleakunting.com'],
            [
                'name' => 'Kurniawan',
                'password' => Hash::make('5@8@12Yaa'),
                'role' => 'superadmin',
            ]
        );
    }
}
