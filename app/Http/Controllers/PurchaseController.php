<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * GET /purchases
     * List Pembelian/Tagihan.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $query = Invoice::where('company_id', $company->id)
            ->where('type', 'Purchase')
            ->with(['contact:id,name', 'businessUnit:id,name'])
            ->orderBy('date', 'desc');

        // Date filters
        if ($request->has('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->has('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }

        // Unit filter (BUMDesa)
        if ($request->has('unit_id')) {
            $query->where('business_unit_id', $request->unit_id);
        }

        $invoices = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $invoices,
            ]);
        }

        return view('purchases.index', compact('invoices'));
    }

    /**
     * POST /purchases
     * Input Tagihan/Belanja dengan auto-journaling.
     * 
     * Auto Journal:
     * - Debit: Biaya/Aset (dari account_id di items)
     * - Kredit: Utang Usaha (atau Kas jika tunai)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canCreateTransactions()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membuat transaksi.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
            'payable_account_id' => ['required', 'exists:chart_of_accounts,id'], // Akun Utang/Kas
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.description' => ['required', 'string'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice = DB::transaction(function () use ($request, $company) {
            // Calculate totals
            $subtotal = collect($request->items)->sum('amount');

            // Generate invoice number
            $lastInvoice = Invoice::where('company_id', $company->id)
                ->where('type', 'Purchase')
                ->whereYear('date', now()->year)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastInvoice) {
                preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches);
                $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            }
            $invoiceNumber = 'PO-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Create Invoice
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'contact_id' => $request->contact_id,
                'business_unit_id' => $request->unit_id,
                'type' => 'Purchase',
                'invoice_number' => $invoiceNumber,
                'date' => $request->date,
                'due_date' => $request->due_date ?? $request->date,
                'subtotal' => $subtotal,
                'tax' => 0,
                'discount' => 0,
                'total' => $subtotal,
                'status' => 'Posted',
                'notes' => $request->notes,
            ]);

            // Create Invoice Items
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'coa_id' => $item['account_id'],
                    'description' => $item['description'],
                    'quantity' => 1,
                    'unit_price' => $item['amount'],
                    'total' => $item['amount'],
                ]);
            }

            // =============================================
            // AUTO-JOURNALING: Debit Biaya/Aset, Kredit Utang
            // =============================================
            $journal = Journal::create([
                'company_id' => $company->id,
                'business_unit_id' => $request->unit_id,
                'date' => $request->date,
                'reference' => $invoiceNumber,
                'description' => 'Pembelian: ' . $invoiceNumber,
                'source' => 'purchase',
                'is_posted' => true,
            ]);

            // Debit: Biaya/Aset (per item)
            foreach ($request->items as $item) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['account_id'],
                    'debit' => $item['amount'],
                    'credit' => 0,
                    'memo' => $item['description'],
                ]);
            }

            // Kredit: Utang Usaha
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $request->payable_account_id,
                'debit' => 0,
                'credit' => $subtotal,
                'memo' => 'Utang dari ' . $invoiceNumber,
            ]);

            // Link journal to invoice
            $invoice->update(['journal_id' => $journal->id]);

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tagihan pembelian berhasil dibuat.',
            'data' => $invoice->load(['items', 'contact', 'journal.items']),
        ], 201);
    }

    /**
     * GET /purchases/{id}
     * Detail Pembelian.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $invoice = Invoice::where('company_id', $user->company_id)
            ->where('type', 'Purchase')
            ->with(['items.account', 'contact', 'businessUnit', 'journal.items.account'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }
}
