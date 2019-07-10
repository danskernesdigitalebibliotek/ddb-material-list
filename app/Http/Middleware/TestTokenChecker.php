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
        list(, $token) = explode(' ', $request->headers->get('Authorization'));

        // We just use the token as GUID.
        $request->setUserResolver(function () use ($token) {
            return $token;
        });

        return $next($request);
    }
}
