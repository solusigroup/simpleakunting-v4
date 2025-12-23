<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * GET /accounts
     * List Chart of Accounts dengan filter optional.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Company not found'], 400);
            }
            return redirect()->route('dashboard');
        }

        $query = ChartOfAccount::where('company_id', $company->id)
            ->with('parent:id,code,name')
            ->orderBy('code');

        // Filter by type (ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by report type (NERACA, LABARUGI)
        if ($request->has('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        // Filter detail accounts only (can have transactions)
        if ($request->boolean('detail_only')) {
            $query->where('is_parent', false);
        }

        $accounts = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $accounts,
            ]);
        }

        return view('accounts.index', compact('accounts'));
    }

    /**
     * POST /accounts
     * Tambah akun baru.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambah data.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:Asset,Liability,Equity,Revenue,Expense'],
            'report_type' => ['required', 'in:NERACA,LABARUGI'],
            'normal_balance' => ['required', 'in:DEBIT,KREDIT'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_parent' => ['boolean'],
            'account_category' => ['nullable', 'string', 'in:cash_bank,accounts_receivable,other_receivable,inventory,prepaid_expense,other_current_asset,fixed_asset,accumulated_depreciation,intangible_asset,other_asset,accounts_payable,other_payable,accrued_expense,other_current_liability,long_term_liability,equity_capital,equity_retained,equity_other,revenue_sales,revenue_service,revenue_other,cogs,expense_operational,expense_administrative,expense_selling,expense_other,other_income,other_expense,general'],
        ]);

        // Check if code already exists
        $exists = ChartOfAccount::where('company_id', $company->id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kode akun sudah digunakan.',
            ], 422);
        }

        // Determine level
        $level = 1;
        if ($request->parent_id) {
            $parent = ChartOfAccount::find($request->parent_id);
            $level = $parent ? $parent->level + 1 : 1;
        }

        $account = ChartOfAccount::create([
            'company_id' => $company->id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'report_type' => $request->report_type,
            'normal_balance' => $request->normal_balance,
            'parent_id' => $request->parent_id,
            'is_parent' => $request->boolean('is_parent', false),
            'level' => $level,
            'is_system' => false,
            'is_active' => true,
            'account_category' => $request->account_category,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil ditambahkan.',
            'data' => $account,
        ], 201);
    }

    /**
     * PUT /accounts/{id}
     * Edit akun.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit data.',
            ], 403);
        }

        $account = ChartOfAccount::where('company_id', $user->company_id)
            ->findOrFail($id);

        // System accounts cannot change their code
        if ($account->is_system && $request->has('code') && $request->code !== $account->code) {
            return response()->json([
                'success' => false,
                'message' => 'Akun sistem tidak dapat diubah kodenya.',
            ], 422);
        }

        // Validate all editable fields
        $validated = $request->validate([
            'code' => ['sometimes', 'required', 'string', 'max:20'],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'type' => ['sometimes', 'required', 'in:Asset,Liability,Equity,Revenue,Expense'],
            'report_type' => ['sometimes', 'required', 'in:NERACA,LABARUGI'],
            'normal_balance' => ['sometimes', 'required', 'in:DEBIT,KREDIT'],
            'is_parent' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'account_category' => ['nullable', 'string', 'in:cash_bank,accounts_receivable,other_receivable,inventory,prepaid_expense,other_current_asset,fixed_asset,accumulated_depreciation,intangible_asset,other_asset,accounts_payable,other_payable,accrued_expense,other_current_liability,long_term_liability,equity_capital,equity_retained,equity_other,revenue_sales,revenue_service,revenue_other,cogs,expense_operational,expense_administrative,expense_selling,expense_other,other_income,other_expense,general'],
        ]);

        // Check if code is being changed and if it already exists
        if ($request->has('code') && $request->code !== $account->code) {
            $exists = ChartOfAccount::where('company_id', $user->company_id)
                ->where('code', $request->code)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode akun sudah digunakan.',
                ], 422);
            }
        }

        // Update the account with validated data
        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui.',
            'data' => $account->fresh(),
        ]);
    }

    /**
     * GET /accounts/{id}
     * Detail akun.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $account = ChartOfAccount::where('company_id', $user->company_id)
            ->with(['parent:id,code,name', 'children:id,code,name,parent_id'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }
}
