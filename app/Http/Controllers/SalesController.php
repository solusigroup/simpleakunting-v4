<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    /**
     * GET /sales
     * List Invoice Penjualan.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $query = Invoice::where('company_id', $company->id)
            ->where('type', 'Sales')
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

        return view('sales.index', compact('invoices'));
    }

    /**
     * POST /sales
     * Buat Invoice Penjualan dengan auto-journaling.
     * 
     * Auto Journal:
     * - Debit: Piutang Usaha (atau Kas jika tunai)
     * - Kredit: Pendapatan (dari account_id di items)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membuat transaksi.',
            ], 403);
        }

        $company = $user->company;

        $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:date'],
            'unit_id' => ['nullable', 'exists:business_units,id'],
            'receivable_account_id' => ['required', 'exists:chart_of_accounts,id'], // Akun Piutang/Kas
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice = DB::transaction(function () use ($request, $company, $user) {
            // Calculate totals
            $subtotal = collect($request->items)->sum(function ($item) {
                return $item['qty'] * $item['amount'];
            });

            // Generate invoice number
            $lastInvoice = Invoice::where('company_id', $company->id)
                ->where('type', 'Sales')
                ->whereYear('date', now()->year)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastInvoice) {
                preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches);
                $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            }
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Create Invoice
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'contact_id' => $request->contact_id,
                'business_unit_id' => $request->unit_id,
                'type' => 'Sales',
                'invoice_number' => $invoiceNumber,
                'date' => $request->date,
                'due_date' => $request->due_date,
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
                    'inventory_id' => $item['inventory_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['amount'],
                    'total' => $item['qty'] * $item['amount'],
                ]);
            }

            // =============================================
            // AUTO-JOURNALING: Debit Piutang, Kredit Pendapatan
            // =============================================
            $journal = Journal::create([
                'company_id' => $company->id,
                'business_unit_id' => $request->unit_id,
                'date' => $request->date,
                'reference' => $invoiceNumber,
                'description' => 'Penjualan: ' . $invoiceNumber,
                'source' => 'sales',
                'is_posted' => true,
            ]);

            // Debit: Piutang Usaha
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $request->receivable_account_id,
                'debit' => $subtotal,
                'credit' => 0,
                'memo' => 'Piutang dari ' . $invoiceNumber,
            ]);

            // Kredit: Pendapatan (per item)
            foreach ($request->items as $item) {
                $itemTotal = $item['qty'] * $item['amount'];
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['account_id'],
                    'debit' => 0,
                    'credit' => $itemTotal,
                    'memo' => $item['description'],
                ]);
            }

            // Link journal to invoice
            $invoice->update(['journal_id' => $journal->id]);

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice penjualan berhasil dibuat.',
            'data' => $invoice->load(['items', 'contact', 'journal.items']),
        ], 201);
    }

    /**
     * GET /sales/{id}
     * Detail Invoice Penjualan.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $invoice = Invoice::where('company_id', $user->company_id)
            ->where('type', 'Sales')
            ->with(['items.account', 'contact', 'businessUnit', 'journal.items.account'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }
}
