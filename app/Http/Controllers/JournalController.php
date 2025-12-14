<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    /**
     * GET /journals
     * List Jurnal Umum.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $query = Journal::where('company_id', $company->id)
            ->with(['businessUnit:id,name', 'items:id,journal_id,coa_id,debit,credit'])
            ->orderBy('date', 'desc');

        // Date filters
        if ($request->has('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->has('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }

        // Source filter
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        // Unit filter (BUMDesa)
        if ($request->has('unit_id')) {
            $query->where('business_unit_id', $request->unit_id);
        }

        $journals = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $journals,
            ]);
        }

        return view('journals.index', compact('journals'));
    }

    /**
     * POST /journals/manual
     * Input Jurnal Manual dengan validasi double-entry.
     * 
     * PENTING: SUM(debit) harus == SUM(credit)
     * Jika tidak seimbang, tolak dengan Error 422.
     */
    public function storeManual(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only Manajer and above can create manual journals
        if (!$user->canManageMasterData()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Manajer atau Administrator yang dapat membuat jurnal manual.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string'],
        ]);

        // =============================================
        // DOUBLE-ENTRY VALIDATION
        // SUM(debit) must equal SUM(credit)
        // =============================================
        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal tidak seimbang! Total Debit (' . number_format($totalDebit, 2) . ') harus sama dengan Total Kredit (' . number_format($totalCredit, 2) . ').',
                'errors' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'difference' => abs($totalDebit - $totalCredit),
                ],
            ], 422);
        }

        // Validate each line has either debit or credit (not both zero)
        foreach ($request->lines as $index => $line) {
            if ($line['debit'] == 0 && $line['credit'] == 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Baris ke-" . ($index + 1) . " harus memiliki nilai Debit atau Kredit.",
                ], 422);
            }
        }

        $journal = DB::transaction(function () use ($request, $company) {
            // Generate reference
            $reference = 'JV-' . now()->format('YmdHis');

            // Create Journal
            $journal = Journal::create([
                'company_id' => $company->id,
                'business_unit_id' => $request->unit_id,
                'date' => $request->date,
                'reference' => $reference,
                'description' => $request->description,
                'source' => 'manual',
                'is_posted' => true,
            ]);

            // Create Journal Items
            foreach ($request->lines as $line) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $line['account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'memo' => $line['memo'] ?? null,
                ]);
            }

            return $journal;
        });

        return response()->json([
            'success' => true,
            'message' => 'Jurnal manual berhasil dibuat.',
            'data' => $journal->load('items.account'),
        ], 201);
    }

    /**
     * GET /journals/{id}
     * Detail Jurnal.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $journal = Journal::where('company_id', $user->company_id)
            ->with(['items.account', 'businessUnit'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $journal,
        ]);
    }
}
