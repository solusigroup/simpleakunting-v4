<?php

namespace App\Http\Controllers;

use App\Models\WasteCollector;
use Illuminate\Http\Request;

class WasteCollectorController extends Controller
{
    public function index()
    {
        $collectors = WasteCollector::latest()->paginate(10);
        
        // Calculate next auto-number
        $lastCollector = WasteCollector::orderBy('id', 'desc')->first();
        $nextNumber = 1;
        if ($lastCollector) {
            preg_match('/(\d+)$/', $lastCollector->collector_number, $matches);
            $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        $nextId = 'NSB-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('waste.collectors.index', compact('collectors', 'nextId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'collector_number' => 'nullable|string|unique:waste_collectors,collector_number',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if (empty($validated['collector_number'])) {
            $lastCollector = WasteCollector::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastCollector) {
                preg_match('/(\d+)$/', $lastCollector->collector_number, $matches);
                $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            }
            $validated['collector_number'] = 'NSB-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        WasteCollector::create($validated);

        return redirect()->route('waste.collectors.index')->with('success', 'Nasabah berhasil ditambahkan');
    }

    public function show(WasteCollector $collector)
    {
        $collector->load(['deposits.category', 'withdrawals']);
        return view('waste.collectors.show', compact('collector'));
    }

    public function update(Request $request, WasteCollector $collector)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $collector->update($validated);

        return redirect()->route('waste.collectors.index')->with('success', 'Nasabah berhasil diperbarui');
    }

    public function destroy(WasteCollector $collector)
    {
        if ($collector->deposits()->exists() || $collector->withdrawals()->exists()) {
            return back()->with('error', 'Nasabah tidak dapat dihapus karena sudah memiliki transaksi');
        }

        $collector->delete();

        return redirect()->route('waste.collectors.index')->with('success', 'Nasabah berhasil dihapus');
    }
}
