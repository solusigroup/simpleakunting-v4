<?php

namespace App\Http\Controllers;

use App\Models\BiologicalAsset;
use App\Models\BiologicalTransformation;
use App\Models\BiologicalValuation;
use App\Models\AgriculturalProduce;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiologicalAssetController extends Controller
{
    /**
     * GET /biological-assets
     * List all biological assets.
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

        // Check if PSAK 69 is enabled
        if (!$company->usesPsak69()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'PSAK 69 module not enabled'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Modul PSAK 69 tidak aktif untuk perusahaan Anda.');
        }

        $assets = BiologicalAsset::where('company_id', $company->id)
            ->with(['account:id,code,name', 'fairValueAccount:id,code,name'])
            ->orderBy('code')
            ->get();

        // Get biological asset accounts (fallback to all Asset accounts)
        $assetAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->biologicalAssets()
            ->get();
        
        if ($assetAccounts->isEmpty()) {
            // Fallback: show all non-parent Asset accounts
            $assetAccounts = ChartOfAccount::where('company_id', $company->id)
                ->where('is_parent', false)
                ->where('type', 'Asset')
                ->orderBy('code')
                ->get();
        }

        // Get fair value gain/loss accounts (fallback to Revenue/Expense accounts)
        $fairValueAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->fairValueGainLoss()
            ->get();
            
        if ($fairValueAccounts->isEmpty()) {
            // Fallback: show Revenue and Expense accounts
            $fairValueAccounts = ChartOfAccount::where('company_id', $company->id)
                ->where('is_parent', false)
                ->whereIn('type', ['Revenue', 'Expense'])
                ->orderBy('code')
                ->get();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $assets]);
        }

        return view('biological-assets.index', compact('assets', 'assetAccounts', 'fairValueAccounts', 'company'));
    }

    /**
     * POST /biological-assets
     * Create new biological asset.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan.',
                ], 400);
            }

            if (!$company->usesPsak69()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modul PSAK 69 tidak aktif.',
                ], 403);
            }

            if (!$user->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin.',
                ], 403);
            }

            $validated = $request->validate([
                'code' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'category' => ['required', 'in:livestock,plantation,aquaculture,forestry,other'],
                'asset_type' => ['required', 'in:consumable,bearer'],
                'maturity_status' => ['required', 'in:immature,mature'],
                'quantity' => ['required', 'numeric', 'min:0'],
                'unit' => ['required', 'string', 'max:50'],
                'acquisition_date' => ['required', 'date'],
                'acquisition_cost' => ['required', 'numeric', 'min:0'],
                'current_fair_value' => ['nullable', 'numeric', 'min:0'],
                'cost_to_sell' => ['nullable', 'numeric', 'min:0'],
                'valuation_method' => ['required', 'in:fair_value,cost_model'],
                'location' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'coa_id' => ['required', 'exists:chart_of_accounts,id'],
                'fair_value_gain_loss_coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            ]);

            // Check for duplicate code
            $exists = BiologicalAsset::where('company_id', $company->id)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode aset biologis sudah digunakan.',
                ], 422);
            }

            // Calculate carrying amount
            $costToSell = $request->cost_to_sell ?? 0;
            if ($request->valuation_method === 'fair_value' && $request->current_fair_value) {
                $carryingAmount = $request->current_fair_value - $costToSell;
            } else {
                $carryingAmount = $request->acquisition_cost;
            }

            $asset = BiologicalAsset::create([
                'company_id' => $company->id,
                'code' => $request->code,
                'name' => $request->name,
                'category' => $request->category,
                'asset_type' => $request->asset_type,
                'maturity_status' => $request->maturity_status,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'acquisition_date' => $request->acquisition_date,
                'acquisition_cost' => $request->acquisition_cost,
                'current_fair_value' => $request->current_fair_value,
                'cost_to_sell' => $costToSell,
                'carrying_amount' => $carryingAmount,
                'valuation_method' => $request->valuation_method,
                'valuation_date' => $request->valuation_method === 'fair_value' ? now() : null,
                'location' => $request->location,
                'notes' => $request->notes,
                'coa_id' => $request->coa_id,
                'fair_value_gain_loss_coa_id' => $request->fair_value_gain_loss_coa_id,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aset biologis berhasil ditambahkan.',
                'data' => $asset->load(['account', 'fairValueAccount']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /biological-assets/{id}
     * Get biological asset detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        $asset = BiologicalAsset::where('company_id', $company->id)
            ->with([
                'account:id,code,name',
                'fairValueAccount:id,code,name',
                'transformations' => function($query) {
                    $query->orderBy('transaction_date', 'desc')->limit(10);
                },
                'valuations' => function($query) {
                    $query->orderBy('valuation_date', 'desc')->limit(10);
                },
                'produce' => function($query) {
                    $query->orderBy('harvest_date', 'desc')->limit(10);
                }
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => array_merge($asset->toArray(), [
                'unit_value' => $asset->getUnitValue(),
                'total_harvested' => $asset->getTotalHarvested(),
                'category_label' => $asset->getCategoryLabel(),
                'asset_type_label' => $asset->getAssetTypeLabel(),
                'maturity_status_label' => $asset->getMaturityStatusLabel(),
            ]),
        ]);
    }

    /**
     * PUT /biological-assets/{id}
     * Update biological asset.
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

        $asset = BiologicalAsset::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'maturity_status' => ['sometimes', 'in:immature,mature'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $asset->update($request->only(['name', 'maturity_status', 'location', 'notes', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Aset biologis berhasil diperbarui.',
            'data' => $asset->fresh(),
        ]);
    }

    /**
     * DELETE /biological-assets/{id}
     * Soft delete biological asset.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        $asset = BiologicalAsset::where('company_id', $company->id)->findOrFail($id);
        
        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aset biologis berhasil dihapus.',
        ]);
    }

    /**
     * POST /biological-assets/{id}/valuate
     * Record fair value adjustment.
     */
    public function valuate(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$user->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin.',
                ], 403);
            }

            $asset = BiologicalAsset::where('company_id', $company->id)->findOrFail($id);

            if ($asset->valuation_method !== 'fair_value') {
                return response()->json([
                    'success' => false,
                    'message' => 'Aset ini menggunakan cost model, tidak dapat direvaluasi.',
                ], 422);
            }

            $validated = $request->validate([
                'valuation_date' => ['required', 'date'],
                'current_fair_value' => ['required', 'numeric', 'min:0'],
                'cost_to_sell' => ['required', 'numeric', 'min:0'],
                'valuation_method' => ['nullable', 'string', 'max:100'],
                'valuation_notes' => ['nullable', 'string'],
                'create_journal' => ['sometimes', 'boolean'],
            ]);

            DB::beginTransaction();

            $previousFairValue = $asset->current_fair_value ?? $asset->acquisition_cost;
            $fairValueChange = $validated['current_fair_value'] - $previousFairValue;
            $newCarryingAmount = $validated['current_fair_value'] - $validated['cost_to_sell'];

            // Create valuation record
            $valuation = BiologicalValuation::create([
                'biological_asset_id' => $asset->id,
                'valuation_date' => $validated['valuation_date'],
                'previous_fair_value' => $previousFairValue,
                'current_fair_value' => $validated['current_fair_value'],
                'cost_to_sell' => $validated['cost_to_sell'],
                'fair_value_change' => $fairValueChange,
                'valuation_method' => $validated['valuation_method'] ?? 'Market price',
                'valuation_notes' => $validated['valuation_notes'],
                'created_by' => $user->id,
            ]);

            // Update asset
            $asset->update([
                'current_fair_value' => $validated['current_fair_value'],
                'cost_to_sell' => $validated['cost_to_sell'],
                'carrying_amount' => $newCarryingAmount,
                'valuation_date' => $validated['valuation_date'],
            ]);

            // Create journal entry if requested
            if ($request->create_journal && $fairValueChange != 0 && $asset->fair_value_gain_loss_coa_id) {
                $journal = $this->createFairValueJournal($asset, $valuation, $fairValueChange, $validated['valuation_date']);
                $valuation->update(['journal_id' => $journal->id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penilaian nilai wajar berhasil dicatat.',
                'data' => [
                    'valuation' => $valuation,
                    'asset' => $asset->fresh(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /biological-assets/{id}/transform
     * Record biological transformation.
     */
    public function transform(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$user->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin.',
                ], 403);
            }

            $asset = BiologicalAsset::where('company_id', $company->id)->findOrFail($id);

            $validated = $request->validate([
                'transformation_type' => ['required', 'in:growth,degeneration,production,procreation,death,harvest'],
                'transaction_date' => ['required', 'date'],
                'quantity_change' => ['required', 'numeric'],
                'description' => ['nullable', 'string'],
            ]);

            // Validate quantity
            $newQuantity = $asset->quantity + $validated['quantity_change'];
            if ($newQuantity < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuantitas tidak boleh negatif.',
                ], 422);
            }

            DB::beginTransaction();

            // Create transformation record
            $transformation = BiologicalTransformation::create([
                'biological_asset_id' => $asset->id,
                'transformation_type' => $validated['transformation_type'],
                'transaction_date' => $validated['transaction_date'],
                'quantity_change' => $validated['quantity_change'],
                'description' => $validated['description'],
            ]);

            // Update asset quantity
            $asset->update(['quantity' => $newQuantity]);

            // If death, create journal entry for loss
            if ($validated['transformation_type'] === 'death' && $validated['quantity_change'] < 0) {
                $lossAmount = abs($validated['quantity_change']) * $asset->getUnitValue();
                // TODO: Create journal for death loss
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transformasi biologis berhasil dicatat.',
                'data' => [
                    'transformation' => $transformation,
                    'asset' => $asset->fresh(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /biological-assets/{id}/harvest
     * Record harvest of agricultural produce.
     */
    public function harvest(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$user->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin.',
                ], 403);
            }

            $asset = BiologicalAsset::where('company_id', $company->id)->findOrFail($id);

            // Check if bearer asset is mature
            if ($asset->isBearer() && !$asset->isMature()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aset penghasil harus sudah dewasa untuk dipanen.',
                ], 422);
            }

            $validated = $request->validate([
                'harvest_date' => ['required', 'date'],
                'product_name' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'numeric', 'min:0'],
                'unit' => ['required', 'string', 'max:50'],
                'fair_value_at_harvest' => ['required', 'numeric', 'min:0'],
                'cost_to_sell' => ['required', 'numeric', 'min:0'],
                'coa_id' => ['required', 'exists:chart_of_accounts,id'],
                'notes' => ['nullable', 'string'],
                'create_journal' => ['sometimes', 'boolean'],
            ]);

            DB::beginTransaction();

            $carryingAmount = $validated['fair_value_at_harvest'] - $validated['cost_to_sell'];

            // Create agricultural produce record
            $produce = AgriculturalProduce::create([
                'company_id' => $company->id,
                'biological_asset_id' => $asset->id,
                'harvest_date' => $validated['harvest_date'],
                'product_name' => $validated['product_name'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'fair_value_at_harvest' => $validated['fair_value_at_harvest'],
                'cost_to_sell' => $validated['cost_to_sell'],
                'carrying_amount' => $carryingAmount,
                'coa_id' => $validated['coa_id'],
                'notes' => $validated['notes'],
            ]);

            // Create transformation record for harvest
            BiologicalTransformation::create([
                'biological_asset_id' => $asset->id,
                'transformation_type' => 'harvest',
                'transaction_date' => $validated['harvest_date'],
                'quantity_change' => -$validated['quantity'], // Negative for harvest
                'description' => "Panen: {$validated['product_name']}",
            ]);

            // For consumable assets, reduce quantity
            if ($asset->isConsumable()) {
                $asset->update(['quantity' => $asset->quantity - $validated['quantity']]);
            }

            // Create journal entry if requested
            if ($request->create_journal) {
                $journal = $this->createHarvestJournal($asset, $produce, $validated['harvest_date']);
                $produce->update(['journal_id' => $journal->id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Panen berhasil dicatat.',
                'data' => [
                    'produce' => $produce,
                    'asset' => $asset->fresh(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create journal entry for fair value adjustment.
     */
    private function createFairValueJournal(BiologicalAsset $asset, BiologicalValuation $valuation, float $fairValueChange, string $date): Journal
    {
        $journal = Journal::create([
            'company_id' => $asset->company_id,
            'date' => $date,
            'type' => 'General',
            'description' => "Penyesuaian Nilai Wajar - {$asset->name}",
            'is_posted' => true,
        ]);

        if ($fairValueChange > 0) {
            // Gain: Dr. Biological Asset, Cr. Fair Value Gain
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $asset->coa_id,
                'debit' => abs($fairValueChange),
                'credit' => 0,
            ]);

            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $asset->fair_value_gain_loss_coa_id,
                'debit' => 0,
                'credit' => abs($fairValueChange),
            ]);
        } else {
            // Loss: Dr. Fair Value Loss, Cr. Biological Asset
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $asset->fair_value_gain_loss_coa_id,
                'debit' => abs($fairValueChange),
                'credit' => 0,
            ]);

            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $asset->coa_id,
                'debit' => 0,
                'credit' => abs($fairValueChange),
            ]);
        }

        return $journal;
    }

    /**
     * Create journal entry for harvest.
     */
    private function createHarvestJournal(BiologicalAsset $asset, AgriculturalProduce $produce, string $date): Journal
    {
        $journal = Journal::create([
            'company_id' => $asset->company_id,
            'date' => $date,
            'type' => 'General',
            'description' => "Panen - {$produce->product_name}",
            'is_posted' => true,
        ]);

        // Dr. Agricultural Produce (Inventory)
        JournalItem::create([
            'journal_id' => $journal->id,
            'coa_id' => $produce->coa_id,
            'debit' => $produce->carrying_amount,
            'credit' => 0,
        ]);

        // Cr. Biological Asset
        JournalItem::create([
            'journal_id' => $journal->id,
            'coa_id' => $asset->coa_id,
            'debit' => 0,
            'credit' => $produce->carrying_amount,
        ]);

        return $journal;
    }
}
