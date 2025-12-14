<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
    
    // ==========================================
    // MASTER DATA
    // ==========================================
    
    // Chart of Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    
    // Contacts (Customers/Suppliers)
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('contacts.show');
    
    // Business Units (BUMDesa only)
    Route::get('/units', [BusinessUnitController::class, 'index'])->name('units.index');
    Route::post('/units', [BusinessUnitController::class, 'store'])->name('units.store');
    Route::put('/units/{id}', [BusinessUnitController::class, 'update'])->name('units.update');
    
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
    
    // Cash & Bank
    Route::post('/cash/spend', [CashController::class, 'spend'])->name('cash.spend');
    Route::post('/cash/receive', [CashController::class, 'receive'])->name('cash.receive');
    
    // Journal (Manual Entry)
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::post('/journals/manual', [JournalController::class, 'storeManual'])->name('journals.manual');
    Route::get('/journals/{id}', [JournalController::class, 'show'])->name('journals.show');
    
    // ==========================================
    // REPORTS
    // ==========================================
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/ledger/{account_id}', [ReportController::class, 'ledger'])->name('ledger');
    });
});

require __DIR__.'/auth.php';

