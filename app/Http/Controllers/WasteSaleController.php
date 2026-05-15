<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Models\WasteSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteSaleController extends Controller
{
    public function index()
    {
        $sales = WasteSale::with('category')->latest()->paginate(10);
        return view('waste.sales.index', compact('sales'));
    }

    public function create()
    {
        $categories = WasteCategory::where('is_active', true)->get()->map(function($cat) {
            $deposited = WasteDeposit::where('waste_category_id', $cat->id)->sum('weight');
            $sold = WasteSale::where('waste_category_id', $cat->id)->sum('weight');
            $cat->stock = $deposited - $sold;
            return $cat;
        })->filter(fn($cat) => $cat->stock > 0);

        $accounts = \App\Models\ChartOfAccount::where('is_parent', false)->get();
        
        return view('waste.sales.create', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waste_category_id' => 'required|exists:waste_categories,id',
            'weight' => 'required|numeric|min:0.01',
            'price_at_time' => 'required|numeric|min:0',
            'date' => 'required|date',
            'buyer_name' => 'nullable|string|max:255',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'note' => 'nullable|string',
        ]);

        $category = WasteCategory::findOrFail($validated['waste_category_id']);
        $company = auth()->user()->company;

        if (!$company->waste_inventory_account_id || !$company->waste_revenue_account_id || !$company->waste_cogs_account_id) {
            return back()->with('error', 'Konfigurasi akun Bank Sampah (Persediaan, Pendapatan, HPP) belum lengkap.');
        }

        // Check stock
        $deposited = WasteDeposit::where('waste_category_id', $category->id)->sum('weight');
        $sold = WasteSale::where('waste_category_id', $category->id)->sum('weight');
        $current_stock = $deposited - $sold;

        if ($validated['weight'] > $current_stock) {
            return back()->with('error', "Stok tidak mencukupi. Stok saat ini: {$current_stock} {$category->unit}");
        }

        $total_revenue = $validated['weight'] * $validated['price_at_time'];
        $total_cost = $validated['weight'] * $category->buy_price; // Assuming FIFO/Average is handled by using current buy_price or we can use the actual cost from deposits
        
        // Calculate next sequence number for the day
        $lastSale = WasteSale::whereDate('date', $validated['date'])->orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastSale) {
            preg_match('/(\d+)$/', $lastSale->sale_number, $matches);
            $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        $sale_number = 'SLS-' . \Carbon\Carbon::parse($validated['date'])->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        try {
            DB::beginTransaction();

            // Create Journal
            $journal = Journal::create([
                'company_id' => $company->id,
                'date' => $validated['date'],
                'reference' => $sale_number,
                'description' => "Penjualan sampah: {$category->name} ke {$validated['buyer_name']}",
                'source' => 'waste_bank',
                'is_posted' => true,
            ]);

            // 1. Revenue Entry
            // Debit: Kas/Bank
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $validated['cash_account_id'],
                'debit' => $total_revenue,
                'credit' => 0,
                'memo' => "Penerimaan penjualan {$category->name}",
            ]);

            // Credit: Revenue
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->waste_revenue_account_id,
                'debit' => 0,
                'credit' => $total_revenue,
                'memo' => "Pendapatan Penjualan Sampah",
            ]);

            // 2. Inventory & COGS Entry
            // Debit: COGS
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->waste_cogs_account_id,
                'debit' => $total_cost,
                'credit' => 0,
                'memo' => "HPP {$category->name}",
            ]);

            // Credit: Inventory
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $company->waste_inventory_account_id,
                'debit' => 0,
                'credit' => $total_cost,
                'memo' => "Pengurangan stok {$category->name}",
            ]);

            // Create Sale Record
            WasteSale::create([
                'sale_number' => $sale_number,
                'waste_category_id' => $category->id,
                'weight' => $validated['weight'],
                'price_at_time' => $validated['price_at_time'],
                'total_amount' => $total_revenue,
                'date' => $validated['date'],
                'buyer_name' => $validated['buyer_name'],
                'journal_id' => $journal->id,
                'note' => $validated['note'],
            ]);

            DB::commit();
            return redirect()->route('waste.sales.index')->with('success', 'Penjualan sampah berhasil dicatat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat penjualan: ' . $e->getMessage());
        }
    }
}
