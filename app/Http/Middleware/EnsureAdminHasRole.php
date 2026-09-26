<?php

namespace App\Http\Middleware;

use App\Enums\AdminRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = array_filter(array_map(
            fn (string $role): ?AdminRole => AdminRole::tryFrom($role),
            $roles,
        ));

        abort_unless($request->user()?->hasAdminRole(...$allowedRoles), 403);

        return $next($request);
    }
}
