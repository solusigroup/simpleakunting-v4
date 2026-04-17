<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // e.g. 'sales.view'
            $table->string('module')->nullable(); // e.g. 'Sales'
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'permission_id']);
        });

        // Add role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->after('role')->nullable()->constrained('roles')->onDelete('set null');
        });

        // Seed default roles and permissions
        $this->seedDefaults();
    }

    /**
     * Seed default roles and permissions based on PermissionHelper.
     */
    private function seedDefaults(): void
    {
        $permissions = \App\Helpers\PermissionHelper::getAllAvailablePermissions();
        $permMap = [];

        foreach ($permissions as $p) {
            $permission = \App\Models\Permission::firstOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name'], 'module' => $p['module']]
            );
            $permMap[$p['slug']] = $permission->id;
        }

        $rolesData = [
            'Super User' => [
                'description' => 'Akses penuh ke seluruh sistem tanpa batasan.',
                'is_system' => true,
                'perms' => ['*']
            ],
            'Administrator' => [
                'description' => 'Administrator aplikasi dengan akses pengaturan sistem.',
                'is_system' => true,
                'perms' => ['*']
            ],
            'Manajer' => [
                'description' => 'Manajer operasional dengan hak persetujuan transaksi.',
                'is_system' => true,
                'perms' => [
                    'sales.*', 'purchase.*', 'cash.*', 'journal.*', 
                    'inventory.*', 'assets.*', 'internet.*', 
                    'biological.*', 'manufacturing.*', 'budget.*', 
                    'investor.*', 'reports.*'
                ]
            ],
            'Operator' => [
                'description' => 'Staff operasional untuk penginputan data harian.',
                'is_system' => true,
                'perms' => [
                    'sales.view', 'sales.create', 'purchase.view', 'purchase.create',
                    'cash.view', 'cash.create', 'journal.view', 'journal.create',
                    'inventory.view', 'inventory.create', 'assets.view', 'assets.create',
                    'internet.view', 'internet.create', 'biological.view', 'biological.create',
                    'manufacturing.view', 'manufacturing.create', 'budget.view', 'budget.create',
                    'investor.view', 'investor.create', 'reports.view',
                ]
            ],
            'Peninjau' => [
                'description' => 'Hak akses terbatas hanya untuk memantau data dan laporan.',
                'is_system' => true,
                'perms' => [
                    'sales.view', 'purchase.view', 'cash.view', 'journal.view',
                    'inventory.view', 'assets.view', 'internet.view', 'biological.view',
                    'manufacturing.view', 'budget.view', 'investor.view', 'reports.view',
                ]
            ],
        ];

        foreach ($rolesData as $roleName => $data) {
            $role = \App\Models\Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $data['description'], 'is_system' => $data['is_system']]
            );

            // Sync permissions
            $ids = [];
            foreach ($data['perms'] as $slug) {
                if ($slug === '*') {
                    // For '*' we assign all current permissions
                    $ids = array_values($permMap);
                    break;
                }
                if (isset($permMap[$slug])) {
                    $ids[] = $permMap[$slug];
                }
            }
            $role->permissions()->sync($ids);

            // Update existing users with this role string
            \App\Models\User::where('role', $roleName)->update(['role_id' => $role->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
