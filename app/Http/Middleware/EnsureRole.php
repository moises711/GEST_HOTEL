<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        $user = $request->user();
        $rolesArr = explode('|', $roles);

        if (!$user) {
            abort(403);
        }

        $isSuperadmin = (bool) ($user->is_superadmin ?? false)
            || (($user->type ?? null) === 'superadmin')
            || (method_exists($user, 'hasRole') && $user->hasRole('superadmin'));

        if ($isSuperadmin) {
            return $next($request);
        }

        if (method_exists($user, 'hasRole')) {
            if (!$user->hasRole($rolesArr)) {
                abort(403);
            }
        } else {
            // Fallback to checking type/is_superadmin
            if (in_array('superadmin', $rolesArr) && $user->is_superadmin) {
                return $next($request);
            }
            if (in_array($user->type, $rolesArr)) {
                return $next($request);
            }
            abort(403);
        }

        return $next($request);
    }
}
