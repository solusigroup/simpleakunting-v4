<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes (Landlord/Admin Panel)
|--------------------------------------------------------------------------
|
| These routes are accessible ONLY on the central domain.
| Tenant subdomains are handled by routes/tenant.php.
|
*/

$centralDomains = config('tenancy.central_domains', []);

// Only register central routes if we can determine the host
// Using a closure-based check so tenant subdomains fall through to tenant.php routes
Route::get('/', function () use ($centralDomains) {
    if (!in_array(request()->getHost(), $centralDomains)) {
        return redirect('/login');
    }
    return redirect('/admin');
});

Route::get('/admin', function () use ($centralDomains) {
    if (!in_array(request()->getHost(), $centralDomains)) {
        abort(404);
    }
    $tenants = \App\Models\Tenant::with('domains')->get();
    $centralDomain = env('CENTRAL_DOMAIN', 'simpleakunting4-0.test');
    return view('admin.index', compact('tenants', 'centralDomain'));
});

Route::get('/admin/create', function () use ($centralDomains) {
    if (!in_array(request()->getHost(), $centralDomains)) {
        abort(404);
    }
    return view('admin.create');
});

Route::post('/admin', [AdminController::class, 'store'])
    ->middleware(\App\Http\Middleware\EnsureCentralDomain::class);

Route::delete('/admin/{tenant}', [AdminController::class, 'destroy'])
    ->middleware(\App\Http\Middleware\EnsureCentralDomain::class);
