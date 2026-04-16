<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Database\Seeders\CoaBumdesaSeeder;
use Database\Seeders\CoaUmkmSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    /**
     * Initialize/Reset Chart of Accounts.
     * POST /setup/init-coa
     */
    public function initCoa(Request $request): JsonResponse
    {
        $request->validate([
            'standard' => ['required', 'in:SAK_EP,KEPMENDESA'],
        ]);

        $user = $request->user();
        $company = $this->getOrCreateCompany($user);

        // Check permission
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat mereset COA.',
            ], 403);
        }

        DB::transaction(function () use ($request, $company) {
            // Delete existing COA
            ChartOfAccount::where('company_id', $company->id)->delete();

            // Update company entity type based on standard
            $entityType = $request->standard === 'SAK_EP' ? 'UMKM' : 'BUMDesa';
            $company->update(['entity_type' => $entityType]);

            // Seed new COA
            if ($request->standard === 'SAK_EP') {
                (new CoaUmkmSeeder())->run($company);
            } else {
                (new CoaBumdesaSeeder())->run($company);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Chart of Accounts berhasil di-reset dengan standar ' . $request->standard,
            'data' => [
                'standard' => $request->standard,
                'total_accounts' => ChartOfAccount::where('company_id', $company->id)->count(),
            ],
        ]);
    }

    /**
     * Get user profile with company and accounting standard.
     * GET /user/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'company' => $company ? [
                    'id' => $company->id,
                    'name' => $company->name,
                    'entity_type' => $company->entity_type,
                    'accounting_standard' => $company->entity_type === 'UMKM' ? 'SAK_EP' : 'KEPMENDESA',
                    'fiscal_start' => $company->fiscal_start?->format('Y-m-d'),
                ] : null,
            ],
        ]);
    }

    /**
     * Update company info during setup.
     * POST /api/company/update
     */
    public function updateCompany(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $this->getOrCreateCompany($user);

        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'fiscal_start' => ['nullable', 'date'],
            'business_sector' => ['nullable', 'in:general,livestock,plantation,aquaculture,forestry,mixed_agriculture'],
            'enable_psak69' => ['nullable', 'boolean'],
        ]);

        $company->update($request->only([
            'name',
            'phone', 
            'email', 
            'npwp', 
            'address', 
            'fiscal_start',
            'business_sector',
            'enable_psak69',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company->fresh(),
        ]);
    }

    /**
     * Helper to get current company or create one if missing.
     */
    protected function getOrCreateCompany($user)
    {
        // 1. Check direct association
        if ($user->company) {
            return $user->company;
        }

        // 2. Try to find any company in this tenant database
        $company = \App\Models\Company::first();

        if (!$company) {
            // 3. Create fresh company from tenant data
            $company = \App\Models\Company::create([
                'user_id' => $user->id,
                'name' => tenant('name') ?? 'Nama Perusahaan',
                'email' => tenant('email') ?? $user->email,
                'entity_type' => 'UMKM',
                'fiscal_start' => date('Y-01-01'),
            ]);
        }

        // 4. Link user to company if not linked
        if ($user->company_id !== $company->id) {
            $user->update(['company_id' => $company->id]);
        }

        return $company;
    }
}

