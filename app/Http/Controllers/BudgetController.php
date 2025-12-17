<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\BusinessUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    /**
     * Display budgets with filtering.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $year = $request->input('year', date('Y'));
        
        $query = Budget::where('company_id', $companyId)
            ->with(['account', 'businessUnit'])
            ->where('period_year', $year)
            ->orderBy('coa_id');

        // Filter by period type
        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        // Filter by account
        if ($request->filled('coa_id')) {
            $query->where('coa_id', $request->coa_id);
        }

        $budgets = $query->get();

        // Group by account for display
        $groupedBudgets = $budgets->groupBy('coa_id');

        // Get accounts for dropdown (only expense accounts typically)
        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_parent', false)
            ->whereIn('type', ['EXPENSE', 'REVENUE', 'ASSET', 'LIABILITY'])
            ->orderBy('code')
            ->get();

        // Get business units
        $businessUnits = BusinessUnit::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Available years
        $years = range(date('Y') - 2, date('Y') + 2);

        return view('budgets.index', compact('budgets', 'groupedBudgets', 'accounts', 'businessUnits', 'years', 'year'));
    }

    /**
     * Store a new budget.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coa_id' => 'required|exists:chart_of_accounts,id',
            'period_type' => 'required|in:MONTHLY,QUARTERLY,YEARLY',
            'period_year' => 'required|integer|min:2020|max:2030',
            'period_month' => 'nullable|integer|min:1|max:12',
            'period_quarter' => 'nullable|integer|min:1|max:4',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'business_unit_id' => 'nullable|exists:business_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $companyId = auth()->user()->company_id;

        // Check for duplicate
        $existing = Budget::where('company_id', $companyId)
            ->where('coa_id', $request->coa_id)
            ->where('business_unit_id', $request->business_unit_id)
            ->where('period_type', $request->period_type)
            ->where('period_year', $request->period_year)
            ->where('period_month', $request->period_month)
            ->where('period_quarter', $request->period_quarter)
            ->exists();

        if ($existing) {
            return response()->json(['errors' => ['coa_id' => ['Budget untuk akun dan periode ini sudah ada.']]], 422);
        }

        $budget = Budget::create([
            'company_id' => $companyId,
            'coa_id' => $request->coa_id,
            'business_unit_id' => $request->business_unit_id,
            'period_type' => $request->period_type,
            'period_year' => $request->period_year,
            'period_month' => $request->period_type === 'MONTHLY' ? $request->period_month : null,
            'period_quarter' => $request->period_type === 'QUARTERLY' ? $request->period_quarter : null,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget berhasil disimpan.',
            'budget' => $budget->load('account'),
        ]);
    }

    /**
     * Update an existing budget.
     */
    public function update(Request $request, Budget $budget)
    {
        // Ensure same company
        if ($budget->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $budget->update([
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget berhasil diperbarui.',
        ]);
    }

    /**
     * Delete a budget.
     */
    public function destroy(Budget $budget)
    {
        if ($budget->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget berhasil dihapus.',
        ]);
    }

    /**
     * Get budget vs actual comparison.
     */
    public function comparison(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $year = $request->input('year', date('Y'));
        $periodType = $request->input('period_type', 'MONTHLY');

        $budgets = Budget::where('company_id', $companyId)
            ->with('account')
            ->where('period_year', $year)
            ->where('period_type', $periodType)
            ->get();

        $comparison = $budgets->map(function ($budget) {
            $actual = $budget->getActual();
            $variance = $budget->getVariance();
            $variancePercent = $budget->getVariancePercent();

            return [
                'id' => $budget->id,
                'account_code' => $budget->account->code,
                'account_name' => $budget->account->name,
                'period' => $budget->period_label,
                'budget' => $budget->amount,
                'actual' => abs($actual),
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'is_over' => $budget->isOverBudget(),
            ];
        });

        return response()->json($comparison);
    }
}
