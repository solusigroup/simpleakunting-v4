<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->get();
        $centralDomain = env('CENTRAL_DOMAIN', 'simpleakunting4-0.test');

        return view('admin.index', compact('tenants', 'centralDomain'));
    }

    public function create()
    {
        return view('admin.create');
    }

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

        return redirect('/admin')->with('success', "Tenant '{$request->name}' berhasil dibuat!");
    }

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

        return redirect('/admin')->with('success', "Tenant '{$tenantName}' berhasil dihapus.");
    }
}
