<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $company = auth()->user()->company;
        return view('company.settings', compact('company'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'npwp' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'fiscal_start' => 'nullable|date',
            'director_name' => 'nullable|string|max:255',
            'director_title' => 'nullable|string|max:255',
            'secretary_name' => 'nullable|string|max:255',
            'secretary_title' => 'nullable|string|max:255',
            'staff_name' => 'nullable|string|max:255',
            'staff_title' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'enable_psak69' => 'nullable|boolean',
            'business_sector' => 'nullable|in:general,livestock,plantation,aquaculture,forestry,mixed_agriculture',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        // Convert checkbox value
        $validated['enable_psak69'] = $request->boolean('enable_psak69');

        auth()->user()->company->update($validated);

        return redirect()->route('company.settings')->with('success', 'Pengaturan perusahaan berhasil diperbarui');
    }
}
