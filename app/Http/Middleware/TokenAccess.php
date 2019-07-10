<?php

namespace App\Http\Middleware;

use Closure;

class TokenAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // Check that a GUID has been set.
        if (empty($request->user())) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }
}
