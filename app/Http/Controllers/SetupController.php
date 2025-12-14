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
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki company.',
            ], 400);
        }

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
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 400);
        }

        $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'fiscal_start' => ['nullable', 'date'],
        ]);

        $company->update($request->only(['phone', 'email', 'npwp', 'address', 'fiscal_start']));

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company->fresh(),
        ]);
    }
}

