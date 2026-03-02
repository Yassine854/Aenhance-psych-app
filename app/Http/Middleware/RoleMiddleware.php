<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $normalizedUserRole = strtoupper(trim((string) $user->role));
        $normalizedAllowedRoles = array_map(
            static fn ($role) => strtoupper(trim((string) $role)),
            $roles
        );

        if (! in_array($normalizedUserRole, $normalizedAllowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }

}
