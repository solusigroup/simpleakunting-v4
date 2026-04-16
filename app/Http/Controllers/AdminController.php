<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of all tenants.
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->get();
        $centralDomain = env('CENTRAL_DOMAIN', 'simpleakunting4-0.test');

        return view('admin.index', compact('tenants', 'centralDomain'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        return view('admin.create');
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subdomain' => 'required|string|max:63|alpha_dash|unique:domains,domain',
            'plan' => 'required|in:free,starter,pro',
        ]);

        $tenantId = strtolower(str_replace(' ', '-', $request->name));

        $tenant = Tenant::create([
            'id' => $tenantId,
            'name' => $request->name,
            'email' => $request->email,
            'plan' => $request->plan,
        ]);

        $tenant->domains()->create([
            'domain' => strtolower($request->subdomain),
        ]);

        try {
            $tenant->database()->makeCredentials();
            $tenant->database()->manager()->createDatabase($tenant);
            Artisan::call('tenants:migrate', ['--tenants' => [$tenant->id]]);
            Artisan::call('tenants:seed', ['--tenants' => [$tenant->id]]);
        } catch (\Exception $e) {
            return back()->with('error', 'Tenant created but database provisioning failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.index')->with('success', "Tenant '{$request->name}' berhasil dibuat!");
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load('domains');
        $centralDomain = env('CENTRAL_DOMAIN', 'simpleakunting4-0.test');

        // Get database info
        $dbName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');

        return view('admin.show', compact('tenant', 'centralDomain', 'dbName'));
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        $tenant->load('domains');
        $centralDomains = config('tenancy.central_domains', []);
        $centralDomain = $centralDomains[0] ?? env('CENTRAL_DOMAIN', 'simpleakunting-v4.test');

        return view('admin.edit', compact('tenant', 'centralDomain'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subdomain' => 'required|string|max:63|alpha_dash',
            'plan' => 'required|in:free,starter,pro',
            'tenant_user_name' => 'nullable|string|max:255',
            'tenant_user_email' => 'nullable|email|max:255',
            'tenant_user_password' => 'nullable|string|min:8',
        ]);

        $tenant->update([
            'name' => $request->name,
            'email' => $request->email,
            'plan' => $request->plan,
        ]);

        // Update subdomain if changed
        $newSubdomain = strtolower($request->subdomain);
        $currentDomain = $tenant->domains->first();
        if ($currentDomain && $currentDomain->domain !== $newSubdomain) {
            // Check if new domain is already taken
            if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $newSubdomain)->where('tenant_id', '!=', $tenant->id)->exists()) {
                return back()->withErrors(['subdomain' => 'Subdomain ini sudah digunakan oleh tenant lain.'])->withInput();
            }
            $currentDomain->update(['domain' => $newSubdomain]);
        }

        // Handle tenant user creation/reset if password is provided
        if ($request->filled('tenant_user_password')) {
            $userName = $request->tenant_user_name ?: 'Administrator';
            $userEmail = $request->tenant_user_email ?: $tenant->email;
            $password = Hash::make($request->tenant_user_password);

            $tenant->run(function () use ($userName, $userEmail, $password) {
                $company = Company::first();
                
                $user = User::updateOrCreate(
                    ['email' => $userEmail],
                    [
                        'name' => $userName,
                        'password' => $password,
                        'role' => 'Administrator',
                        'company_id' => $company?->id,
                    ]
                );

                // If company exists but has no user, or if we want to ensure this admin owns it
                if ($company && !$company->user_id) {
                    $company->update(['user_id' => $user->id]);
                }
            });

            return redirect()->route('admin.show', $tenant)->with('success', "Tenant updated and user '{$userEmail}' has been set as Administrator.");
        }

        return redirect()->route('admin.show', $tenant)->with('success', "Tenant '{$request->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Tenant $tenant)
    {
        $tenantName = $tenant->name;

        // Delete domains first
        $tenant->domains()->delete();

        // Try to delete the tenant (which may trigger database deletion via package events)
        try {
            $tenant->delete();
        } catch (\Exception $e) {
            // If the database didn't exist, the package event listener throws an error.
            // Force delete the tenant record directly to clean up.
            try {
                \App\Models\Tenant::withoutEvents(function () use ($tenant) {
                    $tenant->delete();
                });
            } catch (\Exception $e2) {
                // If even this fails, manually remove from DB
                \Illuminate\Support\Facades\DB::connection('central')
                    ->table('tenants')
                    ->where('id', $tenant->id)
                    ->delete();
            }
        }

        return redirect()->route('admin.index')->with('success', "Tenant '{$tenantName}' berhasil dihapus.");
    }
}
