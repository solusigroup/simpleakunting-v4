<?php

namespace App\Traits;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;

/**
 * Trait ValidatesCashBalance
 * 
 * Provides methods to validate that cash account balance never goes negative.
 * BUSINESS RULE: Kas dan Setara Kas (code 1.1.1) must always have balance >= 0
 */
trait ValidatesCashBalance
{
    /**
     * Get the cash account for a company.
     */
    protected function getCashAccount($company): ?ChartOfAccount
    {
        return ChartOfAccount::where('company_id', $company->id)
            ->where('code', '1.1.1')
            ->first();
    }

    /**
     * Calculate current cash balance.
     */
    protected function getCurrentCashBalance($company): float
    {
        $cashAccount = $this->getCashAccount($company);
        
        if (!$cashAccount) {
            return 0;
        }

        // Sum all debits and credits for cash account from posted journals
        $items = JournalItem::where('coa_id', $cashAccount->id)
            ->whereHas('journal', function ($q) use ($company) {
                $q->where('company_id', $company->id)
                    ->where('is_posted', true);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = $items->total_debit ?? 0;
        $totalCredit = $items->total_credit ?? 0;

        // Cash is a DEBIT account: Balance = Debit - Credit
        return $totalDebit - $totalCredit;
    }

    /**
     * Calculate the impact of journal lines on cash balance.
     * Returns the NET impact (positive = cash increases, negative = cash decreases)
     */
    protected function calculateCashImpact($company, array $lines): float
    {
        $cashAccount = $this->getCashAccount($company);
        
        if (!$cashAccount) {
            return 0;
        }

        $cashDebit = 0;
        $cashCredit = 0;

        foreach ($lines as $line) {
            if ($line['account_id'] == $cashAccount->id) {
                $cashDebit += $line['debit'] ?? 0;
                $cashCredit += $line['credit'] ?? 0;
            }
        }

        // Net impact on cash (DEBIT increases, CREDIT decreases)
        return $cashDebit - $cashCredit;
    }

    /**
     * Validate that a transaction won't cause negative cash balance.
     * Returns array: ['valid' => bool, 'current_balance' => float, 'new_balance' => float, 'impact' => float]
     */
    protected function validateCashBalance($company, array $lines): array
    {
        $currentBalance = $this->getCurrentCashBalance($company);
        $impact = $this->calculateCashImpact($company, $lines);
        $newBalance = $currentBalance + $impact;

        return [
            'valid' => $newBalance >= 0,
            'current_balance' => $currentBalance,
            'new_balance' => $newBalance,
            'impact' => $impact,
            'required_amount' => $impact < 0 ? abs($impact) : 0,
        ];
    }
}
