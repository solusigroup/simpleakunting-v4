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

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['required', 'date'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
        ]);

        $endDate = $request->end_date;
        $unitId = $request->unit_id;

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

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $unitId = $request->unit_id;

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

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['required', 'date'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
        ]);

        $endDate = $request->end_date;
        $unitId = $request->unit_id;

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
     * GET /reports/ledger/{account_id}
     * Buku Besar Per Akun.
     */
    public function ledger(Request $request, int $accountId): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

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
}
