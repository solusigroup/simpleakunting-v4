<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Traits\ValidatesCashBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashController extends Controller
{
    use ValidatesCashBalance;
    /**
     * POST /cash/spend
     * Uang Keluar (Expense) - Pengeluaran langsung dari Kas/Bank.
     * 
     * Journal:
     * - Debit: Akun Biaya (to_account_id)
     * - Kredit: Akun Kas/Bank (from_account_id)
     */
    public function spend(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membuat transaksi.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'from_account_id' => ['required', 'exists:chart_of_accounts,id'], // Kas/Bank
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.to_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.memo' => ['nullable', 'string'],
        ]);

        // =============================================
        // CASH BALANCE VALIDATION
        // =============================================
        $totalAmount = collect($request->items)->sum('amount');
        
        // Build simulated journal lines for validation
        $simulatedLines = [
            ['account_id' => $request->from_account_id, 'debit' => 0, 'credit' => $totalAmount],
        ];
        
        $cashValidation = $this->validateCashBalance($company, $simulatedLines);
        
        if (!$cashValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ditolak: Saldo kas tidak mencukupi',
                'errors' => [
                    'current_cash_balance' => $cashValidation['current_balance'],
                    'transaction_amount' => $totalAmount,
                    'new_balance' => $cashValidation['new_balance'],
                ],
                'user_message' => sprintf(
                    "Saldo kas saat ini: Rp %s. Pengeluaran ini memerlukan: Rp %s.",
                    number_format($cashValidation['current_balance'], 0, ',', '.'),
                    number_format($totalAmount, 0, ',', '.')
                ),
            ], 422);
        }

        $journal = DB::transaction(function () use ($request, $company) {
            $totalAmount = collect($request->items)->sum('amount');

            // Generate reference
            $reference = 'CBO-' . now()->format('YmdHis');

            // Create Journal
            $journal = Journal::create([
                'company_id' => $company->id,
                'business_unit_id' => $request->unit_id,
                'date' => $request->date,
                'reference' => $reference,
                'description' => $request->description ?? 'Pengeluaran Kas',
                'source' => 'cash_bank',
                'is_posted' => true,
            ]);

            // Debit: Akun Biaya (per item)
            foreach ($request->items as $item) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['to_account_id'],
                    'debit' => $item['amount'],
                    'credit' => 0,
                    'memo' => $item['memo'] ?? null,
                ]);
            }

            // Kredit: Akun Kas/Bank
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $request->from_account_id,
                'debit' => 0,
                'credit' => $totalAmount,
                'memo' => 'Pengeluaran dari Kas/Bank',
            ]);

            return $journal;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran kas berhasil dicatat.',
            'data' => $journal->load('items.account'),
        ], 201);
    }

    /**
     * POST /cash/receive
     * Uang Masuk (Income) - Penerimaan ke Kas/Bank.
     * 
     * Journal:
     * - Debit: Akun Kas/Bank (to_account_id)
     * - Kredit: Akun Pendapatan/Piutang (from_account_id)
     */
    public function receive(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membuat transaksi.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'to_account_id' => ['required', 'exists:chart_of_accounts,id'], // Kas/Bank
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.from_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.memo' => ['nullable', 'string'],
        ]);

        $journal = DB::transaction(function () use ($request, $company) {
            $totalAmount = collect($request->items)->sum('amount');

            // Generate reference
            $reference = 'CBI-' . now()->format('YmdHis');

            // Create Journal
            $journal = Journal::create([
                'company_id' => $company->id,
                'business_unit_id' => $request->unit_id,
                'date' => $request->date,
                'reference' => $reference,
                'description' => $request->description ?? 'Penerimaan Kas',
                'source' => 'cash_bank',
                'is_posted' => true,
            ]);

            // Debit: Akun Kas/Bank
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $request->to_account_id,
                'debit' => $totalAmount,
                'credit' => 0,
                'memo' => 'Penerimaan ke Kas/Bank',
            ]);

            // Kredit: Akun Pendapatan/Piutang (per item)
            foreach ($request->items as $item) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['from_account_id'],
                    'debit' => 0,
                    'credit' => $item['amount'],
                    'memo' => $item['memo'] ?? null,
                ]);
            }

            return $journal;
        });

        return response()->json([
            'success' => true,
            'message' => 'Penerimaan kas berhasil dicatat.',
            'data' => $journal->load('items.account'),
        ], 201);
    }
}
