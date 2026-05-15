<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use App\Models\WasteCollector;
use App\Models\WasteDeposit;
use App\Models\WasteSale;
use App\Models\WasteWithdrawal;
use Illuminate\Http\Request;

class WasteDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_collectors' => WasteCollector::count(),
            'total_balance' => WasteCollector::sum('balance'),
            'total_deposits' => WasteDeposit::count(),
            'total_sales' => WasteSale::count(),
            'total_withdrawals' => WasteWithdrawal::count(),
            'waste_stock' => WasteCategory::all()->map(function($cat) {
                $deposited = WasteDeposit::where('waste_category_id', $cat->id)->sum('weight');
                $sold = WasteSale::where('waste_category_id', $cat->id)->sum('weight');
                $cat->stock = $deposited - $sold;
                return $cat;
            })->filter(fn($cat) => $cat->stock > 0),
        ];

        $recent_deposits = WasteDeposit::with(['collector', 'category'])->latest()->take(5)->get();
        $recent_sales = WasteSale::with('category')->latest()->take(5)->get();

        return view('waste.index', compact('stats', 'recent_deposits', 'recent_sales'));
    }
}
