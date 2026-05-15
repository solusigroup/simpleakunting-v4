<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display the listing of roles.
     */
    public function index()
    {
        $roles = Role::all();
        $matrix = PermissionHelper::getPermissionMatrix();
        
        return view('roles.index', compact('roles', 'matrix'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = PermissionHelper::getAllAvailablePermissions();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            if (!empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $slug) {
                    $permission = Permission::firstOrCreate([
                        'slug' => $slug,
                    ], [
                        'name' => $slug, // Simple fallback
                    ]);
                    $role->permissions()->attach($permission->id);
                }
            }

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat role: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = PermissionHelper::getAllAvailablePermissions();
        $rolePermissions = $role->permissions()->pluck('slug')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->is_system && $role->name === 'Super User') {
            return back()->with('error', 'Role Super User tidak dapat diubah.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            // Sync permissions
            $permissionIds = [];
            if (!empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $slug) {
                    $permission = Permission::firstOrCreate(['slug' => $slug], ['name' => $slug]);
                    $permissionIds[] = $permission->id;
                }
            }
            $role->permissions()->sync($permissionIds);

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'Role sistem tidak dapat dihapus.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh pengguna.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus');
    }
}
