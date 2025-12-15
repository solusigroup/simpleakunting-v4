<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return view('setup.wizard');
        }

        // Get date range from query params or default to current month
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));


        // Calculate metrics
        $totalRevenue = $this->getTotalRevenue($company->id, $startDate, $endDate);
        $totalExpense = $this->getTotalExpense($company->id, $startDate, $endDate);
        $netProfit = $totalRevenue - $totalExpense;
        
        // Get cash balance
        $cashAccounts = ChartOfAccount::where('company_id', $company->id)
            ->whereIn('code', ['1100', '1.1.1']) // Kas & Bank
            ->get();
        $cashBalance = $cashAccounts->sum(fn($acc) => $acc->getBalance(null, $endDate));

        // Recent transactions
        $recentSales = Invoice::where('company_id', $company->id)
            ->where('type', 'Sales')
            ->with('contact:id,name')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $recentPurchases = Invoice::where('company_id', $company->id)
            ->where('type', 'Purchase')
            ->with('contact:id,name')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Count stats
        $totalCustomers = Contact::where('company_id', $company->id)->customers()->count();
        $totalSuppliers = Contact::where('company_id', $company->id)->suppliers()->count();
        $pendingInvoices = Invoice::where('company_id', $company->id)
            ->where('status', '!=', 'Paid')
            ->where('due_date', '<', now())
            ->count();

        return view('dashboard', compact(
            'company',
            'totalRevenue',
            'totalExpense',
            'netProfit',
            'cashBalance',
            'recentSales',
            'recentPurchases',
            'totalCustomers',
            'totalSuppliers',
            'pendingInvoices',
            'startDate',
            'endDate'
        ));
    }

    protected function getTotalRevenue(int $companyId, string $startDate, string $endDate): float
    {
        $revenueAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'Revenue')
            ->where('is_parent', false)
            ->get();

        return $revenueAccounts->sum(fn($acc) => abs($acc->getBalance($startDate, $endDate)));
    }

    protected function getTotalExpense(int $companyId, string $startDate, string $endDate): float
    {
        $expenseAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'Expense')
            ->where('is_parent', false)
            ->get();

        return $expenseAccounts->sum(fn($acc) => abs($acc->getBalance($startDate, $endDate)));
    }
}
