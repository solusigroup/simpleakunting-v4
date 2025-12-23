<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * GET /inventory
     * List all inventory items.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Company not found'], 400);
            }
            return redirect()->route('setup.wizard');
        }

        $items = Inventory::where('company_id', $company->id)
            ->with('account:id,code,name')
            ->orderBy('code')
            ->get();

        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('type', 'Asset')
            ->where('is_parent', false)
            ->inventoryAccounts()
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $items]);
        }

        return view('inventory.index', compact('items', 'accounts', 'company'));
    }

    /**
     * POST /inventory
     * Create new inventory item.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan. Silakan lengkapi profil Anda.',
                ], 400);
            }

            if (!$user->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin.',
                ], 403);
            }

            $request->validate([
                'code' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:20'],
                'cost' => ['required', 'numeric', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'min_stock' => ['nullable', 'integer', 'min:0'],
                'coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            ]);

            $exists = Inventory::where('company_id', $company->id)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang sudah digunakan.',
                ], 422);
            }

            $item = Inventory::create([
                'company_id' => $company->id,
                'coa_id' => $request->coa_id,
                'code' => $request->code,
                'name' => $request->name,
                'unit' => $request->unit,
                'cost' => $request->cost,
                'price' => $request->price,
                'stock' => $request->stock,
                'min_stock' => $request->min_stock ?? 0,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan.',
                'data' => $item,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * GET /inventory/{id}
     * Get inventory item detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        $item = Inventory::where('company_id', $company->id)
            ->with('account:id,code,name')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * PUT /inventory/{id}
     * Update inventory item.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        $item = Inventory::where('company_id', $company->id)->findOrFail($id);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'cost' => ['sometimes', 'numeric', 'min:0'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $item->update($request->only(['name', 'unit', 'cost', 'price', 'stock', 'min_stock', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui.',
            'data' => $item->fresh(),
        ]);
    }
}
