<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('admin');

        // Explicitly set the user resolver to use the 'admin' guard.
        // This prevents other middlewares (like throttle) from trying to
        // resolve a user using the default 'web' guard.
        $request->setUserResolver(function () use ($request) {
            return Auth::guard('admin')->user();
        });

        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
