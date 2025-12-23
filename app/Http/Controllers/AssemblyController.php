<?php

namespace App\Http\Controllers;

use App\Models\AssemblyComponent;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssemblyController extends Controller
{
    /**
     * Display a listing of assembly items.
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;
        
        $assemblies = Inventory::where('company_id', $company->id)
            ->where('is_assembly', true)
            ->with(['components.component', 'account'])
            ->orderBy('name')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'assemblies' => $assemblies,
            ]);
        }

        return view('assemblies.index', compact('assemblies'));
    }

    /**
     * Display the specified assembly with BOM.
     */
    public function show(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $assembly = Inventory::where('company_id', $company->id)
            ->where('id', $id)
            ->where('is_assembly', true)
            ->with(['components.component.account', 'account'])
            ->firstOrFail();

        // Calculate total BOM cost
        $totalBomCost = $assembly->getComponentsCost();
        
        // Check stock availability for each component
        $stockStatus = [];
        foreach ($assembly->components as $component) {
            $stockStatus[$component->id] = [
                'available' => $component->component->stock,
                'required' => $component->quantity,
                'sufficient' => $component->component->stock >= $component->quantity,
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'assembly' => $assembly,
                'total_bom_cost' => $totalBomCost,
                'stock_status' => $stockStatus,
            ]);
        }

        return view('assemblies.show', compact('assembly', 'totalBomCost', 'stockStatus'));
    }

    /**
     * Add a component to assembly BOM.
     */
    public function addComponent(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $assembly = Inventory::where('company_id', $company->id)
            ->where('id', $id)
            ->where('is_assembly', true)
            ->firstOrFail();

        $validated = $request->validate([
            'component_id' => 'required|exists:inventories,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        // Validate component is not an assembly (prevent circular dependencies)
        $component = Inventory::findOrFail($validated['component_id']);
        if ($component->is_assembly) {
            return response()->json([
                'success' => false,
                'message' => 'Komponen tidak boleh berupa assembly item.',
            ], 422);
        }

        // Check if component already exists in BOM
        $existing = AssemblyComponent::where('assembly_id', $assembly->id)
            ->where('component_id', $validated['component_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Komponen sudah ada dalam BOM.',
            ], 422);
        }

        // Create assembly component
        $assemblyComponent = AssemblyComponent::create([
            'company_id' => $company->id,
            'assembly_id' => $assembly->id,
            'component_id' => $validated['component_id'],
            'quantity' => $validated['quantity'],
            'unit' => $component->unit,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update assembly cost based on components
        $assembly->cost = $assembly->getComponentsCost();
        $assembly->save();

        return response()->json([
            'success' => true,
            'message' => 'Komponen berhasil ditambahkan ke BOM.',
            'component' => $assemblyComponent->load('component'),
            'new_assembly_cost' => $assembly->cost,
        ]);
    }

    /**
     * Update a component in BOM.
     */
    public function updateComponent(Request $request, $componentId)
    {
        $company = $request->user()->company;
        
        $assemblyComponent = AssemblyComponent::where('company_id', $company->id)
            ->where('id', $componentId)
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $assemblyComponent->update($validated);

        // Update assembly cost
        $assembly = $assemblyComponent->assembly;
        $assembly->cost = $assembly->getComponentsCost();
        $assembly->save();

        return response()->json([
            'success' => true,
            'message' => 'Komponen berhasil diupdate.',
            'component' => $assemblyComponent->fresh(),
            'new_assembly_cost' => $assembly->cost,
        ]);
    }

    /**
     * Remove a component from BOM.
     */
    public function removeComponent(Request $request, $componentId)
    {
        $company = $request->user()->company;
        
        $assemblyComponent = AssemblyComponent::where('company_id', $company->id)
            ->where('id', $componentId)
            ->firstOrFail();

        $assembly = $assemblyComponent->assembly;
        $assemblyComponent->delete();

        // Update assembly cost
        $assembly->cost = $assembly->getComponentsCost();
        $assembly->save();

        return response()->json([
            'success' => true,
            'message' => 'Komponen berhasil dihapus dari BOM.',
            'new_assembly_cost' => $assembly->cost,
        ]);
    }

    /**
     * Calculate total BOM cost for an assembly.
     */
    public function calculateCost(Request $request, $id)
    {
        $company = $request->user()->company;
        
        $assembly = Inventory::where('company_id', $company->id)
            ->where('id', $id)
            ->where('is_assembly', true)
            ->with('components.component')
            ->firstOrFail();

        $totalCost = $assembly->getComponentsCost();
        
        $breakdown = $assembly->components->map(function($comp) {
            return [
                'component_name' => $comp->component->name,
                'quantity' => $comp->quantity,
                'unit_cost' => $comp->component->cost,
                'total_cost' => $comp->getTotalCost(),
            ];
        });

        return response()->json([
            'success' => true,
            'total_cost' => $totalCost,
            'breakdown' => $breakdown,
        ]);
    }
}
