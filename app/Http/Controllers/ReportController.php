<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use App\Helpers\ReportHelper;
use Barryvdh\DomPDF\Facade\Pdf;
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
                    $q->whereDate('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('date', '<=', $endDate);
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
            ->with(['journal:id,date,reference,description,source'])
            ->get();

        // Classify by activity type
        $operatingIn = 0;
        $operatingOut = 0;
        $investingIn = 0;
        $investingOut = 0;
        $financingIn = 0;
        $financingOut = 0;

        foreach ($journalItems as $item) {
            $source = $item->journal->source ?? 'manual';
            $netCash = $item->debit - $item->credit;

            if (in_array($source, ['sales', 'purchase', 'manual', 'cash_receipt', 'cash_payment'])) {
                // Operating activities
                if ($netCash > 0) {
                    $operatingIn += $netCash;
                } else {
                    $operatingOut += abs($netCash);
                }
            } elseif (in_array($source, ['asset', 'fixed_asset'])) {
                // Investing activities
                if ($netCash > 0) {
                    $investingIn += $netCash;
                } else {
                    $investingOut += abs($netCash);
                }
            } else {
                // Financing activities (equity, loan, etc) or fallback to operating
                if ($netCash > 0) {
                    $operatingIn += $netCash;
                } else {
                    $operatingOut += abs($netCash);
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

    /**
     * GET /reports/balance-sheet/export-pdf
     * Export Balance Sheet to PDF
     */
    public function exportBalanceSheetPDF(Request $request)
    {
        $validated = $request->validate([
            'end_date' => 'required|date',
            'unit_id' => 'nullable|exists:business_units,id'
        ]);

        $user = $request->user();
        $company = $user->company;

        // Get report data (reuse existing logic)
        $endDate = $validated['end_date'];
        $unitId = $validated['unit_id'] ?? null;

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
            'sections' => $report,
            'totals' => $totals,
            'is_balanced' => abs($totals['Aset'] - ($totals['Kewajiban'] + $totals['Ekuitas'])) < 0.01,
        ];

        // Prepare view data
        $viewData = [
            'company' => $company,
            'endDate' => $endDate,
            'data' => $data,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.pdf.balance-sheet', $viewData);
        $pdf->setPaper('A4', 'portrait');

        // Download with proper filename
        $filename = sprintf('Neraca_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return $pdf->download($filename);
    }

    /**
     * POST /reports/balance-sheet/comparative
     * Get comparative balance sheet data (JSON API)
     */
    public function balanceSheetComparative(Request $request)
    {
        $validated = $request->validate([
            'periods' => 'required|array|min:2|max:4',
            'periods.*.start_date' => 'nullable|date',
            'periods.*.end_date' => 'required|date',
            'periods.*.label' => 'required|string',
            'unit_id' => 'nullable|exists:business_units,id'
        ]);

        $user = $request->user();
        $company = $user->company;
        $periods = $validated['periods'];
        $unitId = $validated['unit_id'] ?? null;

        // Build comparative data
        $comparativeData = $this->buildComparativeData($company, $periods, 'NERACA', $unitId);

        return response()->json([
            'success' => true,
            'data' => $comparativeData
        ]);
    }

    /**
     * GET /reports/balance-sheet/comparative/export-pdf
     * Export Comparative Balance Sheet to PDF
     */
    public function exportBalanceSheetComparativePDF(Request $request)
    {
        $periodsJson = $request->query('periods');
        $periods = json_decode($periodsJson, true);

        if (!$periods || !is_array($periods) || count($periods) < 2) {
            return back()->withErrors(['periods' => 'At least 2 periods required for comparison']);
        }

        $user = $request->user();
        $company = $user->company;
        $unitId = $request->query('unit_id');

        // Build comparative data
        $comparativeData = $this->buildComparativeData($company, $periods, 'NERACA', $unitId);

        // Prepare view data
        $viewData = [
            'company' => $company,
            'periods' => $periods,
            'data' => $comparativeData,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.pdf.balance-sheet-comparative', $viewData);
        $pdf->setPaper('A4', 'portrait');

        // Download with proper filename
        $filename = sprintf('Neraca_Komparatif_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d')
        );

        return $pdf->download($filename);
    }

    /**
     * Build comparative data for reports
     */
    protected function buildComparativeData($company, array $periods, string $reportType, $unitId = null)
    {
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('report_type', $reportType)
            ->where('is_parent', false)
            ->orderBy('code')
            ->get();

        // Determine sections based on report type
        $sections = match($reportType) {
            'NERACA' => ['Aset' => 'Asset', 'Kewajiban' => 'Liability', 'Ekuitas' => 'Equity'],
            'LABARUGI' => ['Pendapatan' => 'Revenue', 'Beban' => 'Expense'],
            default => []
        };

        $report = array_fill_keys(array_keys($sections), []);
        $totals = array_fill_keys(array_keys($sections), array_fill(0, count($periods), 0));
        
        $accountsIncreased = 0;
        $accountsDecreased = 0;
        $accountsStable = 0;

        foreach ($accounts as $account) {
            $values = [];
            
            // Get balance for each period
            foreach ($periods as $period) {
                $startDate = $period['start_date'] ?? null;
                $endDate = $period['end_date'];
                $balance = $this->getAccountBalance($account, $startDate, $endDate, $unitId);
                $values[] = $balance;
            }

            // Calculate variance (first vs last period)
            $variance = ReportHelper::calculateVariance($values[0], end($values));

            // Classify trend
            if ($variance['trend'] === 'increase') $accountsIncreased++;
            elseif ($variance['trend'] === 'decrease') $accountsDecreased++;
            else $accountsStable++;

            // Categorize by account type
            foreach ($sections as $sectionName => $accountType) {
                if ($account->type === $accountType) {
                    $report[$sectionName][] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'values' => $values,
                        'variance' => $variance
                    ];

                    // Add to totals
                    foreach ($values as $index => $value) {
                        $totals[$sectionName][$index] += $value;
                    }
                    break;
                }
            }
        }

        // Calculate totals variance
        $totalsVariance = [];
        foreach ($totals as $section => $values) {
            $totalsVariance[$section] = ReportHelper::calculateVariance($values[0], end($values));
        }

        // Calculate summary statistics
        $totalAccounts = $accountsIncreased + $accountsDecreased + $accountsStable;
        $avgGrowthRate = $totalAccounts > 0 
            ? array_sum(array_column(array_column(array_merge(...array_values($report)), 'variance'), 'percentage')) / $totalAccounts
            : 0;

        return [
            'company' => [
                'name' => $company->name,
                'logo' => $company->logo
            ],
            'report_type' => $reportType === 'NERACA' ? 'balance_sheet_comparative' : 'profit_loss_comparative',
            'periods' => $periods,
            'sections' => $report,
            'totals' => $totals,
            'totals_variance' => $totalsVariance,
            'summary' => [
                'total_accounts' => $totalAccounts,
                'accounts_increased' => $accountsIncreased,
                'accounts_decreased' => $accountsDecreased,
                'accounts_stable' => $accountsStable,
                'avg_growth_rate' => round($avgGrowthRate, 2)
            ]
        ];
    }

    /**
     * GET /reports/profit-loss/export-pdf
     * Export Profit-Loss to PDF
     */
    public function exportProfitLossPDF(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'required|date',
            'unit_id' => 'nullable|exists:business_units,id'
        ]);

        $user = $request->user();
        $company = $user->company;

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'];
        $unitId = $validated['unit_id'] ?? null;

        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('report_type', 'LABARUGI')
            ->where('is_parent', false)
            ->orderBy('code')
            ->get();

        $report = [
            'Pendapatan' => [],
            'Beban' => [],
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
                    'balance' => $balance,
                ];
            }
        }

        $totalRevenue = array_sum(array_column($report['Pendapatan'], 'balance'));
        $totalExpense = array_sum(array_column($report['Beban'], 'balance'));
        $netProfit = $totalRevenue - $totalExpense;

        $viewData = [
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sections' => $report,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        $pdf = Pdf::loadView('reports.pdf.profit-loss', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('LabaRugi_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return $pdf->download($filename);
    }

    /**
     * POST /reports/profit-loss/comparative
     * Get comparative profit-loss data (JSON API)
     */
    public function profitLossComparative(Request $request)
    {
        $validated = $request->validate([
            'periods' => 'required|array|min:2|max:4',
            'periods.*.start_date' => 'nullable|date',
            'periods.*.end_date' => 'required|date',
            'periods.*.label' => 'required|string',
            'unit_id' => 'nullable|exists:business_units,id'
        ]);

        $user = $request->user();
        $company = $user->company;
        $periods = $validated['periods'];
        $unitId = $validated['unit_id'] ?? null;

        $comparativeData = $this->buildComparativeData($company, $periods, 'LABARUGI', $unitId);

        return response()->json([
            'success' => true,
            'data' => $comparativeData
        ]);
    }

    /**
     * GET /reports/profit-loss/comparative/export-pdf
     * Export Comparative Profit-Loss to PDF
     */
    public function exportProfitLossComparativePDF(Request $request)
    {
        $periodsJson = $request->query('periods');
        $periods = json_decode($periodsJson, true);

        if (!$periods || !is_array($periods) || count($periods) < 2) {
            return back()->withErrors(['periods' => 'At least 2 periods required for comparison']);
        }

        $user = $request->user();
        $company = $user->company;
        $unitId = $request->query('unit_id');

        $comparativeData = $this->buildComparativeData($company, $periods, 'LABARUGI', $unitId);

        $viewData = [
            'company' => $company,
            'periods' => $periods,
            'data' => $comparativeData,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        $pdf = Pdf::loadView('reports.pdf.profit-loss-comparative', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('LabaRugi_Komparatif_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d')
        );

        return $pdf->download($filename);
    }
    public function exportCashFlowPDF(Request $request)
{
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'required|date',
    ]);
    $user = $request->user();
    $company = $user->company;
    
    $startDate = $validated['start_date'] ?? null;
    $endDate = $validated['end_date'];
    // Get cash accounts
    $cashAccounts = ChartOfAccount::where('company_id', $company->id)
        ->where(function($q) {
            $q->where('code', 'LIKE', '1.1.1%')
              ->orWhere('code', 'LIKE', '1100%')
              ->orWhere('name', 'LIKE', '%Kas%')
              ->orWhere('name', 'LIKE', '%Bank%');
        })
        ->where('is_parent', false)
        ->get();
    $beginningBalance = 0;
    if ($startDate) {
        foreach ($cashAccounts as $account) {
            $beginningBalance += $this->getAccountBalance($account, null, $startDate, null);
        }
    }
    $operating = ['inflow' => 0, 'outflow' => 0, 'net' => 0];
    $investing = ['inflow' => 0, 'outflow' => 0, 'net' => 0];
    $financing = ['inflow' => 0, 'outflow' => 0, 'net' => 0];
    $cashItems = JournalItem::whereHas('journal', function($q) use ($company, $startDate, $endDate) {
            $q->where('company_id', $company->id)
              ->where('is_posted', true);
            if ($startDate) $q->where('date', '>=', $startDate);
            $q->where('date', '<=', $endDate);
        })
        ->whereIn('coa_id', $cashAccounts->pluck('id'))
        ->with(['journal'])
        ->get();
    foreach ($cashItems as $item) {
        $type = $item->journal->type ?? '';
        $netCash = $item->debit - $item->credit;
        if (in_array($type, ['SI', 'PI', 'JU', 'CR', 'CP', ''])) {
            if ($netCash > 0) $operating['inflow'] += $netCash;
            else $operating['outflow'] += abs($netCash);
        } elseif (in_array($type, ['FA'])) {
            if ($netCash > 0) $investing['inflow'] += $netCash;
            else $investing['outflow'] += abs($netCash);
        } else {
            if ($netCash > 0) $financing['inflow'] += $netCash;
            else $financing['outflow'] += abs($netCash);
        }
    }
    $operating['net'] = $operating['inflow'] - $operating['outflow'];
    $investing['net'] = $investing['inflow'] - $investing['outflow'];
    $financing['net'] = $financing['inflow'] - $financing['outflow'];
    $netChange = $operating['net'] + $investing['net'] + $financing['net'];
    $endingBalance = $beginningBalance + $netChange;
    $viewData = [
        'company' => $company,
        'period' => ['start_date' => $startDate, 'end_date' => $endDate],
        'beginning_balance' => $beginningBalance,
        'operating' => $operating,
        'investing' => $investing,
        'financing' => $financing,
        'net_change' => $netChange,
        'ending_balance' => $endingBalance,
        'timestamp' => now()->format('d M Y H:i'),
        'city' => ReportHelper::extractCity($company->address ?? ''),
        'date' => now()->format('Y-m-d')
    ];
    $pdf = Pdf::loadView('reports.pdf.cash-flow', $viewData);
    $pdf->setPaper('A4', 'portrait');
    $filename = sprintf('ArusKas_%s_%s.pdf', 
        str_replace(' ', '_', $company->name), 
        date('Y-m-d', strtotime($endDate))
    );
    return $pdf->download($filename);
}

    /**
     * GET /reports/equity-changes
     * Statement of Changes in Equity
     */
    public function equityChanges(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $user = $request->user();
        $company = $user->company;
        
        $endDate = $validated['end_date'] ?? date('Y-m-d');
        $startDate = $validated['start_date'] ?? date('Y-01-01');

        // Get equity accounts (code 3.x.x)
        $equityAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where(function($q) {
                $q->where('code', 'LIKE', '3%')
                  ->orWhere('code', 'LIKE', '3.%');
            })
            ->where('is_parent', false)
            ->get();

        // Calculate beginning equity (before start date)
        $beginningCapital = 0;
        $beginningRetained = 0;
        
        foreach ($equityAccounts as $account) {
            $balance = $this->getAccountBalance($account, null, $startDate, null);
            if (str_contains(strtolower($account->name), 'modal') || str_contains(strtolower($account->name), 'capital')) {
                $beginningCapital += $balance;
            } else {
                $beginningRetained += $balance;
            }
        }
        $beginningEquity = $beginningCapital + $beginningRetained;

        // Calculate net income for the period
        $netIncome = $this->calculateNetIncome($company, $startDate, $endDate);

        // Calculate capital changes during period
        $capitalChanges = [];
        $additions = 0;
        $deductions = 0;

        // Get equity transactions in period
        $equityTransactions = JournalItem::whereIn('coa_id', $equityAccounts->pluck('id'))
            ->whereHas('journal', function($q) use ($company, $startDate, $endDate) {
                $q->where('company_id', $company->id)
                  ->where('is_posted', true)
                  ->whereBetween('date', [$startDate, $endDate]);
            })
            ->with(['journal', 'account'])
            ->get();

        foreach ($equityTransactions as $item) {
            $amount = $item->credit - $item->debit; // Credit increases equity
            $type = str_contains(strtolower($item->account->name), 'modal') ? 'capital' : 'retained';
            
            $capitalChanges[] = [
                'date' => $item->journal->date,
                'description' => $item->journal->description ?: $item->account->name,
                'amount' => $amount,
                'type' => $type
            ];
            
            if ($amount > 0) $additions += $amount;
            else $deductions += abs($amount);
        }

        // Add net income to additions
        if ($netIncome > 0) {
            $additions += $netIncome;
        } else {
            $deductions += abs($netIncome);
        }

        // Calculate ending equity
        $endingCapital = $beginningCapital;
        $endingRetained = $beginningRetained + $netIncome;
        
        foreach ($capitalChanges as $change) {
            if ($change['type'] === 'capital') {
                $endingCapital += $change['amount'];
            } else {
                $endingRetained += $change['amount'];
            }
        }
        $endingEquity = $endingCapital + $endingRetained;

        $data = [
            'beginning_capital' => $beginningCapital,
            'beginning_retained' => $beginningRetained,
            'beginning_equity' => $beginningEquity,
            'changes' => $capitalChanges,
            'net_income' => $netIncome,
            'additions' => $additions,
            'deductions' => $deductions,
            'ending_capital' => $endingCapital,
            'ending_retained' => $endingRetained,
            'ending_equity' => $endingEquity,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.equity-changes', [
            'data' => $data,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate]
        ]);
    }

    /**
     * Get account balance for a specific period (between start and end date)
     */
    private function getAccountBalanceForPeriod($account, $startDate, $endDate)
    {
        $query = JournalItem::where('coa_id', $account->id)
            ->whereHas('journal', function($q) use ($startDate, $endDate) {
                $q->where('is_posted', true)
                  ->whereBetween('date', [$startDate, $endDate]);
            });

        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        // For revenue accounts (4.x.x), balance is credit - debit
        // For expense accounts (5.x.x, 6.x.x), balance is debit - credit
        $accountType = substr($account->code, 0, 1);
        
        if (in_array($accountType, ['4'])) { // Revenue
            return $totalCredit - $totalDebit;
        } else { // Expense
            return $totalDebit - $totalCredit;
        }
    }

    /**
     * Calculate net income for period
     */
    private function calculateNetIncome($company, $startDate, $endDate)
    {
        // Revenue accounts (4.x.x)
        $revenueAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('code', 'LIKE', '4%')
            ->where('is_parent', false)
            ->get();

        // Expense accounts (5.x.x, 6.x.x)
        $expenseAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where(function($q) {
                $q->where('code', 'LIKE', '5%')
                  ->orWhere('code', 'LIKE', '6%');
            })
            ->where('is_parent', false)
            ->get();

        $totalRevenue = 0;
        foreach ($revenueAccounts as $account) {
            $totalRevenue += $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
        }

        $totalExpense = 0;
        foreach ($expenseAccounts as $account) {
            $totalExpense += $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
        }

        return $totalRevenue - $totalExpense;
    }

    /**
     * GET /reports/equity-changes/export-pdf
     * Export Statement of Changes in Equity to PDF
     */
    public function exportEquityChangesPDF(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'required|date',
        ]);

        $user = $request->user();
        $company = $user->company;
        
        $endDate = $validated['end_date'];
        $startDate = $validated['start_date'] ?? date('Y-01-01');

        // Reuse data calculation from equityChanges
        $request->merge(['start_date' => $startDate, 'end_date' => $endDate]);
        
        // Get equity data
        $equityAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('code', 'LIKE', '3%')
            ->where('is_parent', false)
            ->get();

        $beginningCapital = 0;
        $beginningRetained = 0;
        
        foreach ($equityAccounts as $account) {
            $balance = $this->getAccountBalance($account, null, $startDate, null);
            if (str_contains(strtolower($account->name), 'modal') || str_contains(strtolower($account->name), 'capital')) {
                $beginningCapital += $balance;
            } else {
                $beginningRetained += $balance;
            }
        }
        $beginningEquity = $beginningCapital + $beginningRetained;

        $netIncome = $this->calculateNetIncome($company, $startDate, $endDate);

        $capitalChanges = [];
        $equityTransactions = JournalItem::whereIn('coa_id', $equityAccounts->pluck('id'))
            ->whereHas('journal', function($q) use ($company, $startDate, $endDate) {
                $q->where('company_id', $company->id)
                  ->where('is_posted', true)
                  ->whereBetween('date', [$startDate, $endDate]);
            })
            ->with(['journal', 'account'])
            ->get();

        foreach ($equityTransactions as $item) {
            $amount = $item->credit - $item->debit;
            $type = str_contains(strtolower($item->account->name), 'modal') ? 'capital' : 'retained';
            $capitalChanges[] = [
                'date' => $item->journal->date,
                'description' => $item->journal->description ?: $item->account->name,
                'amount' => $amount,
                'type' => $type
            ];
        }

        $endingCapital = $beginningCapital;
        $endingRetained = $beginningRetained + $netIncome;
        foreach ($capitalChanges as $change) {
            if ($change['type'] === 'capital') {
                $endingCapital += $change['amount'];
            } else {
                $endingRetained += $change['amount'];
            }
        }
        $endingEquity = $endingCapital + $endingRetained;

        $viewData = [
            'company' => $company,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'beginning_capital' => $beginningCapital,
            'beginning_retained' => $beginningRetained,
            'beginning_equity' => $beginningEquity,
            'changes' => $capitalChanges,
            'net_income' => $netIncome,
            'ending_capital' => $endingCapital,
            'ending_retained' => $endingRetained,
            'ending_equity' => $endingEquity,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        $pdf = Pdf::loadView('reports.pdf.equity-changes', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('PerubahanEkuitas_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return $pdf->download($filename);
    }

    /**
     * GET /reports/journal-list
     * Daftar Jurnal.
     */
    public function journalList(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $source = $request->query('source');
        $unitId = $request->query('unit_id');

        $query = \App\Models\Journal::where('company_id', $company->id)
            ->where('is_posted', true)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with(['items.account:id,code,name', 'businessUnit:id,name'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($source) {
            $query->where('source', $source);
        }
        if ($unitId) {
            $query->where('business_unit_id', $unitId);
        }

        $journals = $query->paginate(50);

        // Calculate totals
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($journals as $journal) {
            foreach ($journal->items as $item) {
                $totalDebit += $item->debit;
                $totalCredit += $item->credit;
            }
        }

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'journals' => $journals,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'source' => $source,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.journal-list', $data);
    }

    /**
     * GET /reports/sales
     * Laporan Penjualan.
     */
    public function salesReport(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $contactId = $request->query('contact_id');
        $status = $request->query('status');

        $query = \App\Models\Invoice::where('company_id', $company->id)
            ->where('type', 'Sales')
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['contact:id,name,code'])
            ->orderBy('date', 'desc');

        if ($contactId) {
            $query->where('contact_id', $contactId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->get();

        // Calculate summaries
        $totalSales = $invoices->sum('total');
        $totalTax = $invoices->sum('tax');
        $totalDiscount = $invoices->sum('discount');
        $countInvoices = $invoices->count();

        // Group by customer
        $byCustomer = $invoices->groupBy('contact_id')->map(function ($items, $contactId) {
            $contact = $items->first()->contact;
            return [
                'contact_name' => $contact ? $contact->name : 'Unknown',
                'count' => $items->count(),
                'total' => $items->sum('total'),
            ];
        })->values();

        // Get customers for dropdown
        $customers = \App\Models\Contact::where('company_id', $company->id)
            ->customers()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'invoices' => $invoices,
            'summary' => [
                'total_sales' => $totalSales,
                'total_tax' => $totalTax,
                'total_discount' => $totalDiscount,
                'count' => $countInvoices,
            ],
            'by_customer' => $byCustomer,
            'customers' => $customers,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.sales', $data);
    }

    /**
     * GET /reports/purchases
     * Laporan Pembelian.
     */
    public function purchaseReport(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $contactId = $request->query('contact_id');
        $status = $request->query('status');

        $query = \App\Models\Invoice::where('company_id', $company->id)
            ->where('type', 'Purchase')
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['contact:id,name,code'])
            ->orderBy('date', 'desc');

        if ($contactId) {
            $query->where('contact_id', $contactId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->get();

        // Calculate summaries
        $totalPurchases = $invoices->sum('total');
        $totalTax = $invoices->sum('tax');
        $totalDiscount = $invoices->sum('discount');
        $countInvoices = $invoices->count();

        // Group by supplier
        $bySupplier = $invoices->groupBy('contact_id')->map(function ($items, $contactId) {
            $contact = $items->first()->contact;
            return [
                'contact_name' => $contact ? $contact->name : 'Unknown',
                'count' => $items->count(),
                'total' => $items->sum('total'),
            ];
        })->values();

        // Get suppliers for dropdown
        $suppliers = \App\Models\Contact::where('company_id', $company->id)
            ->suppliers()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'invoices' => $invoices,
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_tax' => $totalTax,
                'total_discount' => $totalDiscount,
                'count' => $countInvoices,
            ],
            'by_supplier' => $bySupplier,
            'suppliers' => $suppliers,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.purchases', $data);
    }
}


