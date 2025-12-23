<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountImportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\InventoryImportController;
use App\Http\Controllers\FixedAssetImportController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BiologicalAssetController;
use App\Http\Controllers\BiologicalReportController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ManufacturingReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Setup Wizard
    Route::get('/setup', function () {
        return view('setup.wizard');
    })->name('setup.wizard');
    Route::get('/user/profile', [SetupController::class, 'profile'])->name('user.profile');
    Route::post('/setup/init-coa', [SetupController::class, 'initCoa'])->name('setup.init-coa');
    Route::post('/api/company/update', [SetupController::class, 'updateCompany'])->name('api.company.update');

    // Help / Manual
    Route::view('/help', 'help.index')->name('help');
    
    // Company Settings (Administrator only)
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('/company/settings', [CompanySettingsController::class, 'edit'])->name('company.settings');
        Route::put('/company/settings', [CompanySettingsController::class, 'update'])->name('company.update');
        
        // User Management (Administrator only)
        Route::resource('users', UserController::class);
        
        // Audit Trail (Administrator only)
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
    
    // ==========================================
    // MASTER DATA
    // ==========================================
    
    // Chart of Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    
    // Chart of Accounts Import (must be before {id} routes)
    Route::get('/accounts/import', [AccountImportController::class, 'showForm'])->name('accounts.import.form');
    Route::get('/accounts/import/template', [AccountImportController::class, 'downloadTemplate'])->name('accounts.import.template');
    Route::post('/accounts/import', [AccountImportController::class, 'import'])->name('accounts.import');
    
    // Chart of Accounts - parameterized routes (must be after specific routes)
    Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    
    // Contacts (Customers/Suppliers)
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    
    // Contacts Import (before {id} routes)
    Route::get('/contacts/import', [ContactImportController::class, 'showForm'])->name('contacts.import.form');
    Route::get('/contacts/import/template', [ContactImportController::class, 'downloadTemplate'])->name('contacts.import.template');
    Route::post('/contacts/import', [ContactImportController::class, 'import'])->name('contacts.import');
    Route::get('/contacts/export', [ContactImportController::class, 'export'])->name('contacts.export');
    
    // Contacts parameterized routes
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('contacts.show');
    
    // Business Units (BUMDesa only)
    Route::get('/units', [BusinessUnitController::class, 'index'])->name('units.index');
    Route::post('/units', [BusinessUnitController::class, 'store'])->name('units.store');
    Route::put('/units/{id}', [BusinessUnitController::class, 'update'])->name('units.update');
    
    // Inventory (Persediaan)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    
    // Inventory Import (before {id} routes)
    Route::get('/inventory/import', [InventoryImportController::class, 'showForm'])->name('inventory.import.form');
    Route::get('/inventory/import/template', [InventoryImportController::class, 'downloadTemplate'])->name('inventory.import.template');
    Route::post('/inventory/import', [InventoryImportController::class, 'import'])->name('inventory.import');
    Route::get('/inventory/export', [InventoryImportController::class, 'export'])->name('inventory.export');
    
    // Inventory parameterized routes
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    
    // Fixed Assets (Aset Tetap)
    Route::get('/assets', [FixedAssetController::class, 'index'])->name('assets.index');
    Route::post('/assets', [FixedAssetController::class, 'store'])->name('assets.store');
    
    // Fixed Assets Import (before {id} routes)
    Route::get('/assets/import', [FixedAssetImportController::class, 'showForm'])->name('assets.import.form');
    Route::get('/assets/import/template', [FixedAssetImportController::class, 'downloadTemplate'])->name('assets.import.template');
    Route::post('/assets/import', [FixedAssetImportController::class, 'import'])->name('assets.import');
    Route::get('/assets/export', [FixedAssetImportController::class, 'export'])->name('assets.export');
    
    // Fixed Assets parameterized routes
    Route::get('/assets/{id}', [FixedAssetController::class, 'show'])->name('assets.show');
    Route::put('/assets/{id}', [FixedAssetController::class, 'update'])->name('assets.update');
    
    // ==========================================
    // BIOLOGICAL ASSETS (PSAK 69)
    // ==========================================
    
    // Biological Assets (Aset Biologis) - Only for companies with PSAK 69 enabled
    // TODO: Add proper middleware after creating CheckPsak69Enabled middleware
    // Biological Assets CRUD
    Route::get('/biological-assets', [BiologicalAssetController::class, 'index'])->name('biological-assets.index');
    Route::post('/biological-assets', [BiologicalAssetController::class, 'store'])->name('biological-assets.store');
    Route::get('/biological-assets/{id}', [BiologicalAssetController::class, 'show'])->name('biological-assets.show');
    Route::put('/biological-assets/{id}', [BiologicalAssetController::class, 'update'])->name('biological-assets.update');
    Route::delete('/biological-assets/{id}', [BiologicalAssetController::class, 'destroy'])->name('biological-assets.destroy');
    
    // Biological Assets Special Actions
    Route::post('/biological-assets/{id}/valuate', [BiologicalAssetController::class, 'valuate'])->name('biological-assets.valuate');
    Route::post('/biological-assets/{id}/transform', [BiologicalAssetController::class, 'transform'])->name('biological-assets.transform');
    Route::post('/biological-assets/{id}/harvest', [BiologicalAssetController::class, 'harvest'])->name('biological-assets.harvest');
    
    // ==========================================
    // MANUFACTURING (Assembly & Production)
    // ==========================================
    
    // Assembly / BOM Management
    Route::get('/assemblies', [AssemblyController::class, 'index'])->name('assemblies.index');
    Route::get('/assemblies/{id}', [AssemblyController::class, 'show'])->name('assemblies.show');
    Route::post('/assemblies/{id}/components', [AssemblyController::class, 'addComponent'])->name('assemblies.add-component');
    Route::put('/assemblies/components/{id}', [AssemblyController::class, 'updateComponent'])->name('assemblies.update-component');
    Route::delete('/assemblies/components/{id}', [AssemblyController::class, 'removeComponent'])->name('assemblies.remove-component');
    Route::get('/assemblies/{id}/cost', [AssemblyController::class, 'calculateCost'])->name('assemblies.calculate-cost');
    
    // Production / Manufacturing
    Route::get('/productions', [ProductionController::class, 'index'])->name('productions.index');
    Route::get('/productions/create', [ProductionController::class, 'create'])->name('productions.create');
    Route::post('/productions', [ProductionController::class, 'store'])->name('productions.store');
    Route::get('/productions/{id}', [ProductionController::class, 'show'])->name('productions.show');
    Route::post('/productions/{id}/start', [ProductionController::class, 'start'])->name('productions.start');
    Route::post('/productions/{id}/complete', [ProductionController::class, 'complete'])->name('productions.complete');
    Route::post('/productions/{id}/cancel', [ProductionController::class, 'cancel'])->name('productions.cancel');
    
    // Manufacturing Reports
    Route::get('/reports/manufacturing/production-cost', [ManufacturingReportController::class, 'productionCost'])->name('reports.manufacturing.production-cost');
    Route::get('/reports/manufacturing/material-usage', [ManufacturingReportController::class, 'materialUsage'])->name('reports.manufacturing.material-usage');
    Route::get('/reports/manufacturing/wip', [ManufacturingReportController::class, 'wipValuation'])->name('reports.manufacturing.wip');
    
    // ==========================================
    // TRANSACTIONS
    // ==========================================
    
    // Sales (Penjualan)
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', function () {
        return view('sales.create');
    })->name('sales.create');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/sales/{id}', [SalesController::class, 'show'])->name('sales.show');
    
    // Purchases (Pembelian)
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', function () {
        return view('purchases.create');
    })->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');
    
    // ==========================================
    // CASH TRANSACTIONS
    // ==========================================
    
    // Cash Receipt (Penerimaan Kas)
    Route::get('/cash/receive', function () {
        return view('cash.receive');
    })->name('cash.receive');
    Route::post('/cash/receive', [CashController::class, 'receive'])->name('cash.receive.store');
    
    // Cash Disbursement (Pengeluaran Kas)
    Route::get('/cash/spend', function () {
        return view('cash.spend');  
    })->name('cash.spend');
    Route::post('/cash/spend', [CashController::class, 'spend'])->name('cash.spend.store');
    
    // Journal (Manual Entry)
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::post('/journals/manual', [JournalController::class, 'storeManual'])->name('journals.manual');
    Route::get('/journals/{id}', [JournalController::class, 'show'])->name('journals.show');
    
    // Budgets (Anggaran)
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    Route::get('/budgets/comparison', [BudgetController::class, 'comparison'])->name('budgets.comparison');
    
    // Closing & Adjustment
    Route::get('/journals/closing', function () {
        return view('journals.closing');
    })->name('journals.closing');
    Route::get('/journals/adjustment', function () {
        $accounts = \App\Models\ChartOfAccount::where('company_id', auth()->user()->company_id)
            ->where('is_parent', false)
            ->orderBy('code')
            ->get();
        $businessUnits = \App\Models\BusinessUnit::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        return view('journals.adjustment', compact('accounts', 'businessUnits'));
    })->name('journals.adjustment');
    
    // ==========================================
    // REPORTS
    // ==========================================
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/ledger/{account_id?}', [ReportController::class, 'ledger'])->name('ledger');
        Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/financial-analysis', [ReportController::class, 'financialAnalysis'])->name('financial-analysis');
        
        // PDF Export routes
        Route::get('/balance-sheet/export-pdf', [ReportController::class, 'exportBalanceSheetPDF'])->name('balance-sheet.export-pdf');
        
        // Comparative routes
        Route::post('/balance-sheet/comparative', [ReportController::class, 'balanceSheetComparative'])->name('balance-sheet.comparative');
        Route::get('/balance-sheet/comparative/export-pdf', [ReportController::class, 'exportBalanceSheetComparativePDF'])->name('balance-sheet.comparative.export-pdf');
        
        // Profit-Loss Export routes
        Route::get('/profit-loss/export-pdf', [ReportController::class, 'exportProfitLossPDF'])->name('profit-loss.export-pdf');
        Route::post('/profit-loss/comparative', [ReportController::class, 'profitLossComparative'])->name('profit-loss.comparative');
        Route::get('/profit-loss/comparative/export-pdf', [ReportController::class, 'exportProfitLossComparativePDF'])->name('profit-loss.comparative.export-pdf');
        
        // Cash Flow Export routes
        Route::get('/cash-flow/export-pdf', [ReportController::class, 'exportCashFlowPDF'])->name('cash-flow.export-pdf');
        
        // Equity Changes routes
        Route::get('/equity-changes', [ReportController::class, 'equityChanges'])->name('equity-changes');
        Route::get('/equity-changes/export-pdf', [ReportController::class, 'exportEquityChangesPDF'])->name('equity-changes.export-pdf');
        
        // Journal List
        Route::get('/journal-list', [ReportController::class, 'journalList'])->name('journal-list');
        
        // Sales Report
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        
        // Purchase Report
        Route::get('/purchases', [ReportController::class, 'purchaseReport'])->name('purchases');
        
        // ==========================================
        // BIOLOGICAL ASSETS REPORTS (PSAK 69)
        // ==========================================
        
        // PSAK 69 Reports - Only for companies with PSAK 69 enabled
        // TODO: Add proper middleware
        Route::get('/biological-reconciliation', [BiologicalReportController::class, 'reconciliation'])->name('biological-reconciliation');
        Route::get('/biological-fair-value', [BiologicalReportController::class, 'fairValueChanges'])->name('biological-fair-value');
        Route::get('/biological-production', [BiologicalReportController::class, 'production'])->name('biological-production');
        Route::get('/biological-disclosure', [BiologicalReportController::class, 'disclosure'])->name('biological-disclosure');
    });
});

require __DIR__.'/auth.php';

