<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessUnitController extends Controller
{
    /**
     * GET /units
     * List Business Units (BUMDesa only).
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

        $isBumdesa = $company->isBumdesa();
        
        $units = collect();
        if ($isBumdesa) {
            $units = BusinessUnit::where('company_id', $company->id)
                ->orderBy('code')
                ->get();
        }

        // Return JSON for API requests
        if ($request->wantsJson()) {
            if (!$isBumdesa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit Usaha hanya tersedia untuk BUMDesa.',
                ], 400);
            }
            return response()->json([
                'success' => true,
                'data' => $units,
            ]);
        }

        // Return view for browser requests
        return view('units.index', compact('units', 'isBumdesa', 'company'));
    }

    /**
     * POST /units
     * Tambah Unit Usaha baru.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$user->canManageMasterData()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambah unit usaha.',
            ], 403);
        }

        if (!$company->isBumdesa()) {
            return response()->json([
                'success' => false,
                'message' => 'Unit Usaha hanya tersedia untuk BUMDesa.',
            ], 400);
        }

        $request->validate([
            'code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        // Check if code already exists
        $exists = BusinessUnit::where('company_id', $company->id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kode unit sudah digunakan.',
            ], 422);
        }

        $unit = BusinessUnit::create([
            'company_id' => $company->id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit Usaha berhasil ditambahkan.',
            'data' => $unit,
        ], 201);
    }

    /**
     * PUT /units/{id}
     * Edit Unit Usaha.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$user->canManageMasterData()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit unit usaha.',
            ], 403);
        }

        $unit = BusinessUnit::where('company_id', $company->id)
            ->findOrFail($id);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $unit->update($request->only(['name', 'description', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Unit Usaha berhasil diperbarui.',
            'data' => $unit->fresh(),
        ]);
    }
}
