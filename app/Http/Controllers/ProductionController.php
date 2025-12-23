<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Inventory;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\Production;
use App\Models\ProductionComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    /**
     * Display a listing of productions.
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;
        
        $query = Production::where('company_id', $company->id)
            ->with(['assembly', 'creator']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $productions = $query->orderBy('production_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'productions' => $productions,
            ]);
        }

        return view('productions.index', compact('productions'));
    }

    /**
     * Show the form for creating a new production.
     */
    public function create(Request $request)
    {
        $company = $request->user()->company;
        
        // Get all assembly items
        $assemblies = Inventory::where('company_id', $company->id)
            ->where('is_assembly', true)
            ->where('is_active', true)
            ->with('components.component')
            ->orderBy('name')
            ->get();

        // Get expense accounts for labor and overhead
        $expenseAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('account_category', 'expense_operational')
                  ->orWhere('account_category', 'expense_administrative')
                  ->orWhere('account_category', 'cogs')
                  ->orWhere('name', 'like', '%Labor%')
                  ->orWhere('name', 'like', '%Overhead%')
                  ->orWhere('name', 'like', '%Tenaga Kerja%')
                  ->orWhere('name', 'like', '%Biaya Produksi%');
            })
            ->orderBy('code')
            ->get();

        // Validate manufacturing COAs
        $missingCOAs = $this->validateManufacturingCOAs($company);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'assemblies' => $assemblies,
                'expense_accounts' => $expenseAccounts,
                'missing_coas' => $missingCOAs,
            ]);
        }

        return view('productions.create', compact('assemblies', 'expenseAccounts', 'missingCOAs'));
    }

    /**
     * Store a newly created production in storage.
     */
    public function store(Request $request)
    {
        $company = $request->user()->company;
        
        $validated = $request->validate([
            'production_date' => 'required|date',
            'assembly_id' => 'required|exists:inventories,id',
            'quantity' => 'required|numeric|min:0.01',
            'labor_cost' => 'nullable|numeric|min:0',
            'labor_coa_id' => 'nullable|exists:chart_of_accounts,id',
            'overhead_cost' => 'nullable|numeric|min:0',
            'overhead_coa_id' => 'nullable|exists:chart_of_accounts,id',
            'notes' => 'nullable|string',
            'components' => 'required|array',
            'components.*.component_id' => 'required|exists:inventories,id',
            'components.*.quantity_used' => 'required|numeric|min:0',
        ]);

        // Validate assembly
        $assembly = Inventory::where('company_id', $company->id)
            ->where('id', $validated['assembly_id'])
            ->where('is_assembly', true)
            ->with('components.component')
            ->firstOrFail();

        if (!$assembly->canBeAssembled()) {
            return response()->json([
                'success' => false,
                'message' => 'Assembly belum memiliki BOM yang terdefinisi.',
            ], 422);
        }

        // Validate COAs
        if ($validated['labor_cost'] > 0 && empty($validated['labor_coa_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'COA untuk biaya tenaga kerja harus dipilih.',
            ], 422);
        }

        if ($validated['overhead_cost'] > 0 && empty($validated['overhead_coa_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'COA untuk biaya overhead harus dipilih.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate production number
            $lastProduction = Production::where('company_id', $company->id)
                ->whereYear('production_date', date('Y', strtotime($validated['production_date'])))
                ->orderBy('id', 'desc')
                ->first();

            $year = date('Y', strtotime($validated['production_date']));
            $month = date('m', strtotime($validated['production_date']));
            $sequence = $lastProduction ? (intval(substr($lastProduction->production_number, -4)) + 1) : 1;
            $productionNumber = sprintf('PROD/%s/%s/%04d', $year, $month, $sequence);

            // Create production
            $production = Production::create([
                'company_id' => $company->id,
                'production_number' => $productionNumber,
                'production_date' => $validated['production_date'],
                'assembly_id' => $validated['assembly_id'],
                'quantity' => $validated['quantity'],
                'unit' => $assembly->unit,
                'labor_cost' => $validated['labor_cost'] ?? 0,
                'labor_coa_id' => $validated['labor_coa_id'] ?? null,
                'overhead_cost' => $validated['overhead_cost'] ?? 0,
                'overhead_coa_id' => $validated['overhead_coa_id'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            // Create production components
            $totalMaterialCost = 0;
            foreach ($validated['components'] as $componentData) {
                $component = Inventory::findOrFail($componentData['component_id']);
                
                $productionComponent = ProductionComponent::create([
                    'production_id' => $production->id,
                    'component_id' => $componentData['component_id'],
                    'quantity_required' => $assembly->components->where('component_id', $componentData['component_id'])->first()->quantity * $validated['quantity'],
                    'quantity_used' => $componentData['quantity_used'],
                    'unit' => $component->unit,
                    'unit_cost' => $component->cost,
                    'total_cost' => $componentData['quantity_used'] * $component->cost,
                ]);

                $totalMaterialCost += $productionComponent->total_cost;
            }

            // Update production costs
            $production->total_material_cost = $totalMaterialCost;
            $production->calculateTotalCost();
            $production->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Production order berhasil dibuat.',
                'production' => $production->load(['assembly', 'components.component']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat production order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified production.
     */
    public function show(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $production = Production::where('company_id', $company->id)
            ->where('id', $id)
            ->with(['assembly.account', 'components.component.account', 'journal', 'creator'])
            ->firstOrFail();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'production' => $production,
            ]);
        }

        return view('productions.show', compact('production'));
    }

    /**
     * Start production (draft -> in_progress).
     */
    public function start(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $production = Production::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($production->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya production dengan status draft yang dapat dimulai.',
            ], 422);
        }

        $production->status = 'in_progress';
        $production->save();

        return response()->json([
            'success' => true,
            'message' => 'Production berhasil dimulai.',
            'production' => $production,
        ]);
    }

    /**
     * Complete production and update stock.
     */
    public function complete(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $production = Production::where('company_id', $company->id)
            ->where('id', $id)
            ->with(['assembly.account', 'components.component.account'])
            ->firstOrFail();

        if ($production->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Production sudah selesai.',
            ], 422);
        }

        if ($production->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Production yang dibatalkan tidak dapat diselesaikan.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Validate component stock
            foreach ($production->components as $component) {
                if ($component->component->stock < $component->quantity_used) {
                    throw new \Exception("Stok {$component->component->name} tidak mencukupi.");
                }
            }

            // Validate assembly COA
            if (!$production->assembly->coa_id) {
                throw new \Exception("Assembly {$production->assembly->name} belum memiliki COA.");
            }

            // Reduce component stock
            foreach ($production->components as $component) {
                $component->component->stock -= $component->quantity_used;
                $component->component->save();
            }

            // Increase finished goods stock
            $production->assembly->stock += $production->quantity;
            
            // Update assembly cost based on production unit cost
            $production->assembly->cost = $production->unit_cost;
            $production->assembly->save();

            // Create journal entry
            $journal = $this->createProductionJournal($production);
            $production->journal_id = $journal->id;

            // Update status
            $production->status = 'completed';
            $production->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Production berhasil diselesaikan.',
                'production' => $production->fresh(['assembly', 'components.component', 'journal']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan production: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel production.
     */
    public function cancel(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $production = Production::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($production->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Production yang sudah selesai tidak dapat dibatalkan.',
            ], 422);
        }

        $production->status = 'cancelled';
        $production->save();

        return response()->json([
            'success' => true,
            'message' => 'Production berhasil dibatalkan.',
            'production' => $production,
        ]);
    }

    /**
     * Create journal entry for completed production.
     */
    private function createProductionJournal(Production $production): Journal
    {
        // Create journal header
        $journal = Journal::create([
            'company_id' => $production->company_id,
            'transaction_date' => $production->production_date,
            'type' => 'manual',
            'description' => "Produksi {$production->assembly->name} - {$production->production_number}",
            'created_by' => auth()->id(),
        ]);

        $details = [];

        // Debit: Finished Goods Inventory
        $details[] = [
            'journal_id' => $journal->id,
            'coa_id' => $production->assembly->coa_id,
            'description' => "Produksi {$production->quantity} {$production->unit} {$production->assembly->name}",
            'debit' => $production->total_cost,
            'credit' => 0,
        ];

        // Credit: Raw Materials (for each component)
        foreach ($production->components as $component) {
            if (!$component->component->coa_id) {
                throw new \Exception("Komponen {$component->component->name} belum memiliki COA.");
            }

            $details[] = [
                'journal_id' => $journal->id,
                'coa_id' => $component->component->coa_id,
                'description' => "Penggunaan {$component->quantity_used} {$component->unit} {$component->component->name}",
                'debit' => 0,
                'credit' => $component->total_cost,
            ];
        }

        // Credit: Labor Cost (if any)
        if ($production->labor_cost > 0) {
            $laborCOAId = $production->labor_coa_id ?? $this->getLaborCOA($production->company_id)?->id;
            if (!$laborCOAId) {
                throw new \Exception("COA untuk biaya tenaga kerja tidak ditemukan.");
            }

            $details[] = [
                'journal_id' => $journal->id,
                'coa_id' => $laborCOAId,
                'description' => "Biaya tenaga kerja produksi",
                'debit' => 0,
                'credit' => $production->labor_cost,
            ];
        }

        // Credit: Overhead Cost (if any)
        if ($production->overhead_cost > 0) {
            $overheadCOAId = $production->overhead_coa_id ?? $this->getOverheadCOA($production->company_id)?->id;
            if (!$overheadCOAId) {
                throw new \Exception("COA untuk biaya overhead tidak ditemukan.");
            }

            $details[] = [
                'journal_id' => $journal->id,
                'coa_id' => $overheadCOAId,
                'description' => "Biaya overhead produksi",
                'debit' => 0,
                'credit' => $production->overhead_cost,
            ];
        }

        // Insert all details
        JournalDetail::insert($details);

        return $journal;
    }

    /**
     * Validate manufacturing COAs.
     */
    private function validateManufacturingCOAs($company): array
    {
        $missing = [];
        
        // Check for finished goods account
        $finishedGoodsAccount = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('account_category', 'inventory')
                  ->orWhere('name', 'like', '%Finished Goods%')
                  ->orWhere('name', 'like', '%Barang Jadi%');
            })
            ->first();
        
        if (!$finishedGoodsAccount) {
            $missing[] = 'Finished Goods Inventory Account (Persediaan Barang Jadi)';
        }
        
        // Check for raw materials account
        $rawMaterialsAccount = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('account_category', 'inventory')
                  ->orWhere('name', 'like', '%Raw Material%')
                  ->orWhere('name', 'like', '%Bahan Baku%');
            })
            ->first();
        
        if (!$rawMaterialsAccount) {
            $missing[] = 'Raw Materials Inventory Account (Persediaan Bahan Baku)';
        }
        
        return $missing;
    }

    /**
     * Get labor cost COA.
     */
    private function getLaborCOA($companyId)
    {
        return ChartOfAccount::where('company_id', $companyId)
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('name', 'like', '%Labor%')
                  ->orWhere('name', 'like', '%Tenaga Kerja%')
                  ->orWhere('name', 'like', '%Upah%');
            })
            ->first();
    }

    /**
     * Get overhead cost COA.
     */
    private function getOverheadCOA($companyId)
    {
        return ChartOfAccount::where('company_id', $companyId)
            ->where('is_parent', false)
            ->where(function($q) {
                $q->where('name', 'like', '%Overhead%')
                  ->orWhere('name', 'like', '%Biaya Produksi%')
                  ->orWhere('name', 'like', '%Manufacturing%');
            })
            ->first();
    }
}
