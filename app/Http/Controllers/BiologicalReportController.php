<?php

namespace App\Http\Controllers;

use App\Models\BiologicalAsset;
use App\Models\BiologicalValuation;
use App\Models\AgriculturalProduce;
use App\Models\BiologicalTransformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiologicalReportController extends Controller
{
    /**
     * GET /reports/biological-reconciliation
     * PSAK 69 Reconciliation Report
     */
    public function reconciliation(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company || !$company->usesPsak69()) {
            return redirect()->route('dashboard')->with('error', 'Modul PSAK 69 tidak aktif.');
        }

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get all biological assets
        $assets = BiologicalAsset::where('company_id', $company->id)
            ->with(['account', 'valuations', 'transformations', 'produce'])
            ->get();

        $reconciliationData = [];

        foreach ($assets as $asset) {
            // Opening balance (at start date)
            $openingBalance = $this->getAssetValueAtDate($asset, $startDate);
            
            // Additions (purchases, births, etc)
            $additions = $asset->transformations()
                ->whereIn('transformation_type', ['procreation'])
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('quantity_change', '>', 0)
                ->sum('quantity_change');

            // Decreases (sales, deaths, harvest)
            $decreases = abs($asset->transformations()
                ->whereIn('transformation_type', ['death', 'harvest'])
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('quantity_change'));

            // Fair value changes
            $fairValueChanges = $asset->valuations()
                ->whereBetween('valuation_date', [$startDate, $endDate])
                ->sum('fair_value_change');

            // Closing balance
            $closingBalance = $asset->carrying_amount;

            $reconciliationData[] = [
                'asset' => $asset,
                'opening_balance' => $openingBalance,
                'additions' => $additions,
                'decreases' => $decreases,
                'fair_value_changes' => $fairValueChanges,
                'closing_balance' => $closingBalance,
                'quantity' => $asset->quantity,
            ];
        }

        // Calculate totals
        $totals = [
            'opening_balance' => array_sum(array_column($reconciliationData, 'opening_balance')),
            'additions' => array_sum(array_column($reconciliationData, 'additions')),
            'decreases' => array_sum(array_column($reconciliationData, 'decreases')),
            'fair_value_changes' => array_sum(array_column($reconciliationData, 'fair_value_changes')),
            'closing_balance' => array_sum(array_column($reconciliationData, 'closing_balance')),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'reconciliation' => $reconciliationData,
                    'totals' => $totals,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        }

        return view('reports.biological-reconciliation', compact(
            'reconciliationData',
            'totals',
            'startDate',
            'endDate',
            'company'
        ));
    }

    /**
     * GET /reports/biological-fair-value
     * Fair Value Changes Report
     */
    public function fairValueChanges(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company || !$company->usesPsak69()) {
            return redirect()->route('dashboard')->with('error', 'Modul PSAK 69 tidak aktif.');
        }

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get all valuations in period
        $valuations = BiologicalValuation::whereHas('biologicalAsset', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->with(['biologicalAsset.account', 'creator'])
            ->whereBetween('valuation_date', [$startDate, $endDate])
            ->orderBy('valuation_date', 'desc')
            ->get();

        // Group by asset
        $assetValuations = $valuations->groupBy('biological_asset_id');

        // Calculate summary
        $summary = [
            'total_gains' => $valuations->where('fair_value_change', '>', 0)->sum('fair_value_change'),
            'total_losses' => abs($valuations->where('fair_value_change', '<', 0)->sum('fair_value_change')),
            'net_change' => $valuations->sum('fair_value_change'),
            'total_valuations' => $valuations->count(),
        ];

        // Group by category
        $byCategory = [];
        foreach ($valuations as $valuation) {
            $category = $valuation->biologicalAsset->category;
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = [
                    'category' => $category,
                    'label' => $valuation->biologicalAsset->getCategoryLabel(),
                    'gains' => 0,
                    'losses' => 0,
                    'net' => 0,
                    'count' => 0,
                ];
            }
            
            if ($valuation->fair_value_change > 0) {
                $byCategory[$category]['gains'] += $valuation->fair_value_change;
            } else {
                $byCategory[$category]['losses'] += abs($valuation->fair_value_change);
            }
            
            $byCategory[$category]['net'] += $valuation->fair_value_change;
            $byCategory[$category]['count']++;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'valuations' => $valuations,
                    'summary' => $summary,
                    'by_category' => array_values($byCategory),
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        }

        return view('reports.biological-fair-value', compact(
            'valuations',
            'assetValuations',
            'summary',
            'byCategory',
            'startDate',
            'endDate',
            'company'
        ));
    }

    /**
     * GET /reports/biological-production
     * Production and Harvest Report
     */
    public function production(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company || !$company->usesPsak69()) {
            return redirect()->route('dashboard')->with('error', 'Modul PSAK 69 tidak aktif.');
        }

        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get all harvests in period
        $harvests = AgriculturalProduce::where('company_id', $company->id)
            ->with(['biologicalAsset', 'account', 'inventory'])
            ->whereBetween('harvest_date', [$startDate, $endDate])
            ->orderBy('harvest_date', 'desc')
            ->get();

        // Get transformations (production events)
        $transformations = BiologicalTransformation::whereHas('biologicalAsset', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->with('biologicalAsset')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Summary by product
        $byProduct = $harvests->groupBy('product_name')->map(function($items, $productName) {
            return [
                'product_name' => $productName,
                'total_quantity' => $items->sum('quantity'),
                'unit' => $items->first()->unit,
                'total_fair_value' => $items->sum('fair_value_at_harvest'),
                'total_carrying_amount' => $items->sum('carrying_amount'),
                'harvest_count' => $items->count(),
            ];
        });

        // Summary by biological asset
        $byAsset = $harvests->groupBy('biological_asset_id')->map(function($items) {
            $asset = $items->first()->biologicalAsset;
            return [
                'asset' => $asset,
                'total_harvests' => $items->count(),
                'total_quantity' => $items->sum('quantity'),
                'total_value' => $items->sum('carrying_amount'),
            ];
        });

        // Overall summary
        $summary = [
            'total_harvests' => $harvests->count(),
            'total_quantity' => $harvests->sum('quantity'),
            'total_fair_value' => $harvests->sum('fair_value_at_harvest'),
            'total_carrying_amount' => $harvests->sum('carrying_amount'),
            'unique_products' => $harvests->unique('product_name')->count(),
            'total_transformations' => $transformations->count(),
        ];

        // Transformation summary by type
        $transformationsByType = $transformations->groupBy('transformation_type')->map(function($items, $type) {
            return [
                'type' => $type,
                'label' => $items->first()->getTypeLabel(),
                'count' => $items->count(),
                'total_quantity_change' => $items->sum('quantity_change'),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'harvests' => $harvests,
                    'transformations' => $transformations,
                    'summary' => $summary,
                    'by_product' => $byProduct->values(),
                    'by_asset' => $byAsset->values(),
                    'transformations_by_type' => $transformationsByType->values(),
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        }

        return view('reports.biological-production', compact(
            'harvests',
            'transformations',
            'summary',
            'byProduct',
            'byAsset',
            'transformationsByType',
            'startDate',
            'endDate',
            'company'
        ));
    }

    /**
     * GET /reports/biological-disclosure
     * PSAK 69 Disclosure Report
     */
    public function disclosure(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company || !$company->usesPsak69()) {
            return redirect()->route('dashboard')->with('error', 'Modul PSAK 69 tidak aktif.');
        }

        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get all biological assets
        $assets = BiologicalAsset::where('company_id', $company->id)
            ->with(['account', 'fairValueAccount'])
            ->get();

        // Group by category
        $byCategory = $assets->groupBy('category')->map(function($items, $category) {
            return [
                'category' => $category,
                'label' => $items->first()->getCategoryLabel(),
                'total_quantity' => $items->sum('quantity'),
                'total_carrying_amount' => $items->sum('carrying_amount'),
                'assets' => $items,
            ];
        });

        // Group by maturity status
        $byMaturity = $assets->groupBy('maturity_status')->map(function($items, $status) {
            return [
                'status' => $status,
                'label' => $items->first()->getMaturityStatusLabel(),
                'count' => $items->count(),
                'total_carrying_amount' => $items->sum('carrying_amount'),
            ];
        });

        // Group by asset type
        $byType = $assets->groupBy('asset_type')->map(function($items, $type) {
            return [
                'type' => $type,
                'label' => $items->first()->getAssetTypeLabel(),
                'count' => $items->count(),
                'total_carrying_amount' => $items->sum('carrying_amount'),
            ];
        });

        // Valuation methods
        $byValuationMethod = $assets->groupBy('valuation_method')->map(function($items, $method) {
            return [
                'method' => $method,
                'label' => $method === 'fair_value' ? 'Nilai Wajar' : 'Biaya Perolehan',
                'count' => $items->count(),
                'total_carrying_amount' => $items->sum('carrying_amount'),
            ];
        });

        // Overall summary
        $summary = [
            'total_assets' => $assets->count(),
            'total_carrying_amount' => $assets->sum('carrying_amount'),
            'total_fair_value' => $assets->where('valuation_method', 'fair_value')->sum('current_fair_value'),
            'total_cost_to_sell' => $assets->sum('cost_to_sell'),
            'active_assets' => $assets->where('is_active', true)->count(),
            'inactive_assets' => $assets->where('is_active', false)->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'by_category' => $byCategory->values(),
                    'by_maturity' => $byMaturity->values(),
                    'by_type' => $byType->values(),
                    'by_valuation_method' => $byValuationMethod->values(),
                    'assets' => $assets,
                    'as_of_date' => $endDate,
                ],
            ]);
        }

        return view('reports.biological-disclosure', compact(
            'summary',
            'byCategory',
            'byMaturity',
            'byType',
            'byValuationMethod',
            'assets',
            'endDate',
            'company'
        ));
    }

    /**
     * Helper: Get asset value at specific date
     */
    private function getAssetValueAtDate(BiologicalAsset $asset, string $date): float
    {
        // Get the last valuation before or on the date
        $valuation = $asset->valuations()
            ->where('valuation_date', '<=', $date)
            ->orderBy('valuation_date', 'desc')
            ->first();

        if ($valuation) {
            return $valuation->current_fair_value - $valuation->cost_to_sell;
        }

        // If no valuation, use acquisition cost
        return $asset->acquisition_cost;
    }
}
