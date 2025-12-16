<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    /**
     * GET /assets
     * List all fixed assets.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Company not found'], 400);
            }
            return redirect()->route('setup.wizard');
        }

        $assets = FixedAsset::where('company_id', $company->id)
            ->with(['account:id,code,name', 'accumulatedAccount:id,code,name'])
            ->orderBy('code')
            ->get();

        // Get asset accounts
        $assetAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Asset')
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('code', 'like', '1.2%')
                  ->orWhere('code', 'like', '12%');
            })
            ->get();

        // Get accumulated depreciation accounts
        $accumAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Asset')
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('name', 'like', '%Akumulasi%')
                  ->orWhere('name', 'like', '%Accumulated%');
            })
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $assets]);
        }

        return view('assets.index', compact('assets', 'assetAccounts', 'accumAccounts', 'company'));
    }

    /**
     * POST /assets
     * Create new fixed asset.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan. Silakan lengkapi profil Anda.',
                ], 400);
            }

            if (!$user->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin.',
                ], 403);
            }

            $request->validate([
                'code' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'acquisition_date' => ['required', 'date'],
                'acquisition_cost' => ['required', 'numeric', 'min:0'],
                'salvage_value' => ['required', 'numeric', 'min:0'],
                'useful_life_months' => ['required', 'integer', 'min:1'],
                'depreciation_method' => ['required', 'in:straight_line,declining_balance'],
                'coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
                'accum_coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            ]);

            $exists = FixedAsset::where('company_id', $company->id)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode aset sudah digunakan.',
                ], 422);
            }

            $asset = FixedAsset::create([
                'company_id' => $company->id,
                'coa_id' => $request->coa_id,
                'accum_coa_id' => $request->accum_coa_id,
                'code' => $request->code,
                'name' => $request->name,
                'acquisition_date' => $request->acquisition_date,
                'acquisition_cost' => $request->acquisition_cost,
                'salvage_value' => $request->salvage_value,
                'useful_life_months' => $request->useful_life_months,
                'depreciation_method' => $request->depreciation_method,
                'accumulated_depreciation' => 0,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aset tetap berhasil ditambahkan.',
                'data' => $asset,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * GET /assets/{id}
     * Get fixed asset detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        $asset = FixedAsset::where('company_id', $company->id)
            ->with(['account:id,code,name', 'accumulatedAccount:id,code,name'])
            ->findOrFail($id);

        return response()->json([
            'success' => true, 
            'data' => array_merge($asset->toArray(), [
                'book_value' => $asset->getBookValue(),
                'monthly_depreciation' => $asset->getMonthlyDepreciation(),
                'remaining_life' => $asset->getRemainingLife(),
            ]),
        ]);
    }

    /**
     * PUT /assets/{id}
     * Update fixed asset.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        $asset = FixedAsset::where('company_id', $company->id)->findOrFail($id);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'salvage_value' => ['sometimes', 'numeric', 'min:0'],
            'accumulated_depreciation' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $asset->update($request->only(['name', 'salvage_value', 'accumulated_depreciation', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Aset tetap berhasil diperbarui.',
            'data' => $asset->fresh(),
        ]);
    }
}
