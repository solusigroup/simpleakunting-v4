<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use App\Models\WasteCollector;
use App\Models\WasteDeposit;
use App\Models\WasteSale;
use App\Models\WasteWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WasteCollectorsExport;

class WasteReportController extends Controller
{
    public function index()
    {
        return view('waste.reports.index');
    }

    public function stock(Request $request)
    {
        $categories = WasteCategory::all()->map(function($cat) {
            $deposited = WasteDeposit::where('waste_category_id', $cat->id)->sum('weight');
            $sold = WasteSale::where('waste_category_id', $cat->id)->sum('weight');
            $cat->stock = $deposited - $sold;
            $cat->stock_value = $cat->stock * $cat->buy_price;
            return $cat;
        });

        if ($request->has('export')) {
            // Placeholder for export logic
        }

        return view('waste.reports.stock', compact('categories'));
    }

    public function collectors(Request $request)
    {
        if ($request->has('export')) {
            return Excel::download(new WasteCollectorsExport, 'saldo_nasabah_bank_sampah.xlsx');
        }

        $collectors = WasteCollector::orderBy('balance', 'desc')->get();
        return view('waste.reports.collectors', compact('collectors'));
    }

    public function ledger(Request $request)
    {
        $request->validate([
            'waste_collector_id' => 'nullable|exists:waste_collectors,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $collector = null;
        $transactions = collect();

        if ($request->waste_collector_id) {
            $collector = WasteCollector::findOrFail($request->waste_collector_id);
            
            $deposits = WasteDeposit::where('waste_collector_id', $collector->id)
                ->when($request->start_date, fn($q) => $q->where('date', '>=', $request->start_date))
                ->when($request->end_date, fn($q) => $q->where('date', '<=', $request->end_date))
                ->get()
                ->map(function($item) {
                    $item->type = 'Deposit';
                    $item->amount = $item->total_amount;
                    return $item;
                });

            $withdrawals = WasteWithdrawal::where('waste_collector_id', $collector->id)
                ->when($request->start_date, fn($q) => $q->where('date', '>=', $request->start_date))
                ->when($request->end_date, fn($q) => $q->where('date', '<=', $request->end_date))
                ->get()
                ->map(function($item) {
                    $item->type = 'Withdrawal';
                    $item->amount = -$item->amount;
                    return $item;
                });

            $transactions = $deposits->concat($withdrawals)->sortBy('date');
        }

        $all_collectors = WasteCollector::all();

        return view('waste.reports.ledger', compact('collector', 'transactions', 'all_collectors'));
    }
}
