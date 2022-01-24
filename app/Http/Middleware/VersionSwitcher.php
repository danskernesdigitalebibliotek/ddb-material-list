<?php

namespace App\Http\Middleware;

use App\Exceptions\AcceptHeaderWrongFormatException;

/**
 * Router middleware that switches the controller registered to current route
 * depending on "Accept-Version" header.
 */
class VersionSwitcher
{
    public function handle($request, $next)
    {
        // If a version has been specified in the header validate it.
        if ($headerVersion = $request->header('Accept-Version')) {
            if (!is_string($headerVersion) || !intval($headerVersion)) {
                throw new AcceptHeaderWrongFormatException('The Accept-Version header should be an integer as a string');
            }
        }

        // If no version has been specified either in header or config do nothing.
        if (!$version = $headerVersion ?? config(('api.version'))) {
            return $next($request);
        }

        $route = $request->route();

        foreach ($route as $routeComponent) {
            // Only handle routes that has a controller defined.
            if ($uses = $routeComponent['uses'] ?? null) {
                [$controllerFrom, $method] = explode('@', $uses);
                $replacement = sprintf('$1v%d\\\\$2', $version);
                $controllerTo = preg_replace('/(.*\\\\)([\w]+)$/', $replacement, $controllerFrom);
                if (method_exists($controllerTo, $method)) {
                    app()->alias($controllerTo, $controllerFrom);
                }
            }
        }

        return $next($request);
    }
}
