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
            // Priority: direct permission check
            if ($user->hasPermission($role)) {
                return $next($request);
            }

            // Role name check (literal)
            // If the user's role name matches exactly
            $roleName = $user->roleRecord()->exists() ? $user->roleRecord->name : $user->role;
            if ($roleName === $role) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
