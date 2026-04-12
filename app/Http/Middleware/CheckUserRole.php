<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            // If it contains a dot, treat it as a permission check
            if (str_contains($role, '.')) {
                if ($user->hasPermission($role)) {
                    return $next($request);
                }
            } else {
                // Otherwise treat as a raw role name check
                if ($user->role === $role) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
