<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DataResetController extends Controller
{
    /**
     * Purge all transaction data for the current company.
     * Master data (COA, Contacts, Customers, Company Settings) are PRESERVED.
     */
    public function resetTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only Administrator can reset data
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat melakukan reset data.',
            ], 403);
        }

        $company = $user->company;

        if (!$request->has('confirm') || $request->confirm !== 'DELETE_ALL_TRANSACTIONS') {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi tidak valid. Harap masukkan kode konfirmasi yang tepat.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($company) {
                $companyId = $company->id;

                // 1. Investor Sharing
                DB::table('investor_sharing')->whereIn('investor_id', function ($q) use ($companyId) {
                    $q->select('id')->from('investors')->where('company_id', $companyId);
                })->delete();

                // 2. Internet Billings & Payments
                DB::table('internet_payments')->whereIn('internet_billing_id', function ($q) use ($companyId) {
                    $q->select('id')->from('internet_billings')->where('company_id', $companyId);
                })->delete();
                DB::table('internet_billings')->where('company_id', $companyId)->delete();

                // 3. Biological Transactions
                DB::table('agricultural_produce')->where('company_id', $companyId)->delete();
                DB::table('biological_valuations')->whereIn('biological_asset_id', function ($q) use ($companyId) {
                    $q->select('id')->from('biological_assets')->where('company_id', $companyId);
                })->delete();
                DB::table('biological_transformations')->whereIn('biological_asset_id', function ($q) use ($companyId) {
                    $q->select('id')->from('biological_assets')->where('company_id', $companyId);
                })->delete();

                // 4. Invoices & Items
                DB::table('invoice_items')->whereIn('invoice_id', function ($q) use ($companyId) {
                    $q->select('id')->from('invoices')->where('company_id', $companyId);
                })->delete();
                DB::table('invoices')->where('company_id', $companyId)->delete();

                // 5. Budgets
                DB::table('budgets')->where('company_id', $companyId)->delete();

                // 6. Production Transactions
                DB::table('production_components')->whereIn('production_id', function ($q) use ($companyId) {
                    $q->select('id')->from('productions')->where('company_id', $companyId);
                })->delete();
                DB::table('productions')->where('company_id', $companyId)->delete();

                // 7. Journals & Items (The Core)
                DB::table('journal_items')->whereIn('journal_id', function ($q) use ($companyId) {
                    $q->select('id')->from('journals')->where('company_id', $companyId);
                })->delete();
                DB::table('journals')->where('company_id', $companyId)->delete();

                // 8. Audit Logs
                DB::table('audit_logs')->where('company_id', $companyId)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Semua data transaksi berhasil dihapus. Data Master (COA, Pelanggan, Kontak) tetap aman.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat meriset data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
