<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class WasteSettingsController extends Controller
{
    public function edit()
    {
        $company = auth()->user()->company;
        $accounts = ChartOfAccount::where('is_parent', false)->orderBy('code')->get();
        
        return view('waste.settings', compact('company', 'accounts'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'waste_inventory_account_id' => 'nullable|exists:chart_of_accounts,id',
            'waste_liability_account_id' => 'nullable|exists:chart_of_accounts,id',
            'waste_revenue_account_id' => 'nullable|exists:chart_of_accounts,id',
            'waste_cogs_account_id' => 'nullable|exists:chart_of_accounts,id',
            'waste_cash_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        auth()->user()->company->update($validated);

        return redirect()->route('waste.settings.edit')->with('success', 'Pengaturan Bank Sampah berhasil diperbarui');
    }
}
