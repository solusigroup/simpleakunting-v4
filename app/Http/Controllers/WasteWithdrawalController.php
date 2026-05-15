<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\WasteCollector;
use App\Models\WasteWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteWithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = WasteWithdrawal::with('collector')->latest()->paginate(10);
        return view('waste.withdrawals.index', compact('withdrawals'));
    }

    public function create()
    {
        $collectors = WasteCollector::where('balance', '>', 0)->get();
        $accounts = \App\Models\ChartOfAccount::where('is_parent', false)->get();
        return view('waste.withdrawals.create', compact('collectors', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waste_collector_id' => 'required|exists:waste_collectors,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'note' => 'nullable|string',
        ]);

        $collector = WasteCollector::findOrFail($validated['waste_collector_id']);
        $company = auth()->user()->company;

        if (!$company->waste_liability_account_id) {
            return back()->with('error', 'Akun Utang Tabungan Sampah belum dikonfigurasi.');
        }

        if ($validated['amount'] > $collector->balance) {
            return back()->with('error', "Saldo tidak mencukupi. Saldo saat ini: Rp " . number_format($collector->balance, 0, ',', '.'));
        }

        // Calculate next sequence number for the day
        $lastWithdrawal = WasteWithdrawal::whereDate('date', $validated['date'])->orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastWithdrawal) {
            preg_match('/(\d+)$/', $lastWithdrawal->withdrawal_number, $matches);
            $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        $withdrawal_number = 'WTH-' . \Carbon\Carbon::parse($validated['date'])->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        try {
            $withdrawal = DB::transaction(function () use ($validated, $collector, $company, $withdrawal_number) {
                // Create Journal
                $journal = Journal::create([
                    'company_id' => $company->id,
                    'date' => $validated['date'],
                    'reference' => $withdrawal_number,
                    'description' => "Penarikan tabungan sampah: {$collector->name}",
                    'source' => 'waste_bank',
                    'is_posted' => true,
                ]);

                // Debit: Waste Liability
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $company->waste_liability_account_id,
                    'debit' => $validated['amount'],
                    'credit' => 0,
                    'memo' => "Penarikan Tabungan: {$collector->name}",
                ]);

                // Credit: Kas/Bank
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $validated['cash_account_id'],
                    'debit' => 0,
                    'credit' => $validated['amount'],
                    'memo' => "Pembayaran tunai ke nasabah",
                ]);

                // Create Withdrawal Record
                $withdrawal = WasteWithdrawal::create([
                    'withdrawal_number' => $withdrawal_number,
                    'waste_collector_id' => $collector->id,
                    'amount' => $validated['amount'],
                    'date' => $validated['date'],
                    'journal_id' => $journal->id,
                    'note' => $validated['note'],
                ]);

                // Update Collector Balance
                $collector->decrement('balance', $validated['amount']);

                return $withdrawal;
            });

            return redirect()->route('waste.withdrawals.receipt', $withdrawal)
                ->with('success', 'Penarikan berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat penarikan: ' . $e->getMessage());
        }
    }

    public function receipt(WasteWithdrawal $withdrawal)
    {
        $withdrawal->load('collector');
        return view('waste.withdrawals.receipt', compact('withdrawal'));
    }
}
