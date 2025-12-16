<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * GET /contacts
     * List contacts dengan filter type.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Company not found'], 400);
            }
            return redirect()->route('dashboard');
        }

        $query = Contact::where('company_id', $company->id)
            ->orderBy('name');

        // Filter by type (Customer, Supplier, Both)
        if ($request->has('type')) {
            $type = strtoupper($request->type);
            if ($type === 'CUSTOMER') {
                $query->customers();
            } elseif ($type === 'VENDOR' || $type === 'SUPPLIER') {
                $query->suppliers();
            }
        }

        // Filter active only
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $contacts = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $contacts,
            ]);
        }

        return view('contacts.index', compact('contacts'));
    }

    /**
     * POST /contacts
     * Tambah kontak baru.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambah data.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:Customer,Supplier,Both'],
            'code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string', 'max:30'],
        ]);

        // Check if code already exists (if provided)
        if ($request->code) {
            $exists = Contact::where('company_id', $company->id)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode kontak sudah digunakan.',
                ], 422);
            }
        }

        $contact = Contact::create([
            'company_id' => $company->id,
            'name' => $request->name,
            'type' => $request->type,
            'code' => $request->code,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'npwp' => $request->npwp,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kontak berhasil ditambahkan.',
            'data' => $contact,
        ], 201);
    }

    /**
     * PUT /contacts/{id}
     * Edit kontak.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit data.',
            ], 403);
        }

        $contact = Contact::where('company_id', $user->company_id)
            ->findOrFail($id);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:Customer,Supplier,Both'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $contact->update($request->only([
            'name', 'type', 'phone', 'email', 'address', 'npwp', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kontak berhasil diperbarui.',
            'data' => $contact->fresh(),
        ]);
    }

    /**
     * GET /contacts/{id}
     * Detail kontak.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $contact = Contact::where('company_id', $user->company_id)
            ->withCount('invoices')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }
}
