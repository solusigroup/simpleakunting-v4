<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Map of roles and their module permissions.
     * key: role name
     * value: array of permissions (module.action)
     */
    public static function getPermissions(): array
    {
        return [
            'Administrator' => ['*'], // All access
            
            'Manajer' => [
                'sales.*', 'purchase.*', 'cash.*', 'journal.*', 
                'inventory.*', 'assets.*', 'internet.*', 
                'biological.*', 'manufacturing.*', 'budget.*', 
                'investor.*', 'reports.*'
            ],
            
            'Operator' => [
                'sales.view', 'sales.create',
                'purchase.view', 'purchase.create',
                'cash.view', 'cash.create',
                'journal.view', 'journal.create',
                'inventory.view', 'inventory.create',
                'assets.view', 'assets.create',
                'internet.view', 'internet.create',
                'biological.view', 'biological.create',
                'manufacturing.view', 'manufacturing.create',
                'budget.view', 'budget.create',
                'investor.view', 'investor.create',
                'reports.view',
            ],
            
            'Peninjau' => [
                'sales.view',
                'purchase.view',
                'cash.view',
                'journal.view',
                'inventory.view',
                'assets.view',
                'internet.view',
                'biological.view',
                'manufacturing.view',
                'budget.view',
                'investor.view',
                'reports.view',
            ],
        ];
    }

    /**
     * Check if a role has a specific permission.
     */
    public static function hasPermission(string $role, string $permission): bool
    {
        $map = self::getPermissions();
        $rolePermissions = $map[$role] ?? [];

        if (in_array('*', $rolePermissions)) {
            return true;
        }

        if (in_array($permission, $rolePermissions)) {
            return true;
        }

        // Handle wildcards like 'sales.*'
        $parts = explode('.', $permission);
        if (count($parts) === 2) {
            $wildcard = $parts[0] . '.*';
            if (in_array($wildcard, $rolePermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all unique permissions and their status for each role.
     */
    public static function getPermissionMatrix(): array
    {
        $permissions = self::getPermissions();
        $allPermissions = [];
        
        foreach ($permissions as $role => $perms) {
            foreach ($perms as $p) {
                if ($p !== '*') {
                    $allPermissions[] = $p;
                }
            }
        }
        
        // Add common actions that might be covered by wildcards
        $modules = ['sales', 'purchase', 'cash', 'journal', 'inventory', 'assets', 'internet', 'biological', 'manufacturing', 'budget', 'investor', 'reports'];
        $actions = ['view', 'create', 'edit', 'delete', 'post'];
        
        foreach ($modules as $m) {
            foreach ($actions as $a) {
                $allPermissions[] = "$m.$a";
            }
        }

        $uniquePermissions = array_unique($allPermissions);
        sort($uniquePermissions);

        $matrix = [];
        foreach ($uniquePermissions as $p) {
            $row = ['permission' => $p];
            foreach ($permissions as $role => $perms) {
                $row[$role] = self::hasPermission($role, $p);
            }
            $matrix[] = $row;
        }

        return $matrix;
    }
}
