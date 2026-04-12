<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\InvestorSharing;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestorSharingController extends Controller
{
    /**
     * Display the sharing calculator.
     */
    public function index(Request $request)
    {
        $company = auth()->user()->company;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $unitId = $request->get('unit_id');

        $investors = Investor::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $calculations = [];
        $grossProfit = $this->calculateGrossProfit($company, $startDate, $endDate, $unitId);
        $netProfit = $this->calculateNetProfit($company, $startDate, $endDate, $unitId);

        foreach ($investors as $investor) {
            $basisAmount = ($investor->basis === 'GROSS_PROFIT') ? $grossProfit : $netProfit;
            $shareAmount = ($basisAmount * $investor->share_percentage) / 100;

            $calculations[] = [
                'investor' => $investor,
                'basis_amount' => $basisAmount,
                'share_amount' => $shareAmount,
            ];
        }

        return view('reports.investor-sharing', compact(
            'calculations',
            'startDate',
            'endDate',
            'grossProfit',
            'netProfit',
            'company'
        ));
    }

    /**
     * Post the sharing data to journal.
     */
    public function post(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'investors' => 'required|array',
            'investors.*.id' => 'required|exists:investors,id',
            'investors.*.amount' => 'required|numeric|min:0',
        ]);

        $company = auth()->user()->company;

        // Check if COA settings are configured
        if (!$company->investor_sharing_debit_coa_id || !$company->investor_sharing_credit_coa_id) {
            return back()->with('error', 'Silakan atur Akun Debet dan Akun Kredit untuk Bagi Hasil di Pengaturan Perusahaan terlebih dahulu.');
        }

        return DB::transaction(function () use ($request, $company) {
            $totalAmount = 0;
            foreach ($request->investors as $invData) {
                $totalAmount += $invData['amount'];
            }

            if ($totalAmount <= 0) {
                return back()->with('error', 'Total bagi hasil harus lebih dari 0.');
            }

            // Create Journal
            $journal = Journal::create([
                'company_id' => $company->id,
                'date' => now(),
                'reference' => 'BS-' . now()->format('YmdHis'),
                'description' => 'Bagi Hasil Investor Periode ' . $request->start_date . ' s/d ' . $request->end_date,
                'source' => 'manual',
                'is_posted' => true,
            ]);

            // Debit Entry (Expense/Equity)
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->investor_sharing_debit_coa_id,
                'debit' => $totalAmount,
                'credit' => 0,
                'memo' => 'Alokasi Bagi Hasil Investor',
            ]);

            // Credit Entry (Liability/Payable)
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->investor_sharing_credit_coa_id,
                'debit' => 0,
                'credit' => $totalAmount,
                'memo' => 'Hutang Bagi Hasil Investor',
            ]);

            // Log individual records
            foreach ($request->investors as $invData) {
                $investor = Investor::find($invData['id']);
                InvestorSharing::create([
                    'investor_id' => $investor->id,
                    'journal_id' => $journal->id,
                    'amount' => $invData['amount'],
                    'period_start' => $request->start_date,
                    'period_end' => $request->end_date,
                    'basis_amount' => $invData['basis_amount'] ?? 0,
                    'note' => 'Bagi Hasil ' . $investor->share_percentage . '% dari ' . $investor->basis,
                ]);
            }

            return redirect()->route('reports.investor-sharing')->with('success', 'Bagi hasil berhasil di-posting ke Jurnal Umum.');
        });
    }

    private function calculateGrossProfit($company, $startDate, $endDate, $unitId)
    {
        $revenue = $this->getSumBalance($company, 'Revenue', $startDate, $endDate, $unitId);
        // HPP usually starts with 5xxx in your seeder
        $cogs = $this->getSumBalanceByCode($company, '5', $startDate, $endDate, $unitId);

        return $revenue - $cogs;
    }

    private function calculateNetProfit($company, $startDate, $endDate, $unitId)
    {
        $revenue = $this->getSumBalance($company, 'Revenue', $startDate, $endDate, $unitId);
        $expenses = $this->getSumBalance($company, 'Expense', $startDate, $endDate, $unitId);

        return $revenue - $expenses;
    }

    private function getSumBalance($company, $type, $startDate, $endDate, $unitId)
    {
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', $type)
            ->where('is_parent', false)
            ->get();

        $total = 0;
        foreach ($accounts as $account) {
            $total += abs($this->getAccountBalance($account, $startDate, $endDate, $unitId));
        }
        return $total;
    }

    private function getSumBalanceByCode($company, $codePrefix, $startDate, $endDate, $unitId)
    {
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('code', 'like', $codePrefix . '%')
            ->where('is_parent', false)
            ->get();

        $total = 0;
        foreach ($accounts as $account) {
            $total += abs($this->getAccountBalance($account, $startDate, $endDate, $unitId));
        }
        return $total;
    }

    private function getAccountBalance($account, $startDate, $endDate, $unitId)
    {
        $query = JournalItem::where('coa_id', $account->id)
            ->whereHas('journal', function ($q) use ($account, $startDate, $endDate, $unitId) {
                $q->where('company_id', $account->company_id)
                    ->where('is_posted', true)
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
                
                if ($unitId) {
                    $q->where('business_unit_id', $unitId);
                }
            });

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        if ($account->normal_balance === 'DEBIT') {
            return $debit - $credit;
        }
        return $credit - $debit;
    }
}
