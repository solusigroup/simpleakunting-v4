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
            ->with(['account:id,code,name', 'accumulatedAccount:id,code,name', 'expenseAccount:id,code,name'])
            ->orderBy('code')
            ->get();

        // Get asset accounts using category-based scope
        $assetAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Asset')
            ->where('is_parent', false)
            ->fixedAssetAccounts()
            ->get();

        // Get accumulated depreciation accounts using category-based scope
        $accumAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Asset')
            ->where('is_parent', false)
            ->accumulatedDepreciation()
            ->get();

        // Get depreciation expense accounts
        $expenseAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Expense')
            ->where('is_parent', false)
            ->where(function ($q) {
                $q->where('name', 'like', '%Penyusutan%')
                  ->orWhere('name', 'like', '%Depreciation%')
                  ->orWhere('account_category', 'expense_other');
            })
            ->orderBy('code')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $assets]);
        }

        return view('assets.index', compact('assets', 'assetAccounts', 'accumAccounts', 'expenseAccounts', 'company'));
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
                'expense_coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
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
                'expense_coa_id' => $request->expense_coa_id,
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
            'expense_coa_id' => ['sometimes', 'nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $asset->update($request->only(['name', 'salvage_value', 'accumulated_depreciation', 'expense_coa_id', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Aset tetap berhasil diperbarui.',
            'data' => $asset->fresh(),
        ]);
    }

    /**
     * POST /assets/depreciate
     * Run monthly depreciation for all active assets.
     * Requires Manajer role or higher.
     */
    public function runDepreciation(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        // Only Manajer or Administrator can run depreciation
        if (!$user->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Manajer atau Administrator yang dapat menjalankan depresiasi.',
            ], 403);
        }

        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $request->year;
        $month = $request->month;
        $depreciationDate = sprintf('%04d-%02d-%02d', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));

        // Get all active assets with required COA accounts
        $assets = FixedAsset::where('company_id', $company->id)
            ->where('is_active', true)
            ->whereNotNull('accum_coa_id')
            ->whereNotNull('expense_coa_id')
            ->get();

        if ($assets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada aset aktif dengan akun penyusutan yang lengkap.',
            ], 422);
        }

        try {
            \DB::beginTransaction();

            $journalItems = [];
            $totalDepreciation = 0;

            foreach ($assets as $asset) {
                $depreciation = $asset->getMonthlyDepreciation();
                
                // Skip if no remaining value to depreciate
                if ($depreciation <= 0 || $asset->getBookValue() <= $asset->salvage_value) {
                    continue;
                }

                // Don't depreciate beyond salvage value
                $maxDepreciation = $asset->getBookValue() - $asset->salvage_value;
                $depreciation = min($depreciation, $maxDepreciation);

                if ($depreciation > 0) {
                    // Add journal items
                    $journalItems[] = [
                        'coa_id' => $asset->expense_coa_id,
                        'debit' => $depreciation,
                        'credit' => 0,
                        'description' => "Penyusutan {$asset->name}",
                    ];
                    $journalItems[] = [
                        'coa_id' => $asset->accum_coa_id,
                        'debit' => 0,
                        'credit' => $depreciation,
                        'description' => "Akumulasi Penyusutan {$asset->name}",
                    ];

                    // Update accumulated depreciation on asset
                    $asset->accumulated_depreciation += $depreciation;
                    $asset->save();

                    $totalDepreciation += $depreciation;
                }
            }

            if (empty($journalItems)) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penyusutan yang perlu dicatat untuk periode ini.',
                ], 422);
            }

            // Create journal entry
            $journal = \App\Models\Journal::create([
                'company_id' => $company->id,
                'date' => $depreciationDate,
                'reference' => 'DEP-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT),
                'description' => "Penyusutan Aset Tetap - " . date('F Y', strtotime($depreciationDate)),
                'source' => 'depreciation',
                'is_posted' => true,
            ]);

            foreach ($journalItems as $item) {
                \App\Models\JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['coa_id'],
                    'debit' => $item['debit'],
                    'credit' => $item['credit'],
                    'description' => $item['description'],
                ]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jurnal penyusutan berhasil dibuat.',
                'data' => [
                    'journal_id' => $journal->id,
                    'total_depreciation' => $totalDepreciation,
                    'assets_processed' => count($journalItems) / 2,
                ],
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

