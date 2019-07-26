<?php

namespace App\Http\Middleware;

use Closure;

class TestTokenChecker
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
        $parts = explode(' ', $request->headers->get('Authorization', '', true));
        $token = isset($parts[1]) ? $parts[1] : '';

        // We just use the token as GUID.
        $request->setUserResolver(function () use ($token) {
            return $token;
        });

        return $next($request);
    }
}
