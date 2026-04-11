<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\InternetBilling;
use App\Models\InternetCustomer;
use App\Models\InternetPayment;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternetCustomerController extends Controller
{
    // ==========================================
    // SETTINGS COA
    // ==========================================

    /**
     * GET /internet/settings
     */
    public function settings(Request $request)
    {
        $company = $request->user()->company;
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('internet.settings', compact('company', 'accounts'));
    }

    /**
     * POST /internet/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        $request->validate([
            'internet_receivable_module_coa_id' => ['required', 'exists:chart_of_accounts,id'],
            'internet_revenue_module_coa_id' => ['required', 'exists:chart_of_accounts,id'],
        ]);

        $user->company->update([
            'internet_receivable_module_coa_id' => $request->internet_receivable_module_coa_id,
            'internet_revenue_module_coa_id' => $request->internet_revenue_module_coa_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan COA internet berhasil diperbarui.',
        ]);
    }

    // ==========================================
    // PELANGGAN INTERNET (CRUD)
    // ==========================================

    /**
     * GET /internet
     * Daftar pelanggan internet.
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $query = InternetCustomer::where('company_id', $company->id)
            ->orderBy('customer_id');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20);

        // Summary stats
        $stats = [
            'total' => InternetCustomer::where('company_id', $company->id)->count(),
            'active' => InternetCustomer::where('company_id', $company->id)->where('status', 'active')->count(),
            'total_monthly_revenue' => InternetCustomer::where('company_id', $company->id)->where('status', 'active')->sum('monthly_rate'),
            'total_outstanding' => InternetBilling::where('company_id', $company->id)->whereIn('status', ['unpaid', 'partial', 'overdue'])->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')->value('total') ?? 0,
        ];

        return view('internet.index', compact('customers', 'stats'));
    }

    /**
     * POST /internet
     * Tambah pelanggan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        $company = $user->company;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'package_name' => ['required', 'string', 'max:255'],
            'monthly_rate' => ['required', 'numeric', 'min:1000'],
            'billing_date' => ['required', 'integer', 'min:1', 'max:28'],
            'activated_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = InternetCustomer::create([
            'company_id' => $company->id,
            'customer_id' => InternetCustomer::generateCustomerId($company->id),
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'package_name' => $request->package_name,
            'monthly_rate' => $request->monthly_rate,
            'billing_date' => $request->billing_date,
            'status' => 'active',
            'activated_at' => $request->activated_at ?? now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan internet berhasil ditambahkan.',
            'data' => $customer,
        ], 201);
    }

    /**
     * PUT /internet/{id}
     * Edit pelanggan.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        $customer = InternetCustomer::where('company_id', $user->company_id)->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'package_name' => ['required', 'string', 'max:255'],
            'monthly_rate' => ['required', 'numeric', 'min:1000'],
            'billing_date' => ['required', 'integer', 'min:1', 'max:28'],
            'status' => ['required', 'in:active,suspended,terminated'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($request->only([
            'name', 'address', 'phone', 'email', 'package_name',
            'monthly_rate', 'billing_date', 'status', 'notes',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'data' => $customer,
        ]);
    }

    /**
     * DELETE /internet/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        $customer = InternetCustomer::where('company_id', $user->company_id)->findOrFail($id);

        // Check if customer has billings
        if ($customer->billings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan ini memiliki riwayat tagihan. Ubah status ke "Terminated" sebagai gantinya.',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }

    /**
     * GET /internet/{id}
     * Detail pelanggan + riwayat billing & payment.
     */
    public function customerDetail(Request $request, int $id)
    {
        $company = $request->user()->company;

        $customer = InternetCustomer::where('company_id', $company->id)
            ->findOrFail($id);

        $billings = InternetBilling::where('internet_customer_id', $customer->id)
            ->with('payments.cashBankAccount')
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->paginate(24);

        $summary = [
            'total_billed' => InternetBilling::where('internet_customer_id', $customer->id)->sum('amount'),
            'total_paid' => InternetBilling::where('internet_customer_id', $customer->id)->sum('paid_amount'),
            'outstanding' => $customer->outstanding_balance,
            'overdue_count' => InternetBilling::where('internet_customer_id', $customer->id)->where('status', 'overdue')->count(),
        ];

        return view('internet.show', compact('customer', 'billings', 'summary'));
    }

    // ==========================================
    // BILLING (Generate & List)
    // ==========================================

    /**
     * GET /internet/billing
     * Daftar tagihan.
     */
    public function billingIndex(Request $request)
    {
        $company = $request->user()->company;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $query = InternetBilling::where('company_id', $company->id)
            ->with('customer:id,customer_id,name,package_name')
            ->orderBy('billing_number');

        if ($request->filled('month') || $request->filled('year')) {
            $query->forPeriod($month, $year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%");
            });
        }

        $billings = $query->paginate(30);

        // Summary for this period
        $periodStats = InternetBilling::where('company_id', $company->id)
            ->forPeriod($month, $year)
            ->selectRaw('
                COUNT(*) as total_bills,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(amount - paid_amount), 0) as total_outstanding
            ')
            ->first();

        // Cash/Bank accounts for payment modal
        $cashBankAccounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->where('is_active', true)
            ->cashBank()
            ->orderBy('code')
            ->get();

        return view('internet.billing', compact('billings', 'periodStats', 'month', 'year', 'cashBankAccounts'));
    }

    /**
     * POST /internet/billing/generate
     * Generate billing for all active customers for a given month.
     * Auto-creates journal: Debit Piutang, Credit Pendapatan.
     */
    public function generateBilling(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        $company = $user->company;

        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
        ]);

        $month = (int)$request->month;
        $year = (int)$request->year;

        // Get active customers
        $customers = InternetCustomer::where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pelanggan aktif.',
            ], 422);
        }

        // Check if COA settings are configured
        if (!$company->internet_receivable_module_coa_id || !$company->internet_revenue_module_coa_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akun COA Piutang/Pendapatan Internet belum diatur di menu Pengaturan.',
            ], 422);
        }

        $receivableAccount = $company->internetReceivableAccount;
        $revenueAccount = $company->internetRevenueAccount;

        $generated = 0;
        $skipped = 0;

        DB::transaction(function () use ($customers, $company, $month, $year, $receivableAccount, $revenueAccount, &$generated, &$skipped) {
            foreach ($customers as $customer) {
                // Skip if billing already exists for this period
                $exists = InternetBilling::where('internet_customer_id', $customer->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $billingNumber = InternetBilling::generateBillingNumber($company->id, $month, $year);
                $billingDate = sprintf('%04d-%02d-%02d', $year, $month, min($customer->billing_date, 28));
                $dueDate = \Carbon\Carbon::parse($billingDate)->addDays(14);

                // Create Journal: Debit Piutang, Credit Pendapatan
                $journal = Journal::create([
                    'company_id' => $company->id,
                    'date' => $billingDate,
                    'reference' => $billingNumber,
                    'description' => "Tagihan Internet {$this->monthName($month)} {$year} - {$customer->name}",
                    'source' => 'internet_billing',
                    'is_posted' => true,
                ]);

                // Debit: Piutang Pelanggan Internet
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $receivableAccount->id,
                    'debit' => $customer->monthly_rate,
                    'credit' => 0,
                    'memo' => "Piutang Internet {$customer->customer_id} - {$customer->name}",
                ]);

                // Credit: Pendapatan Jasa Internet
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => $customer->monthly_rate,
                    'memo' => "Pendapatan Internet {$customer->customer_id} - {$customer->name}",
                ]);

                // Create Billing record
                InternetBilling::create([
                    'company_id' => $company->id,
                    'internet_customer_id' => $customer->id,
                    'journal_id' => $journal->id,
                    'billing_number' => $billingNumber,
                    'period_month' => $month,
                    'period_year' => $year,
                    'amount' => $customer->monthly_rate,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                    'due_date' => $dueDate,
                ]);

                $generated++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Billing berhasil digenerate: {$generated} tagihan dibuat, {$skipped} dilewati (sudah ada).",
            'data' => [
                'generated' => $generated,
                'skipped' => $skipped,
            ],
        ]);
    }

    // ==========================================
    // PAYMENT
    // ==========================================

    /**
     * POST /internet/billing/{id}/pay
     * Record payment for a billing.
     * Auto-creates journal: Debit Kas/Bank, Credit Piutang.
     */
    public function recordPayment(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        $company = $user->company;

        $billing = InternetBilling::where('company_id', $company->id)
            ->with('customer')
            ->findOrFail($id);

        if ($billing->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini sudah lunas.',
            ], 422);
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,transfer,other'],
            'cash_bank_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $payAmount = (float)$request->amount;
        $remaining = $billing->remaining_amount;

        if ($payAmount > $remaining) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah bayar (Rp " . number_format($payAmount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($remaining, 0, ',', '.') . ").",
            ], 422);
        }

        // Check if COA settings are configured
        if (!$company->internet_receivable_module_coa_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akun COA Piutang Internet belum diatur di menu Pengaturan.',
            ], 422);
        }

        $receivableAccount = $company->internetReceivableAccount;

        $payment = DB::transaction(function () use ($request, $company, $billing, $payAmount, $receivableAccount) {
            $paymentNumber = InternetPayment::generatePaymentNumber($company->id);
            $customer = $billing->customer;

            // Create Journal: Debit Kas/Bank, Credit Piutang
            $journal = Journal::create([
                'company_id' => $company->id,
                'date' => $request->payment_date,
                'reference' => $paymentNumber,
                'description' => "Pembayaran Internet {$billing->period_label} - {$customer->name}",
                'source' => 'internet_payment',
                'is_posted' => true,
            ]);

            // Debit: Kas/Bank
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $request->cash_bank_account_id,
                'debit' => $payAmount,
                'credit' => 0,
                'memo' => "Pembayaran dari {$customer->customer_id} - {$customer->name}",
            ]);

            // Credit: Piutang Pelanggan Internet
            JournalItem::create([
                'journal_id' => $journal->id,
                'coa_id' => $receivableAccount->id,
                'debit' => 0,
                'credit' => $payAmount,
                'memo' => "Pelunasan tagihan {$billing->billing_number}",
            ]);

            // Create Payment record
            $payment = InternetPayment::create([
                'company_id' => $company->id,
                'internet_billing_id' => $billing->id,
                'journal_id' => $journal->id,
                'payment_number' => $paymentNumber,
                'amount' => $payAmount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'cash_bank_account_id' => $request->cash_bank_account_id,
                'notes' => $request->notes,
            ]);

            // Update billing paid_amount and status
            $billing->paid_amount += $payAmount;
            $billing->save();
            $billing->refreshStatus();

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat.',
            'data' => $payment->load('billing.customer'),
        ]);
    }

    // ==========================================
    // BUKU BANTU PIUTANG (Subsidiary Ledger)
    // ==========================================

    /**
     * GET /internet/ledger
     * Buku Bantu Piutang — ringkasan per pelanggan.
     */
    public function subsidiaryLedger(Request $request)
    {
        $company = $request->user()->company;

        $customers = InternetCustomer::where('company_id', $company->id)
            ->withCount(['billings as total_billed' => function ($q) {
                $q->selectRaw('COALESCE(SUM(amount), 0)');
            }])
            ->withCount(['billings as total_paid_amount' => function ($q) {
                $q->selectRaw('COALESCE(SUM(paid_amount), 0)');
            }])
            ->withCount(['billings as unpaid_count' => function ($q) {
                $q->whereIn('status', ['unpaid', 'partial', 'overdue']);
            }])
            ->orderBy('customer_id')
            ->get();

        // Grand totals
        $grandTotals = [
            'total_billed' => InternetBilling::where('company_id', $company->id)->sum('amount'),
            'total_paid' => InternetBilling::where('company_id', $company->id)->sum('paid_amount'),
            'total_outstanding' => InternetBilling::where('company_id', $company->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0,
        ];

        return view('internet.ledger', compact('customers', 'grandTotals'));
    }

    // ==========================================
    // PRIVATE HELPERS
    // ==========================================

    /**
     * Ensure a COA account exists, create if not.
     */
    private function ensureCoa(int $companyId, string $code, string $name, string $type, string $reportType, string $normalBalance, ?string $category = null): ChartOfAccount
    {
        $account = ChartOfAccount::where('company_id', $companyId)
            ->where('code', $code)
            ->first();

        if (!$account) {
            // Find parent account
            $parts = explode('.', $code);
            array_pop($parts);
            $parentCode = implode('.', $parts);
            $parent = ChartOfAccount::where('company_id', $companyId)
                ->where('code', $parentCode)
                ->first();

            $account = ChartOfAccount::create([
                'company_id' => $companyId,
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'report_type' => $reportType,
                'normal_balance' => $normalBalance,
                'is_parent' => false,
                'parent_id' => $parent?->id,
                'level' => substr_count($code, '.') + 1,
                'is_system' => true,
                'is_active' => true,
                'account_category' => $category,
            ]);
        }

        return $account;
    }

    private function monthName(int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $months[$month] ?? '';
    }
}
