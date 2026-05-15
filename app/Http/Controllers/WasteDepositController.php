<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\WasteCollector;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteDepositController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteDeposit::with(['collector', 'category'])->latest();

        if ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }
        if ($request->filled('collector_id')) {
            $query->where('waste_collector_id', $request->collector_id);
        }

        $deposits = $query->paginate(20);
        $collectors = WasteCollector::all();
        
        return view('waste.deposits.index', compact('deposits', 'collectors'));
    }

    public function create()
    {
        $collectors = WasteCollector::all();
        $categories = WasteCategory::where('is_active', true)->get();
        return view('waste.deposits.create', compact('collectors', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waste_collector_id' => 'required|exists:waste_collectors,id',
            'waste_category_id' => 'required|exists:waste_categories,id',
            'weight' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $collector = WasteCollector::findOrFail($validated['waste_collector_id']);
        $category = WasteCategory::findOrFail($validated['waste_category_id']);
        $company = auth()->user()->company;

        if (!$company->waste_inventory_account_id || !$company->waste_liability_account_id) {
            return back()->with('error', 'Akun akuntansi untuk Bank Sampah belum dikonfigurasi di Pengaturan Perusahaan.');
        }

        $total_amount = $validated['weight'] * $category->buy_price;
        // Calculate next sequence number for the day
        $lastDeposit = WasteDeposit::whereDate('date', $validated['date'])->orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastDeposit) {
            preg_match('/(\d+)$/', $lastDeposit->deposit_number, $matches);
            $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        $deposit_number = 'DEP-' . \Carbon\Carbon::parse($validated['date'])->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        try {
            DB::beginTransaction();

            // Create Journal
            $journal = Journal::create([
                'company_id' => $company->id,
                'date' => $validated['date'],
                'reference' => $deposit_number,
                'description' => "Setoran sampah: {$category->name} ({$validated['weight']} {$category->unit}) dari {$collector->name}",
                'source' => 'waste_bank',
                'is_posted' => true, // Auto post for waste bank
            ]);

            // Debit: Waste Inventory
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->waste_inventory_account_id,
                'debit' => $total_amount,
                'credit' => 0,
                'memo' => "Persediaan {$category->name}",
            ]);

            // Credit: Waste Liability (Utang Tabungan)
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->waste_liability_account_id,
                'debit' => 0,
                'credit' => $total_amount,
                'memo' => "Tabungan Nasabah: {$collector->name}",
            ]);

            // Create Deposit Record
            WasteDeposit::create([
                'deposit_number' => $deposit_number,
                'waste_collector_id' => $collector->id,
                'waste_category_id' => $category->id,
                'weight' => $validated['weight'],
                'price_at_time' => $category->buy_price,
                'total_amount' => $total_amount,
                'date' => $validated['date'],
                'journal_id' => $journal->id,
                'note' => $validated['note'],
            ]);

            // Update Collector Balance
            $collector->increment('balance', $total_amount);

            DB::commit();
            return redirect()->route('waste.deposits.index')->with('success', 'Setoran sampah berhasil dicatat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat setoran: ' . $e->getMessage());
        }
    }

    public function show(WasteDeposit $deposit)
    {
        $deposit->load(['collector', 'category', 'journal.items.account']);
        return view('waste.deposits.show', compact('deposit'));
    }
}
