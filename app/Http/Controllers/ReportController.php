<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /reports/balance-sheet
     * Neraca / Laporan Posisi Keuangan.
     * 
     * Group by: Aset, Kewajiban, Ekuitas
     */
    public function balanceSheet(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        // Use defaults if not provided
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $startDate = $request->query('start_date');
        $unitId = $request->query('unit_id');

        // Get all NERACA accounts
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('report_type', 'NERACA')
            ->where('is_parent', false)
            ->orderBy('code')
            ->get();

        $report = [
            'Aset' => [],
            'Kewajiban' => [],
            'Ekuitas' => [],
        ];

        $totals = [
            'Aset' => 0,
            'Kewajiban' => 0,
            'Ekuitas' => 0,
        ];

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, null, $endDate, $unitId);
            
            $category = match($account->type) {
                'Asset' => 'Aset',
                'Liability' => 'Kewajiban',
                'Equity' => 'Ekuitas',
                default => null,
            };

            if ($category) {
                $report[$category][] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'balance' => $balance,
                ];
                $totals[$category] += $balance;
            }
        }

        $data = [
            'report_date' => $endDate,
            'unit_id' => $unitId,
            'sections' => $report,
            'totals' => $totals,
            'is_balanced' => abs($totals['Aset'] - ($totals['Kewajiban'] + $totals['Ekuitas'])) < 0.01,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.balance-sheet', $data);
    }

    /**
     * GET /reports/profit-loss
     * Laba Rugi / Laporan Aktivitas.
     * 
     * Group by: Pendapatan, HPP, Beban
     */
    public function profitLoss(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        // Use defaults if not provided
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $unitId = $request->query('unit_id');

        // Get all LABARUGI accounts
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('report_type', 'LABARUGI')
            ->where('is_parent', false)
            ->orderBy('code')
            ->get();

        $report = [
            'Pendapatan' => [],
            'Beban' => [],
        ];

        $totals = [
            'Pendapatan' => 0,
            'Beban' => 0,
        ];

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, $startDate, $endDate, $unitId);
            
            $category = match($account->type) {
                'Revenue' => 'Pendapatan',
                'Expense' => 'Beban',
                default => null,
            };

            if ($category) {
                $report[$category][] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'balance' => abs($balance),
                ];
                $totals[$category] += abs($balance);
            }
        }

        $netProfit = $totals['Pendapatan'] - $totals['Beban'];

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'unit_id' => $unitId,
            'sections' => $report,
            'total_revenue' => $totals['Pendapatan'],
            'total_expense' => $totals['Beban'],
            'net_profit' => $netProfit,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.profit-loss', $data);
    }

    /**
     * GET /reports/trial-balance
     * Neraca Saldo.
     */
    public function trialBalance(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        // Use defaults if not provided
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $unitId = $request->query('unit_id');

        // Get all detail accounts
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->orderBy('code')
            ->get();

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, null, $endDate, $unitId);
            
            $debit = 0;
            $credit = 0;

            if ($account->normal_balance === 'DEBIT') {
                if ($balance >= 0) {
                    $debit = $balance;
                } else {
                    $credit = abs($balance);
                }
            } else {
                if ($balance >= 0) {
                    $credit = $balance;
                } else {
                    $debit = abs($balance);
                }
            }

            if ($debit != 0 || $credit != 0) {
                $trialBalance[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'debit' => $debit,
                    'credit' => $credit,
                ];
                $totalDebit += $debit;
                $totalCredit += $credit;
            }
        }

        $data = [
            'report_date' => $endDate,
            'unit_id' => $unitId,
            'accounts' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.trial-balance', $data);
    }

    /**
     * GET /reports/ledger/{account_id?}
     * Buku Besar Per Akun.
     */
    public function ledger(Request $request, int $accountId = null)
    {
        $user = $request->user();
        $company = $user->company;

        // If no account_id provided and not JSON request, return the view with account selector
        if (!$accountId && !$request->wantsJson()) {
            return view('reports.ledger');
        }

        // If no account_id but JSON request, return error
        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'Account ID is required',
            ], 400);
        }

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
        ]);

        $account = ChartOfAccount::where('company_id', $company->id)
            ->findOrFail($accountId);

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $unitId = $request->unit_id;

        // Get beginning balance (before start_date)
        $beginningBalance = $this->getAccountBalance($account, null, date('Y-m-d', strtotime($startDate) - 86400), $unitId);

        // Get transactions in period
        $query = JournalItem::where('coa_id', $accountId)
            ->whereHas('journal', function ($q) use ($company, $startDate, $endDate, $unitId) {
                $q->where('company_id', $company->id)
                    ->where('is_posted', true)
                    ->whereBetween('date', [$startDate, $endDate]);
                
                if ($unitId) {
                    $q->where('business_unit_id', $unitId);
                }
            })
            ->with(['journal:id,date,reference,description'])
            ->orderBy('id')
            ->get();

        $transactions = [];
        $runningBalance = $beginningBalance;

        foreach ($query as $item) {
            if ($account->normal_balance === 'DEBIT') {
                $runningBalance += $item->debit - $item->credit;
            } else {
                $runningBalance += $item->credit - $item->debit;
            }

            $transactions[] = [
                'date' => $item->journal->date->format('Y-m-d'),
                'reference' => $item->journal->reference,
                'description' => $item->journal->description,
                'memo' => $item->memo,
                'debit' => $item->debit,
                'credit' => $item->credit,
                'balance' => $runningBalance,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'account' => [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'normal_balance' => $account->normal_balance,
                ],
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'unit_id' => $unitId,
                'beginning_balance' => $beginningBalance,
                'transactions' => $transactions,
                'ending_balance' => $runningBalance,
            ],
        ]);
    }

    /**
     * Calculate account balance.
     */
    protected function getAccountBalance(ChartOfAccount $account, ?string $startDate, ?string $endDate, ?int $unitId): float
    {
        $query = JournalItem::where('coa_id', $account->id)
            ->whereHas('journal', function ($q) use ($account, $startDate, $endDate, $unitId) {
                $q->where('company_id', $account->company_id)
                    ->where('is_posted', true);
                
                if ($startDate) {
                    $q->where('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
                if ($unitId) {
                    $q->where('business_unit_id', $unitId);
                }
            });

        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        if ($account->normal_balance === 'DEBIT') {
            return $totalDebit - $totalCredit;
        }
        
        return $totalCredit - $totalDebit;
    }

    /**
     * GET /reports/cash-flow
     * Laporan Arus Kas.
     */
    public function cashFlow(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $unitId = $request->query('unit_id');

        // Get cash accounts
        $cashAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where(function($q) {
                $q->where('code', 'like', '1.1.1%')
                  ->orWhere('code', 'like', '1100%')
                  ->orWhere('name', 'like', '%Kas%')
                  ->orWhere('name', 'like', '%Bank%');
            })
            ->where('is_parent', false)
            ->get();

        // Beginning balance
        $beginningBalance = 0;
        foreach ($cashAccounts as $acc) {
            $beginningBalance += $this->getAccountBalance($acc, null, date('Y-m-d', strtotime($startDate) - 86400), $unitId);
        }

        // Get all journal items for cash accounts in period
        $cashAccountIds = $cashAccounts->pluck('id');
        
        $journalItems = JournalItem::whereIn('coa_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($company, $startDate, $endDate, $unitId) {
                $q->where('company_id', $company->id)
                    ->where('is_posted', true)
                    ->whereBetween('date', [$startDate, $endDate]);
                if ($unitId) {
                    $q->where('business_unit_id', $unitId);
                }
            })
            ->with(['journal:id,date,reference,description,type'])
            ->get();

        // Classify by activity type
        $operatingIn = 0;
        $operatingOut = 0;
        $investingIn = 0;
        $investingOut = 0;
        $financingIn = 0;
        $financingOut = 0;

        foreach ($journalItems as $item) {
            $type = $item->journal->type ?? 'JU';
            $netCash = $item->debit - $item->credit;

            if (in_array($type, ['SI', 'PI', 'JU', 'CR', 'CP'])) {
                // Operating activities
                if ($netCash > 0) {
                    $operatingIn += $netCash;
                } else {
                    $operatingOut += abs($netCash);
                }
            } elseif (in_array($type, ['FA'])) {
                // Investing activities
                if ($netCash > 0) {
                    $investingIn += $netCash;
                } else {
                    $investingOut += abs($netCash);
                }
            } else {
                // Financing activities
                if ($netCash > 0) {
                    $financingIn += $netCash;
                } else {
                    $financingOut += abs($netCash);
                }
            }
        }

        $netOperating = $operatingIn - $operatingOut;
        $netInvesting = $investingIn - $investingOut;
        $netFinancing = $financingIn - $financingOut;
        $netChange = $netOperating + $netInvesting + $netFinancing;
        $endingBalance = $beginningBalance + $netChange;

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'beginning_balance' => $beginningBalance,
            'operating' => [
                'inflow' => $operatingIn,
                'outflow' => $operatingOut,
                'net' => $netOperating,
            ],
            'investing' => [
                'inflow' => $investingIn,
                'outflow' => $investingOut,
                'net' => $netInvesting,
            ],
            'financing' => [
                'inflow' => $financingIn,
                'outflow' => $financingOut,
                'net' => $netFinancing,
            ],
            'net_change' => $netChange,
            'ending_balance' => $endingBalance,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.cash-flow', $data);
    }

    /**
     * GET /reports/financial-analysis
     * Analisis Rasio Keuangan.
     */
    public function financialAnalysis(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $startDate = $request->query('start_date', now()->startOfYear()->format('Y-m-d'));

        // Get account balances by type
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->get();

        $balances = [
            'current_assets' => 0,
            'fixed_assets' => 0,
            'total_assets' => 0,
            'current_liabilities' => 0,
            'long_term_liabilities' => 0,
            'total_liabilities' => 0,
            'equity' => 0,
            'revenue' => 0,
            'expenses' => 0,
            'inventory' => 0,
        ];

        foreach ($accounts as $account) {
            $balance = abs($this->getAccountBalance($account, null, $endDate, null));
            
            if ($account->type === 'Asset') {
                $balances['total_assets'] += $balance;
                if (str_starts_with($account->code, '1.1') || str_starts_with($account->code, '11')) {
                    $balances['current_assets'] += $balance;
                    if (stripos($account->name, 'persediaan') !== false || stripos($account->name, 'inventory') !== false) {
                        $balances['inventory'] += $balance;
                    }
                } else {
                    $balances['fixed_assets'] += $balance;
                }
            } elseif ($account->type === 'Liability') {
                $balances['total_liabilities'] += $balance;
                if (str_starts_with($account->code, '2.1') || str_starts_with($account->code, '21')) {
                    $balances['current_liabilities'] += $balance;
                } else {
                    $balances['long_term_liabilities'] += $balance;
                }
            } elseif ($account->type === 'Equity') {
                $balances['equity'] += $balance;
            } elseif ($account->type === 'Revenue') {
                $balances['revenue'] += abs($this->getAccountBalance($account, $startDate, $endDate, null));
            } elseif ($account->type === 'Expense') {
                $balances['expenses'] += abs($this->getAccountBalance($account, $startDate, $endDate, null));
            }
        }

        $netProfit = $balances['revenue'] - $balances['expenses'];

        // Calculate ratios
        $ratios = [
            'liquidity' => [
                'current_ratio' => $balances['current_liabilities'] > 0 
                    ? round($balances['current_assets'] / $balances['current_liabilities'], 2) 
                    : null,
                'quick_ratio' => $balances['current_liabilities'] > 0 
                    ? round(($balances['current_assets'] - $balances['inventory']) / $balances['current_liabilities'], 2) 
                    : null,
            ],
            'profitability' => [
                'net_profit_margin' => $balances['revenue'] > 0 
                    ? round(($netProfit / $balances['revenue']) * 100, 2) 
                    : null,
                'return_on_assets' => $balances['total_assets'] > 0 
                    ? round(($netProfit / $balances['total_assets']) * 100, 2) 
                    : null,
                'return_on_equity' => $balances['equity'] > 0 
                    ? round(($netProfit / $balances['equity']) * 100, 2) 
                    : null,
            ],
            'leverage' => [
                'debt_to_equity' => $balances['equity'] > 0 
                    ? round($balances['total_liabilities'] / $balances['equity'], 2) 
                    : null,
                'debt_ratio' => $balances['total_assets'] > 0 
                    ? round(($balances['total_liabilities'] / $balances['total_assets']) * 100, 2) 
                    : null,
            ],
        ];

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'balances' => $balances,
            'net_profit' => $netProfit,
            'ratios' => $ratios,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.financial-analysis', $data);
    }
}

