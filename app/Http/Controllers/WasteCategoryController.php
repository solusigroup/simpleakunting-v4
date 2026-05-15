<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use Illuminate\Http\Request;

class WasteCategoryController extends Controller
{
    public function index()
    {
        $categories = WasteCategory::all();
        return view('waste.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:10',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
        ]);

        WasteCategory::create($validated);

        return redirect()->route('waste.categories.index')->with('success', 'Kategori sampah berhasil ditambahkan');
    }

    public function update(Request $request, WasteCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:10',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('waste.categories.index')->with('success', 'Kategori sampah berhasil diperbarui');
    }

    public function destroy(WasteCategory $category)
    {
        if ($category->deposits()->exists() || $category->sales()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah memiliki transaksi');
        }

        $category->delete();

        return redirect()->route('waste.categories.index')->with('success', 'Kategori sampah berhasil dihapus');
    }
}
