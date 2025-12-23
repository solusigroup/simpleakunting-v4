<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Inventory;
use App\Models\Production;
use App\Models\ProductionComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturingReportController extends Controller
{
    /**
     * Production Cost Report
     * Shows total production costs breakdown by period
     */
    public function productionCost(Request $request)
    {
        $company = $request->user()->company;
        
        if (!$company) {
            return redirect()->route('dashboard');
        }

        // Date filters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get completed productions within date range
        $productions = Production::where('company_id', $company->id)
            ->where('status', 'completed')
            ->whereBetween('production_date', [$startDate, $endDate])
            ->with(['assembly', 'components.component'])
            ->orderBy('production_date', 'desc')
            ->get();

        // Calculate totals
        $totalMaterialCost = $productions->sum('total_material_cost');
        $totalLaborCost = $productions->sum('labor_cost');
        $totalOverheadCost = $productions->sum('overhead_cost');
        $totalCost = $productions->sum('total_cost');
        $totalQuantity = $productions->sum('quantity');
        $averageUnitCost = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;

        // Group by product
        $costByProduct = $productions->groupBy('assembly_id')->map(function($group) {
            $first = $group->first();
            return [
                'product_name' => $first->assembly->name,
                'product_code' => $first->assembly->code,
                'quantity' => $group->sum('quantity'),
                'unit' => $first->unit,
                'material_cost' => $group->sum('total_material_cost'),
                'labor_cost' => $group->sum('labor_cost'),
                'overhead_cost' => $group->sum('overhead_cost'),
                'total_cost' => $group->sum('total_cost'),
                'production_count' => $group->count(),
            ];
        })->values();

        // Monthly trend (last 6 months)
        $monthlyTrend = Production::where('company_id', $company->id)
            ->where('status', 'completed')
            ->where('production_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('DATE_FORMAT(production_date, "%Y-%m") as month, 
                         SUM(total_material_cost) as material_cost,
                         SUM(labor_cost) as labor_cost,
                         SUM(overhead_cost) as overhead_cost,
                         SUM(total_cost) as total_cost,
                         SUM(quantity) as quantity')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => compact('productions', 'totalMaterialCost', 'totalLaborCost', 
                                 'totalOverheadCost', 'totalCost', 'averageUnitCost', 
                                 'costByProduct', 'monthlyTrend'),
            ]);
        }

        return view('reports.manufacturing.production-cost', compact(
            'productions', 'startDate', 'endDate',
            'totalMaterialCost', 'totalLaborCost', 'totalOverheadCost', 
            'totalCost', 'totalQuantity', 'averageUnitCost',
            'costByProduct', 'monthlyTrend'
        ));
    }

    /**
     * Material Usage Report
     * Shows raw material consumption in production
     */
    public function materialUsage(Request $request)
    {
        $company = $request->user()->company;
        
        if (!$company) {
            return redirect()->route('dashboard');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get material usage from completed productions
        $materialUsage = ProductionComponent::whereHas('production', function($q) use ($company, $startDate, $endDate) {
            $q->where('company_id', $company->id)
              ->where('status', 'completed')
              ->whereBetween('production_date', [$startDate, $endDate]);
        })
        ->with(['component', 'production.assembly'])
        ->get()
        ->groupBy('component_id')
        ->map(function($group) {
            $first = $group->first();
            $totalRequired = $group->sum('quantity_required');
            $totalUsed = $group->sum('quantity_used');
            $variance = $totalUsed - $totalRequired;
            $variancePercent = $totalRequired > 0 ? ($variance / $totalRequired) * 100 : 0;
            
            return [
                'component_id' => $first->component_id,
                'component_code' => $first->component->code,
                'component_name' => $first->component->name,
                'unit' => $first->unit,
                'current_stock' => $first->component->stock,
                'quantity_required' => $totalRequired,
                'quantity_used' => $totalUsed,
                'variance' => $variance,
                'variance_percent' => round($variancePercent, 2),
                'total_cost' => $group->sum('total_cost'),
                'usage_count' => $group->count(),
                'products' => $group->pluck('production.assembly.name')->unique()->values(),
            ];
        })->sortByDesc('total_cost')->values();

        // Summary stats
        $totalMaterialCost = $materialUsage->sum('total_cost');
        $totalVariance = $materialUsage->sum('variance');
        $materialsWithWaste = $materialUsage->where('variance', '>', 0)->count();
        $materialsWithSavings = $materialUsage->where('variance', '<', 0)->count();

        // Top 5 most used materials
        $topMaterials = $materialUsage->take(5);

        // Materials with high variance (potential issues)
        $highVarianceMaterials = $materialUsage->filter(function($m) {
            return abs($m['variance_percent']) > 5; // More than 5% variance
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => compact('materialUsage', 'totalMaterialCost', 'totalVariance',
                                 'materialsWithWaste', 'materialsWithSavings', 
                                 'topMaterials', 'highVarianceMaterials'),
            ]);
        }

        return view('reports.manufacturing.material-usage', compact(
            'materialUsage', 'startDate', 'endDate',
            'totalMaterialCost', 'totalVariance',
            'materialsWithWaste', 'materialsWithSavings',
            'topMaterials', 'highVarianceMaterials'
        ));
    }

    /**
     * WIP (Work in Process) Valuation Report
     * Shows value of productions currently in progress
     */
    public function wipValuation(Request $request)
    {
        $company = $request->user()->company;
        
        if (!$company) {
            return redirect()->route('dashboard');
        }

        // Get all in-progress and draft productions
        $wipProductions = Production::where('company_id', $company->id)
            ->whereIn('status', ['draft', 'in_progress'])
            ->with(['assembly.account', 'components.component', 'creator'])
            ->orderBy('production_date', 'desc')
            ->get();

        // Calculate WIP values
        $wipSummary = [
            'draft' => [
                'count' => $wipProductions->where('status', 'draft')->count(),
                'total_cost' => $wipProductions->where('status', 'draft')->sum('total_cost'),
            ],
            'in_progress' => [
                'count' => $wipProductions->where('status', 'in_progress')->count(),
                'total_cost' => $wipProductions->where('status', 'in_progress')->sum('total_cost'),
            ],
        ];
        $wipSummary['total'] = [
            'count' => $wipSummary['draft']['count'] + $wipSummary['in_progress']['count'],
            'total_cost' => $wipSummary['draft']['total_cost'] + $wipSummary['in_progress']['total_cost'],
        ];

        // Group by product
        $wipByProduct = $wipProductions->groupBy('assembly_id')->map(function($group) {
            $first = $group->first();
            return [
                'product_name' => $first->assembly->name,
                'product_code' => $first->assembly->code,
                'coa_name' => $first->assembly->account->name ?? '-',
                'quantity' => $group->sum('quantity'),
                'unit' => $first->unit,
                'total_cost' => $group->sum('total_cost'),
                'draft_count' => $group->where('status', 'draft')->count(),
                'in_progress_count' => $group->where('status', 'in_progress')->count(),
            ];
        })->values();

        // Aging analysis (days since production started)
        $aging = $wipProductions->map(function($prod) {
            $daysOld = now()->diffInDays($prod->production_date);
            return [
                'production_number' => $prod->production_number,
                'product_name' => $prod->assembly->name,
                'status' => $prod->status,
                'days_old' => $daysOld,
                'total_cost' => $prod->total_cost,
                'risk_level' => $daysOld > 30 ? 'high' : ($daysOld > 14 ? 'medium' : 'low'),
            ];
        })->sortByDesc('days_old')->values();

        // WIP by COA (for balance sheet)
        $wipByAccount = $wipProductions->groupBy(function($prod) {
            return $prod->assembly->coa_id ?? 0;
        })->map(function($group) {
            $first = $group->first();
            return [
                'coa_id' => $first->assembly->coa_id,
                'coa_code' => $first->assembly->account->code ?? '-',
                'coa_name' => $first->assembly->account->name ?? 'Tanpa COA',
                'total_cost' => $group->sum('total_cost'),
                'count' => $group->count(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => compact('wipProductions', 'wipSummary', 'wipByProduct', 'aging', 'wipByAccount'),
            ]);
        }

        return view('reports.manufacturing.wip-valuation', compact(
            'wipProductions', 'wipSummary', 'wipByProduct', 'aging', 'wipByAccount'
        ));
    }
}
