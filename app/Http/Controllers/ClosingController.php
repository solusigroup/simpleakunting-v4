<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosingController extends Controller
{
    /**
     * Show closing page.
     */
    public function index(Request $request)
    {
        return view('journals.closing');
    }

    /**
     * POST /journals/closing/preview
     * Preview closing journal entries.
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        // Only Manajer or Administrator can run closing
        if (!$user->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Manajer atau Administrator yang dapat menjalankan tutup buku.',
            ], 403);
        }

        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $request->year;
        $month = $request->month;

        // Calculate period dates
        if ($month) {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = sprintf('%04d-%02d-%02d', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
            $periodLabel = date('F Y', strtotime($startDate));
        } else {
            $startDate = sprintf('%04d-01-01', $year);
            $endDate = sprintf('%04d-12-31', $year);
            $periodLabel = "Tahun $year";
        }

        // Get Revenue accounts with balances
        $revenueAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Revenue')
            ->where('is_parent', false)
            ->get()
            ->map(function ($acc) use ($startDate, $endDate) {
                $balance = $acc->getBalance($startDate, $endDate);
                return [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($a) => $a['balance'] != 0)
            ->values();

        // Get Expense accounts with balances
        $expenseAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Expense')
            ->where('is_parent', false)
            ->get()
            ->map(function ($acc) use ($startDate, $endDate) {
                $balance = $acc->getBalance($startDate, $endDate);
                return [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($a) => $a['balance'] != 0)
            ->values();

        $totalRevenue = $revenueAccounts->sum('balance');
        $totalExpense = $expenseAccounts->sum('balance');
        $netIncome = $totalRevenue - $totalExpense;

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $periodLabel,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'revenue_accounts' => $revenueAccounts,
                'expense_accounts' => $expenseAccounts,
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_income' => $netIncome,
            ],
        ]);
    }

    /**
     * POST /journals/closing/execute
     * Execute closing journal entries.
     */
    public function execute(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        // Only Manajer or Administrator can run closing
        if (!$user->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Manajer atau Administrator yang dapat menjalankan tutup buku.',
            ], 403);
        }

        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $request->year;
        $month = $request->month;

        // Calculate period dates
        if ($month) {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = sprintf('%04d-%02d-%02d', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
            $periodLabel = date('F Y', strtotime($startDate));
            $reference = 'CLO-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT);
        } else {
            $startDate = sprintf('%04d-01-01', $year);
            $endDate = sprintf('%04d-12-31', $year);
            $periodLabel = "Tahun $year";
            $reference = 'CLO-' . $year;
        }

        try {
            DB::beginTransaction();

            // Get or create Income Summary account (Ikhtisar Laba-Rugi)
            $incomeSummary = ChartOfAccount::where('company_id', $company->id)
                ->where('account_category', 'equity_other')
                ->where('name', 'like', '%Ikhtisar%')
                ->first();

            if (!$incomeSummary) {
                // Create Ikhtisar Laba-Rugi account
                $incomeSummary = ChartOfAccount::create([
                    'company_id' => $company->id,
                    'code' => '3.9.01',
                    'name' => 'Ikhtisar Laba-Rugi',
                    'type' => 'Equity',
                    'report_type' => 'NERACA',
                    'normal_balance' => 'KREDIT',
                    'is_parent' => false,
                    'level' => 3,
                    'account_category' => 'equity_other',
                    'is_active' => true,
                    'is_system' => true,
                ]);
            }

            // Get or create Retained Earnings account (Laba Ditahan)
            $retainedEarnings = ChartOfAccount::where('company_id', $company->id)
                ->where('account_category', 'equity_retained')
                ->first();

            if (!$retainedEarnings) {
                // Create Laba Ditahan account
                $retainedEarnings = ChartOfAccount::create([
                    'company_id' => $company->id,
                    'code' => '3.2.01',
                    'name' => 'Laba Ditahan',
                    'type' => 'Equity',
                    'report_type' => 'NERACA',
                    'normal_balance' => 'KREDIT',
                    'is_parent' => false,
                    'level' => 3,
                    'account_category' => 'equity_retained',
                    'is_active' => true,
                    'is_system' => true,
                ]);
            }

            // Get Revenue accounts with balances
            $revenueAccounts = ChartOfAccount::where('company_id', $company->id)
                ->where('type', 'Revenue')
                ->where('is_parent', false)
                ->get();

            // Get Expense accounts with balances
            $expenseAccounts = ChartOfAccount::where('company_id', $company->id)
                ->where('type', 'Expense')
                ->where('is_parent', false)
                ->get();

            $journalItems = [];
            $totalRevenue = 0;
            $totalExpense = 0;

            // Close Revenue accounts (Debit Revenue, Credit Ikhtisar Laba-Rugi)
            foreach ($revenueAccounts as $acc) {
                $balance = $acc->getBalance($startDate, $endDate);
                if ($balance > 0) {
                    $journalItems[] = [
                        'coa_id' => $acc->id,
                        'debit' => $balance,
                        'credit' => 0,
                        'description' => "Menutup {$acc->name}",
                    ];
                    $totalRevenue += $balance;
                }
            }

            // Close Expense accounts (Debit Ikhtisar Laba-Rugi, Credit Expense)
            foreach ($expenseAccounts as $acc) {
                $balance = $acc->getBalance($startDate, $endDate);
                if ($balance > 0) {
                    $journalItems[] = [
                        'coa_id' => $acc->id,
                        'debit' => 0,
                        'credit' => $balance,
                        'description' => "Menutup {$acc->name}",
                    ];
                    $totalExpense += $balance;
                }
            }

            if (empty($journalItems) || ($totalRevenue == 0 && $totalExpense == 0)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada saldo pendapatan atau beban untuk ditutup.',
                ], 422);
            }

            // Add Ikhtisar Laba-Rugi entries
            if ($totalRevenue > 0) {
                $journalItems[] = [
                    'coa_id' => $incomeSummary->id,
                    'debit' => 0,
                    'credit' => $totalRevenue,
                    'description' => "Ikhtisar Laba-Rugi dari Pendapatan",
                ];
            }
            if ($totalExpense > 0) {
                $journalItems[] = [
                    'coa_id' => $incomeSummary->id,
                    'debit' => $totalExpense,
                    'credit' => 0,
                    'description' => "Ikhtisar Laba-Rugi dari Beban",
                ];
            }

            // Transfer net income to Retained Earnings
            $netIncome = $totalRevenue - $totalExpense;
            if ($netIncome > 0) {
                // Profit: Debit Ikhtisar, Credit Laba Ditahan
                $journalItems[] = [
                    'coa_id' => $incomeSummary->id,
                    'debit' => $netIncome,
                    'credit' => 0,
                    'description' => "Transfer Laba Bersih ke Laba Ditahan",
                ];
                $journalItems[] = [
                    'coa_id' => $retainedEarnings->id,
                    'debit' => 0,
                    'credit' => $netIncome,
                    'description' => "Laba Bersih Periode $periodLabel",
                ];
            } elseif ($netIncome < 0) {
                // Loss: Credit Ikhtisar, Debit Laba Ditahan
                $journalItems[] = [
                    'coa_id' => $incomeSummary->id,
                    'debit' => 0,
                    'credit' => abs($netIncome),
                    'description' => "Transfer Rugi Bersih ke Laba Ditahan",
                ];
                $journalItems[] = [
                    'coa_id' => $retainedEarnings->id,
                    'debit' => abs($netIncome),
                    'credit' => 0,
                    'description' => "Rugi Bersih Periode $periodLabel",
                ];
            }

            // Create journal entry
            $journal = Journal::create([
                'company_id' => $company->id,
                'date' => $endDate,
                'reference' => $reference,
                'description' => "Jurnal Penutup - $periodLabel",
                'source' => 'closing',
                'is_posted' => true,
            ]);

            foreach ($journalItems as $item) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['coa_id'],
                    'debit' => $item['debit'],
                    'credit' => $item['credit'],
                    'description' => $item['description'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jurnal penutup berhasil dibuat.',
                'data' => [
                    'journal_id' => $journal->id,
                    'period' => $periodLabel,
                    'total_revenue' => $totalRevenue,
                    'total_expense' => $totalExpense,
                    'net_income' => $netIncome,
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
}
