<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminProfileController;
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

$centralDomains = config('tenancy.central_domains', []);

foreach ($centralDomains as $index => $domain) {
    Route::domain($domain)->group(function () use ($index) {
        $withNames = ($index === 0);

        // Root route — redirect to admin panel on central domain
        Route::get('/', function () {
            return redirect('/admin');
        });

        // ==========================================
        // ADMIN PANEL (Central Domain Only)
        // ==========================================

        // Admin Auth Routes (no auth required)
        Route::prefix('admin')->group(function () use ($withNames) {
            Route::get('/login', [AdminAuthController::class, 'showLogin'])->name($withNames ? 'admin.login' : null);
            Route::post('/login', [AdminAuthController::class, 'login'])->name($withNames ? 'admin.login.submit' : null);
            Route::post('/logout', [AdminAuthController::class, 'logout'])->name($withNames ? 'admin.logout' : null);
        });

        // Admin Protected Routes (requires admin auth)
        Route::middleware([\App\Http\Middleware\EnsureAdminAuth::class])
            ->prefix('admin')
            ->group(function () use ($withNames) {
                Route::get('/', [AdminController::class, 'index'])->name($withNames ? 'admin.index' : null);
                Route::get('/create', [AdminController::class, 'create'])->name($withNames ? 'admin.create' : null);
                Route::post('/', [AdminController::class, 'store'])->name($withNames ? 'admin.store' : null);
                
                // Profile & Settings
                Route::get('/profile', [AdminProfileController::class, 'edit'])->name($withNames ? 'admin.profile.edit' : null);
                Route::put('/profile', [AdminProfileController::class, 'update'])->name($withNames ? 'admin.profile.update' : null);

                Route::get('/{tenant}', [AdminController::class, 'show'])->name($withNames ? 'admin.show' : null)->where('tenant', '[a-z0-9-]+');
                Route::get('/{tenant}/edit', [AdminController::class, 'edit'])->name($withNames ? 'admin.edit' : null)->where('tenant', '[a-z0-9-]+');
                Route::put('/{tenant}', [AdminController::class, 'update'])->name($withNames ? 'admin.update' : null)->where('tenant', '[a-z0-9-]+');
                Route::delete('/{tenant}', [AdminController::class, 'destroy'])->name($withNames ? 'admin.destroy' : null)->where('tenant', '[a-z0-9-]+');
            });

    });
}
