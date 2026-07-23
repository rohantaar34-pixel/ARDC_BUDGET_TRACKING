<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasModuleAccess
{
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasAnyModuleAccess($modules)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
