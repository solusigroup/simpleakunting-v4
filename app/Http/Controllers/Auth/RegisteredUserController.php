<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CoaBumdesaSeeder;
use Database\Seeders\CoaUmkmSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'entity_type' => ['required', 'in:UMKM,BUMDesa'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, &$user) {
            // Create user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Administrator', // First user is always Admin
            ]);

            // Create company
            $company = Company::create([
                'user_id' => $user->id,
                'name' => $request->company_name,
                'entity_type' => $request->entity_type,
                'fiscal_start' => now()->startOfYear(),
            ]);

            // Assign company to user
            $user->update(['company_id' => $company->id]);

            // Seed Chart of Accounts based on entity type
            if ($request->entity_type === 'UMKM') {
                (new CoaUmkmSeeder())->run($company);
            } else {
                (new CoaBumdesaSeeder())->run($company);
            }
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

