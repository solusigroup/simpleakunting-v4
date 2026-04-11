<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes (Landlord/Admin Panel)
|--------------------------------------------------------------------------
|
| These routes are accessible ONLY on the central domain.
| Tenant subdomains are handled by routes/tenant.php.
| The accounting app routes (dashboard, journals, reports, etc.) are
| defined ONLY in tenant.php — they should NOT be duplicated here.
|
*/

foreach (config('tenancy.central_domains', []) as $domain) {
    Route::domain($domain)->group(function () {

        // Root route — redirect to admin panel on central domain
        Route::get('/', function () {
            return redirect('/admin');
        });

        // ==========================================
        // ADMIN PANEL (Central Domain Only)
        // ==========================================

        // Admin Auth Routes (no auth required)
        Route::prefix('admin')->group(function () {
            Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
            Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
            Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        });

        // Admin Protected Routes (requires admin auth)
        Route::middleware([\App\Http\Middleware\EnsureAdminAuth::class])
            ->prefix('admin')
            ->group(function () {
                Route::get('/', [AdminController::class, 'index'])->name('admin.index');
                Route::get('/create', [AdminController::class, 'create'])->name('admin.create');
                Route::post('/', [AdminController::class, 'store'])->name('admin.store');
                Route::get('/{tenant}', [AdminController::class, 'show'])->name('admin.show');
                Route::get('/{tenant}/edit', [AdminController::class, 'edit'])->name('admin.edit');
                Route::put('/{tenant}', [AdminController::class, 'update'])->name('admin.update');
                Route::delete('/{tenant}', [AdminController::class, 'destroy'])->name('admin.destroy');
            });

    }); // end Route::domain($domain)
} // end foreach central_domains
