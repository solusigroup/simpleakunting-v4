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
use Illuminate\Validation\Rule;

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
            'internet_receivable_module_coa_id' => [
                'required', 
                Rule::exists('chart_of_accounts', 'id')->where('company_id', $user->company->id)
            ],
            'internet_revenue_module_coa_id' => [
                'required', 
                Rule::exists('chart_of_accounts', 'id')->where('company_id', $user->company->id)
            ],
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
                    'is_posted' => false,
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
            'cash_bank_account_id' => [
                'required', 
                Rule::exists('chart_of_accounts', 'id')->where('company_id', $company->id)
            ],
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
                'is_posted' => false,
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

    // ==========================================
    // IMPOR PELANGGAN INTERNET (EXCEL)
    // ==========================================

    /**
     * GET /internet/import
     */
    public function showImportForm(Request $request)
    {
        return view('internet.import');
    }

    /**
     * GET /internet/import/template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Internet Customers');
        
        // Headers
        $headers = [
            'Customer ID', 'Nama Pelanggan', 'Alamat', 'No. Telepon', 'Email', 
            'Paket Internet', 'Tarif Bulanan', 'Tanggal Tagih', 'Status', 'Catatan'
        ];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'e86c25']], // primary theme color
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        // Sample data
        $sampleData = [
            ['PLG-001', 'Budi Santoso', 'Jl. Merdeka No. 10', '081234567890', 'budi@example.com', '10 Mbps', 150000, 1, 'active', 'Pelanggan baru'],
            ['PLG-002', 'Siti Rahma', 'Jl. Mawar No. 4', '085678901234', 'siti@example.com', '20 Mbps', 250000, 5, 'active', ''],
            ['', 'Toko Berkah', 'Pasar Baru Blok A/5', '081122334455', '', '50 Mbps', 500000, 10, 'active', 'Gunakan IP Statis'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');
        
        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Instructions sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            ['Kolom', 'Wajib?', 'Tipe Data', 'Nilai Valid', 'Keterangan'],
            ['Customer ID', 'Tidak', 'Teks', 'Maks 20 karakter, unik', 'Kode pelanggan. Kosongkan untuk generate otomatis (misal PLG-001)'],
            ['Nama Pelanggan', 'Ya', 'Teks', 'Maks 255 karakter', 'Nama lengkap pelanggan'],
            ['Alamat', 'Tidak', 'Teks', '-', 'Alamat instalasi/rumah'],
            ['No. Telepon', 'Tidak', 'Teks', 'Maks 20 karakter', 'Nomor HP/Telepon aktif'],
            ['Email', 'Tidak', 'Email', '-', 'Alamat email valid'],
            ['Paket Internet', 'Ya', 'Teks', 'Maks 255 karakter', 'Nama paket (misal: 10 Mbps, 20 Mbps)'],
            ['Tarif Bulanan', 'Ya', 'Angka', 'Min 0', 'Tarif tagihan bulanan (tanpa Rp atau titik)'],
            ['Tanggal Tagih', 'Ya', 'Angka', '1 s/d 28', 'Tanggal tagih bulanan (default 1)'],
            ['Status', 'Tidak', 'Pilihan', 'active, suspended, terminated', 'Status langganan (default: active)'],
            ['Catatan', 'Tidak', 'Teks', '-', 'Catatan tambahan'],
        ];
        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        foreach (range('A', 'E') as $col) {
            $instructionsSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'internet_customers_template_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * POST /internet/import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Perusahaan tidak ditemukan.',
            ], 400);
        }

        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk import pelanggan.',
            ], 403);
        }

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows); // Remove header row

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $imported = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    // Check if row is empty
                    if (empty(array_filter($row, function ($val) { return $val !== null && $val !== ''; }))) {
                        continue;
                    }

                    $customerId = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $address = trim($row[2] ?? '');
                    $phone = trim($row[3] ?? '');
                    $email = trim($row[4] ?? '');
                    $packageName = trim($row[5] ?? '');
                    $monthlyRate = $row[6];
                    $billingDate = $row[7];
                    $status = strtolower(trim($row[8] ?? 'active'));
                    $notes = trim($row[9] ?? '');

                    // Validation
                    if (empty($name)) {
                        throw new \Exception('Nama Pelanggan wajib diisi');
                    }
                    if (empty($packageName)) {
                        throw new \Exception('Paket Internet wajib diisi');
                    }
                    if ($monthlyRate === null || $monthlyRate === '') {
                        throw new \Exception('Tarif Bulanan wajib diisi');
                    }
                    if (!is_numeric($monthlyRate) || $monthlyRate < 0) {
                        throw new \Exception('Tarif Bulanan harus berupa angka positif');
                    }
                    if ($billingDate === null || $billingDate === '') {
                        $billingDate = 1; // Default
                    }
                    if (!is_numeric($billingDate) || $billingDate < 1 || $billingDate > 28) {
                        throw new \Exception('Tanggal Tagih harus berupa angka antara 1 s/d 28');
                    }
                    if (!in_array($status, ['active', 'suspended', 'terminated'])) {
                        $status = 'active'; // Default
                    }
                    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception('Format email tidak valid');
                    }

                    // Check duplicate customer ID if provided
                    if (!empty($customerId)) {
                        $exists = InternetCustomer::where('company_id', $company->id)
                            ->where('customer_id', $customerId)
                            ->exists();

                        if ($exists) {
                            throw new \Exception("ID Pelanggan '{$customerId}' sudah digunakan");
                        }
                    } else {
                        // Generate dynamic unique ID taking local imports transaction into account
                        $generatedId = InternetCustomer::generateCustomerId($company->id);
                        $counter = 0;
                        while (true) {
                            $match = false;
                            foreach ($imported as $item) {
                                if ($item['customer_id'] === $generatedId) {
                                    $match = true;
                                    break;
                                }
                            }
                            if (!$match) {
                                break;
                            }
                            preg_match('/(\d+)$/', $generatedId, $matches);
                            $next = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
                            $generatedId = 'PLG-' . str_pad($next, 3, '0', STR_PAD_LEFT);
                            $counter++;
                            if ($counter > 1000) break;
                        }
                        $customerId = $generatedId;
                    }

                    $customer = InternetCustomer::create([
                        'company_id' => $company->id,
                        'customer_id' => $customerId,
                        'name' => $name,
                        'address' => $address ?: null,
                        'phone' => $phone ?: null,
                        'email' => $email ?: null,
                        'package_name' => $packageName,
                        'monthly_rate' => (float)$monthlyRate,
                        'billing_date' => (int)$billingDate,
                        'status' => $status,
                        'activated_at' => now(),
                        'notes' => $notes ?: null,
                    ]);

                    $successCount++;
                    $imported[] = [
                        'row' => $rowNumber,
                        'customer_id' => $customer->customer_id,
                        'name' => $name,
                    ];

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'customer_id' => $customerId ?? '',
                        'name' => $name ?? '',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            if ($errorCount > 0 && $successCount == 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            return response()->json([
                'success' => true,
                'message' => "Import selesai: {$successCount} berhasil, {$errorCount} gagal.",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'imported' => $imported,
                    'errors' => $errors,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
