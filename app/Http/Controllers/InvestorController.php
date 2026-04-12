<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    /**
     * Display a listing of the investors.
     */
    public function index()
    {
        $company = auth()->user()->company;
        $investors = Investor::where('company_id', $company->id)->get();

        return view('investors.index', compact('investors'));
    }

    /**
     * Show the form for creating a new investor.
     */
    public function create()
    {
        return view('investors.create');
    }

    /**
     * Store a newly created investor in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'share_percentage' => 'required|numeric|min:0|max:100',
            'basis' => 'required|in:GROSS_PROFIT,NET_PROFIT',
            'is_active' => 'boolean',
        ]);

        $company = auth()->user()->company;
        $validated['company_id'] = $company->id;
        $validated['is_active'] = $request->has('is_active');

        Investor::create($validated);

        return redirect()->route('investors.index')->with('success', 'Investor berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified investor.
     */
    public function edit(Investor $investor)
    {
        // Ensure investor belongs to current company
        if ($investor->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        return view('investors.edit', compact('investor'));
    }

    /**
     * Update the specified investor in storage.
     */
    public function update(Request $request, Investor $investor)
    {
        // Ensure investor belongs to current company
        if ($investor->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'share_percentage' => 'required|numeric|min:0|max:100',
            'basis' => 'required|in:GROSS_PROFIT,NET_PROFIT',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $investor->update($validated);

        return redirect()->route('investors.index')->with('success', 'Data investor berhasil diperbarui.');
    }

    /**
     * Remove the specified investor from storage.
     */
    public function destroy(Investor $investor)
    {
        // Ensure investor belongs to current company
        if ($investor->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if ($investor->sharings()->exists()) {
            return back()->with('error', 'Investor tidak bisa dihapus karena sudah memiliki histori bagi hasil.');
        }

        $investor->delete();

        return redirect()->route('investors.index')->with('success', 'Investor berhasil dihapus.');
    }
}
