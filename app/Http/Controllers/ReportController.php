<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use App\Helpers\ReportHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

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
            
            // Contra-asset accounts (Asset type with KREDIT normal balance) should reduce total assets
            // Examples: Akumulasi Penyusutan
            if ($account->type === 'Asset' && $account->normal_balance === 'KREDIT') {
                $balance = -$balance;
            }
            
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

        // Calculate Net Income (Laba/Rugi) from LABARUGI accounts
        // Period: from beginning of year to endDate
        $startOfYear = date('Y-01-01', strtotime($endDate));
        $labaRugiAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('report_type', 'LABARUGI')
            ->where('is_parent', false)
            ->get();

        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($labaRugiAccounts as $account) {
            $balance = $this->getAccountBalance($account, $startOfYear, $endDate, $unitId);
            
            if ($account->type === 'Revenue') {
                $totalRevenue += abs($balance);
            } elseif ($account->type === 'Expense') {
                $totalExpense += abs($balance);
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        // Add Net Income to Ekuitas section
        $report['Ekuitas'][] = [
            'account_code' => '',
            'account_name' => 'Laba (Rugi) Periode Berjalan',
            'balance' => $netIncome,
            'is_net_income' => true, // Flag to style differently in view
        ];
        $totals['Ekuitas'] += $netIncome;

        $data = [
            'report_date' => $endDate,
            'unit_id' => $unitId,
            'sections' => $report,
            'totals' => $totals,
            'net_income' => $netIncome,
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

        // Get cash accounts using category-based scope with fallback
        $cashAccounts = ChartOfAccount::where('company_id', $company->id)
            ->cashBank()
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
                if ($account->isCurrentAsset()) {
                    $balances['current_assets'] += $balance;
                    if ($account->account_category === 'inventory' || 
                        stripos($account->name, 'persediaan') !== false || 
                        stripos($account->name, 'inventory') !== false) {
                        $balances['inventory'] += $balance;
                    }
                } else {
                    $balances['fixed_assets'] += $balance;
                }
            } elseif ($account->type === 'Liability') {
                $balances['total_liabilities'] += $balance;
                if ($account->isCurrentLiability()) {
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
            
            // Contra-asset accounts (Asset type with KREDIT normal balance) should reduce total assets
            if ($account->type === 'Asset' && $account->normal_balance === 'KREDIT') {
                $balance = -$balance;
            }
            
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

        // Calculate Net Income (Laba/Rugi) from LABARUGI accounts
        $startOfYear = date('Y-01-01', strtotime($endDate));
        $labaRugiAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('report_type', 'LABARUGI')
            ->where('is_parent', false)
            ->get();

        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($labaRugiAccounts as $account) {
            $balance = $this->getAccountBalance($account, $startOfYear, $endDate, $unitId);
            
            if ($account->type === 'Revenue') {
                $totalRevenue += abs($balance);
            } elseif ($account->type === 'Expense') {
                $totalExpense += abs($balance);
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        // Add Net Income to Ekuitas section
        $report['Ekuitas'][] = [
            'account_code' => '',
            'account_name' => 'Laba (Rugi) Periode Berjalan',
            'balance' => $netIncome,
            'is_net_income' => true,
        ];
        $totals['Ekuitas'] += $netIncome;

        $data = [
            'sections' => $report,
            'totals' => $totals,
            'net_income' => $netIncome,
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
                
                // Contra-asset accounts (Asset type with KREDIT normal balance) should reduce total assets
                if ($reportType === 'NERACA' && $account->type === 'Asset' && $account->normal_balance === 'KREDIT') {
                    $balance = -$balance;
                }
                
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

        // For NERACA: Calculate Net Income for each period and add to Ekuitas
        if ($reportType === 'NERACA') {
            $labaRugiAccounts = ChartOfAccount::where('company_id', $company->id)
                ->where('report_type', 'LABARUGI')
                ->where('is_parent', false)
                ->get();

            $netIncomeValues = [];
            
            foreach ($periods as $period) {
                $endDate = $period['end_date'];
                $startOfYear = date('Y-01-01', strtotime($endDate));
                
                $totalRevenue = 0;
                $totalExpense = 0;
                
                foreach ($labaRugiAccounts as $account) {
                    $balance = $this->getAccountBalance($account, $startOfYear, $endDate, $unitId);
                    
                    if ($account->type === 'Revenue') {
                        $totalRevenue += abs($balance);
                    } elseif ($account->type === 'Expense') {
                        $totalExpense += abs($balance);
                    }
                }
                
                $netIncomeValues[] = $totalRevenue - $totalExpense;
            }

            // Calculate variance for net income
            $netIncomeVariance = ReportHelper::calculateVariance($netIncomeValues[0], end($netIncomeValues));

            // Add Net Income to Ekuitas section
            $report['Ekuitas'][] = [
                'account_code' => '',
                'account_name' => 'Laba (Rugi) Periode Berjalan',
                'values' => $netIncomeValues,
                'variance' => $netIncomeVariance,
                'is_net_income' => true
            ];

            // Add Net Income to Ekuitas totals
            foreach ($netIncomeValues as $index => $value) {
                $totals['Ekuitas'][$index] += $value;
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
            'unit_id' => 'nullable|exists:business_units,id',
        ]);

        $user = $request->user();
        $company = $user->company;
        
        $endDate = $validated['end_date'] ?? date('Y-m-d');
        $startDate = $validated['start_date'] ?? date('Y-01-01');
        $unitId = $validated['unit_id'] ?? null;

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
            $balance = $this->getAccountBalance($account, null, $startDate, $unitId);
            if (str_contains(strtolower($account->name), 'modal') || str_contains(strtolower($account->name), 'capital')) {
                $beginningCapital += $balance;
            } else {
                $beginningRetained += $balance;
            }
        }
        $beginningEquity = $beginningCapital + $beginningRetained;

        // Calculate net income for the period
        $netIncome = $this->calculateNetIncome($company, $startDate, $endDate, $unitId);

        // Calculate capital changes during period
        $capitalChanges = [];
        $additions = 0;
        $deductions = 0;

        // Get equity transactions in period
        $equityTransactions = JournalItem::whereIn('coa_id', $equityAccounts->pluck('id'))
            ->whereHas('journal', function($q) use ($company, $startDate, $endDate, $unitId) {
                $q->where('company_id', $company->id)
                  ->where('is_posted', true)
                  ->whereBetween('date', [$startDate, $endDate]);
                if ($unitId) {
                    $q->where('business_unit_id', $unitId);
                }
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
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'unit_id' => $unitId,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('reports.equity-changes', [
            'data' => $data,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'unit_id' => $unitId,
        ]);
    }

    /**
     * Get account balance for a specific period (between start and end date)
     */
     private function getAccountBalanceForPeriod($account, $startDate, $endDate, $unitId = null)
     {
         $query = JournalItem::where('coa_id', $account->id)
             ->whereHas('journal', function($q) use ($startDate, $endDate, $unitId) {
                 $q->where('is_posted', true)
                   ->whereBetween('date', [$startDate, $endDate]);
                 if ($unitId) {
                     $q->where('business_unit_id', $unitId);
                 }
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
     private function calculateNetIncome($company, $startDate, $endDate, $unitId = null)
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
             $totalRevenue += $this->getAccountBalanceForPeriod($account, $startDate, $endDate, $unitId);
         }
 
         $totalExpense = 0;
         foreach ($expenseAccounts as $account) {
             $totalExpense += $this->getAccountBalanceForPeriod($account, $startDate, $endDate, $unitId);
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
            'unit_id' => 'nullable|exists:business_units,id',
        ]);

        $user = $request->user();
        $company = $user->company;
        
        $endDate = $validated['end_date'];
        $startDate = $validated['start_date'] ?? date('Y-01-01');
        $unitId = $validated['unit_id'] ?? null;

        // Reuse data calculation from equityChanges
        $request->merge(['start_date' => $startDate, 'end_date' => $endDate, 'unit_id' => $unitId]);
        
        // Get equity data
        $equityAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('code', 'LIKE', '3%')
            ->where('is_parent', false)
            ->get();

        $beginningCapital = 0;
        $beginningRetained = 0;
        
        foreach ($equityAccounts as $account) {
            $balance = $this->getAccountBalance($account, null, $startDate, $unitId);
            if (str_contains(strtolower($account->name), 'modal') || str_contains(strtolower($account->name), 'capital')) {
                $beginningCapital += $balance;
            } else {
                $beginningRetained += $balance;
            }
        }
        $beginningEquity = $beginningCapital + $beginningRetained;

        $netIncome = $this->calculateNetIncome($company, $startDate, $endDate, $unitId);

        $capitalChanges = [];
        $equityTransactions = JournalItem::whereIn('coa_id', $equityAccounts->pluck('id'))
            ->whereHas('journal', function($q) use ($company, $startDate, $endDate, $unitId) {
                $q->where('company_id', $company->id)
                  ->where('is_posted', true)
                  ->whereBetween('date', [$startDate, $endDate]);
                if ($unitId) {
                    $q->where('business_unit_id', $unitId);
                }
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

    public function exportBalanceSheetExcel(Request $request)
    {
        $view = $this->balanceSheet($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;
        $endDate = $data['report_date'] ?? now()->format('Y-m-d');
        $unit = null;
        if (isset($data['unit_id']) && $data['unit_id']) {
            $unit = \App\Models\BusinessUnit::find($data['unit_id']);
        }

        $exportData = [
            'company' => $company,
            'endDate' => $endDate,
            'data' => $data,
            'unit' => $unit,
        ];

        $filename = sprintf('Neraca_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return Excel::download(new FinancialReportExport('reports.excel.balance-sheet', $exportData, 'Neraca'), $filename);
    }

    public function exportBalanceSheetComparativeExcel(Request $request)
    {
        $periodsJson = $request->query('periods');
        $periods = json_decode($periodsJson, true);

        if (!$periods || !is_array($periods) || count($periods) < 2) {
            return back()->withErrors(['periods' => 'At least 2 periods required for comparison']);
        }

        $user = $request->user();
        $company = $user->company;
        $unitId = $request->query('unit_id');

        $comparativeData = $this->buildComparativeData($company, $periods, 'NERACA', $unitId);

        $viewData = [
            'company' => $company,
            'periods' => $periods,
            'data' => $comparativeData,
        ];

        $filename = sprintf('Neraca_Komparatif_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d')
        );

        return Excel::download(new FinancialReportExport('reports.excel.balance-sheet-comparative', $viewData, 'Neraca Komparatif'), $filename);
    }

    public function exportProfitLossExcel(Request $request)
    {
        $view = $this->profitLoss($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;
        $endDate = $data['end_date'] ?? now()->format('Y-m-d');
        $startDate = $data['start_date'] ?? null;
        $unit = null;
        if (isset($data['unit_id']) && $data['unit_id']) {
            $unit = \App\Models\BusinessUnit::find($data['unit_id']);
        }

        $exportData = [
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sections' => $data['sections'] ?? [],
            'totalRevenue' => $data['total_revenue'] ?? 0,
            'totalExpense' => $data['total_expense'] ?? 0,
            'netProfit' => $data['net_profit'] ?? 0,
            'unit' => $unit,
        ];

        $filename = sprintf('LabaRugi_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return Excel::download(new FinancialReportExport('reports.excel.profit-loss', $exportData, 'Laba Rugi'), $filename);
    }

    public function exportProfitLossComparativeExcel(Request $request)
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
        ];

        $filename = sprintf('LabaRugi_Komparatif_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d')
        );

        return Excel::download(new FinancialReportExport('reports.excel.profit-loss-comparative', $viewData, 'Laba Rugi Komparatif'), $filename);
    }

    public function exportCashFlowExcel(Request $request)
    {
        $view = $this->cashFlow($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;
        
        $filename = sprintf('ArusKas_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date'] ?? now()))
        );

        return Excel::download(new FinancialReportExport('reports.excel.cash-flow', $data, 'Arus Kas'), $filename);
    }

    public function exportEquityChangesExcel(Request $request)
    {
        $view = $this->equityChanges($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;
        
        $filename = sprintf('PerubahanEkuitas_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date'] ?? now()))
        );

        return Excel::download(new FinancialReportExport('reports.excel.equity-changes', $data, 'Perubahan Ekuitas'), $filename);
    }

    public function exportTrialBalancePDF(Request $request)
    {
        $view = $this->trialBalance($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;
        
        $endDate = $data['report_date'] ?? now()->format('Y-m-d');

        $viewData = [
            'company' => $company,
            'endDate' => $endDate,
            'accounts' => $data['accounts'] ?? [],
            'total_debit' => $data['total_debit'] ?? 0,
            'total_credit' => $data['total_credit'] ?? 0,
            'is_balanced' => $data['is_balanced'] ?? true,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        $pdf = Pdf::loadView('reports.pdf.trial-balance', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('NeracaSaldo_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return $pdf->download($filename);
    }

    public function exportTrialBalanceExcel(Request $request)
    {
        $view = $this->trialBalance($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;
        $endDate = $data['report_date'] ?? now()->format('Y-m-d');

        $rows = [];
        foreach ($data['accounts'] ?? [] as $acc) {
            $rows[] = [
                'code' => $acc['account_code'],
                'name' => $acc['account_name'],
                'debit' => $acc['debit'],
                'credit' => $acc['credit'],
            ];
        }

        $headers = [
            ['key' => 'code', 'label' => 'Kode Akun', 'align' => 'left'],
            ['key' => 'name', 'label' => 'Nama Akun', 'align' => 'left'],
            ['key' => 'debit', 'label' => 'Debit (Rp)', 'align' => 'right'],
            ['key' => 'credit', 'label' => 'Kredit (Rp)', 'align' => 'right'],
        ];

        $totals = [
            'code' => 'TOTAL',
            'name' => '',
            'debit' => $data['total_debit'] ?? 0,
            'credit' => $data['total_credit'] ?? 0,
        ];

        $exportData = [
            'company' => $company,
            'title' => 'Neraca Saldo',
            'subtitle' => 'Per ' . \App\Helpers\ReportHelper::formatDate($endDate),
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
        ];

        $filename = sprintf('NeracaSaldo_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return Excel::download(new FinancialReportExport('reports.excel.generic', $exportData, 'Neraca Saldo'), $filename);
    }

    public function exportLedgerPDF(Request $request, int $accountId)
    {
        $response = $this->ledger($request, $accountId);
        if ($response instanceof JsonResponse) {
            $responseData = json_decode($response->getContent(), true);
            if (!$responseData['success']) {
                return back()->withErrors(['error' => $responseData['message']]);
            }
            $data = $responseData['data'];
        } else {
            return back()->withErrors(['error' => 'Gagal mengambil data buku besar']);
        }

        $user = $request->user();
        $company = $user->company;

        $viewData = [
            'company' => $company,
            'account' => $data['account'],
            'period' => $data['period'],
            'beginning_balance' => $data['beginning_balance'],
            'transactions' => $data['transactions'],
            'ending_balance' => $data['ending_balance'],
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        $pdf = Pdf::loadView('reports.pdf.ledger', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('BukuBesar_%s_%s_%s.pdf', 
            str_replace(' ', '_', $data['account']['code']), 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date']))
        );

        return $pdf->download($filename);
    }

    public function exportLedgerExcel(Request $request, int $accountId)
    {
        $response = $this->ledger($request, $accountId);
        if ($response instanceof JsonResponse) {
            $responseData = json_decode($response->getContent(), true);
            if (!$responseData['success']) {
                return back()->withErrors(['error' => $responseData['message']]);
            }
            $data = $responseData['data'];
        } else {
            return back()->withErrors(['error' => 'Gagal mengambil data buku besar']);
        }

        $user = $request->user();
        $company = $user->company;

        $rows = [];
        $rows[] = [
            'date' => $data['period']['start_date'],
            'reference' => '-',
            'description' => 'Saldo Awal',
            'debit' => '-',
            'credit' => '-',
            'balance' => $data['beginning_balance'],
            '_style' => 'font-style: italic;',
        ];

        foreach ($data['transactions'] as $tx) {
            $rows[] = [
                'date' => $tx['date'],
                'reference' => $tx['reference'],
                'description' => $tx['description'] . ($tx['memo'] ? ' ('.$tx['memo'].')' : ''),
                'debit' => $tx['debit'] != 0 ? $tx['debit'] : '-',
                'credit' => $tx['credit'] != 0 ? $tx['credit'] : '-',
                'balance' => $tx['balance'],
            ];
        }

        $rows[] = [
            'date' => $data['period']['end_date'],
            'reference' => '-',
            'description' => 'Saldo Akhir',
            'debit' => '-',
            'credit' => '-',
            'balance' => $data['ending_balance'],
            '_style' => 'font-weight: bold; background-color: #cbd5e1;',
        ];

        $headers = [
            ['key' => 'date', 'label' => 'Tanggal', 'align' => 'left'],
            ['key' => 'reference', 'label' => 'Referensi', 'align' => 'left'],
            ['key' => 'description', 'label' => 'Keterangan / Memo', 'align' => 'left'],
            ['key' => 'debit', 'label' => 'Debit (Rp)', 'align' => 'right'],
            ['key' => 'credit', 'label' => 'Kredit (Rp)', 'align' => 'right'],
            ['key' => 'balance', 'label' => 'Saldo (Rp)', 'align' => 'right'],
        ];

        $exportData = [
            'company' => $company,
            'title' => 'Buku Besar - ' . $data['account']['code'] . ' ' . $data['account']['name'],
            'subtitle' => 'Periode: ' . \App\Helpers\ReportHelper::formatDate($data['period']['start_date']) . ' s/d ' . \App\Helpers\ReportHelper::formatDate($data['period']['end_date']),
            'headers' => $headers,
            'rows' => $rows,
            'totals' => null,
        ];

        $filename = sprintf('BukuBesar_%s_%s_%s.xlsx', 
            str_replace(' ', '_', $data['account']['code']), 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date']))
        );

        return Excel::download(new FinancialReportExport('reports.excel.generic', $exportData, 'Buku Besar'), $filename);
    }

    public function exportJournalListPDF(Request $request)
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

        $journals = $query->get();

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($journals as $journal) {
            foreach ($journal->items as $item) {
                $totalDebit += $item->debit;
                $totalCredit += $item->credit;
            }
        }

        $viewData = [
            'company' => $company,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'journals' => $journals,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ];

        $pdf = Pdf::loadView('reports.pdf.journal-list', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('LaporanJurnal_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return $pdf->download($filename);
    }

    public function exportJournalListExcel(Request $request)
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

        $journals = $query->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($journals as $journal) {
            $rows[] = [
                'date' => $journal->date->format('Y-m-d'),
                'reference' => $journal->reference,
                'description' => $journal->description . ($journal->businessUnit ? ' (Unit: ' . $journal->businessUnit->name . ')' : ''),
                'debit' => '',
                'credit' => '',
                '_style' => 'font-weight: bold; background-color: #f3f4f6;',
            ];

            foreach ($journal->items as $item) {
                $rows[] = [
                    'date' => '',
                    'reference' => '',
                    'description' => ($item->credit > 0 ? '     ' : '') . $item->account->code . ' - ' . $item->account->name . ($item->memo ? ' (' . $item->memo . ')' : ''),
                    'debit' => $item->debit != 0 ? $item->debit : '-',
                    'credit' => $item->credit != 0 ? $item->credit : '-',
                ];
                $totalDebit += $item->debit;
                $totalCredit += $item->credit;
            }
        }

        $headers = [
            ['key' => 'date', 'label' => 'Tanggal', 'align' => 'left'],
            ['key' => 'reference', 'label' => 'No. Jurnal', 'align' => 'left'],
            ['key' => 'description', 'label' => 'Akun & Keterangan', 'align' => 'left'],
            ['key' => 'debit', 'label' => 'Debit (Rp)', 'align' => 'right'],
            ['key' => 'credit', 'label' => 'Kredit (Rp)', 'align' => 'right'],
        ];

        $totals = [
            'date' => 'TOTAL',
            'reference' => '',
            'description' => '',
            'debit' => $totalDebit,
            'credit' => $totalCredit,
        ];

        $exportData = [
            'company' => $company,
            'title' => 'Laporan Jurnal',
            'subtitle' => 'Periode: ' . \App\Helpers\ReportHelper::formatDate($startDate) . ' s/d ' . \App\Helpers\ReportHelper::formatDate($endDate),
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
        ];

        $filename = sprintf('LaporanJurnal_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($endDate))
        );

        return Excel::download(new FinancialReportExport('reports.excel.generic', $exportData, 'Laporan Jurnal'), $filename);
    }

    public function exportSalesReportPDF(Request $request)
    {
        $view = $this->salesReport($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;

        $pdf = Pdf::loadView('reports.pdf.sales', [
            'company' => $company,
            'period' => $data['period'],
            'invoices' => $data['invoices'],
            'summary' => $data['summary'],
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('LaporanPenjualan_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date']))
        );

        return $pdf->download($filename);
    }

    public function exportSalesReportExcel(Request $request)
    {
        $view = $this->salesReport($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;

        $rows = [];
        foreach ($data['invoices'] as $inv) {
            $rows[] = [
                'date' => $inv->date->format('Y-m-d'),
                'number' => $inv->number,
                'contact' => $inv->contact ? $inv->contact->name : '-',
                'tax' => $inv->tax,
                'total' => $inv->total,
            ];
        }

        $headers = [
            ['key' => 'date', 'label' => 'Tanggal', 'align' => 'left'],
            ['key' => 'number', 'label' => 'No. Transaksi', 'align' => 'left'],
            ['key' => 'contact', 'label' => 'Pelanggan', 'align' => 'left'],
            ['key' => 'tax', 'label' => 'Pajak (Rp)', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total (Rp)', 'align' => 'right'],
        ];

        $totals = [
            'date' => 'TOTAL',
            'number' => '',
            'contact' => '',
            'tax' => $data['summary']['total_tax'],
            'total' => $data['summary']['total_sales'],
        ];

        $exportData = [
            'company' => $company,
            'title' => 'Laporan Penjualan',
            'subtitle' => 'Periode: ' . \App\Helpers\ReportHelper::formatDate($data['period']['start_date']) . ' s/d ' . \App\Helpers\ReportHelper::formatDate($data['period']['end_date']),
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
        ];

        $filename = sprintf('LaporanPenjualan_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date']))
        );

        return Excel::download(new FinancialReportExport('reports.excel.generic', $exportData, 'Laporan Penjualan'), $filename);
    }

    public function exportPurchaseReportPDF(Request $request)
    {
        $view = $this->purchaseReport($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;

        $pdf = Pdf::loadView('reports.pdf.purchases', [
            'company' => $company,
            'period' => $data['period'],
            'invoices' => $data['invoices'],
            'summary' => $data['summary'],
            'timestamp' => now()->format('d M Y H:i'),
            'city' => ReportHelper::extractCity($company->address ?? ''),
            'date' => now()->format('Y-m-d')
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = sprintf('LaporanPembelian_%s_%s.pdf', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date']))
        );

        return $pdf->download($filename);
    }

    public function exportPurchaseReportExcel(Request $request)
    {
        $view = $this->purchaseReport($request);
        $data = $view->getData();
        $user = $request->user();
        $company = $user->company;

        $rows = [];
        foreach ($data['invoices'] as $inv) {
            $rows[] = [
                'date' => $inv->date->format('Y-m-d'),
                'number' => $inv->number,
                'contact' => $inv->contact ? $inv->contact->name : '-',
                'tax' => $inv->tax,
                'total' => $inv->total,
            ];
        }

        $headers = [
            ['key' => 'date', 'label' => 'Tanggal', 'align' => 'left'],
            ['key' => 'number', 'label' => 'No. Transaksi', 'align' => 'left'],
            ['key' => 'contact', 'label' => 'Pemasok', 'align' => 'left'],
            ['key' => 'tax', 'label' => 'Pajak (Rp)', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total (Rp)', 'align' => 'right'],
        ];

        $totals = [
            'date' => 'TOTAL',
            'number' => '',
            'contact' => '',
            'tax' => $data['summary']['total_tax'],
            'total' => $data['summary']['total_purchases'],
        ];

        $exportData = [
            'company' => $company,
            'title' => 'Laporan Pembelian',
            'subtitle' => 'Periode: ' . \App\Helpers\ReportHelper::formatDate($data['period']['start_date']) . ' s/d ' . \App\Helpers\ReportHelper::formatDate($data['period']['end_date']),
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
        ];

        $filename = sprintf('LaporanPembelian_%s_%s.xlsx', 
            str_replace(' ', '_', $company->name), 
            date('Y-m-d', strtotime($data['period']['end_date']))
        );

        return Excel::download(new FinancialReportExport('reports.excel.generic', $exportData, 'Laporan Pembelian'), $filename);
    }
}


