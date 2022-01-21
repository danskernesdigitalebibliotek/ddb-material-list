<?php

namespace App\Http\Middleware;

use Exception;
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

        $request->setRouteResolver(function () use ($version, $route) {

            return array_map(function ($routeComponent) use ($version) {
                // Only handle routes that has a controller defined.
                if ($uses = $routeComponent['uses'] ?? null) {
                    if (preg_match('/^([^@]+)@(.*)$/', $uses, $m)) {
                        list(, $classFrom, $method) = $m;

                        // Squeeze in a \v[version]\ before the controller class name.
                        $replacement = sprintf('$1v%d\\\\$2', $version);
                        $classTo = preg_replace('/(.*\\\\)([\w]+)$/', $replacement, $classFrom);

                        // If we have a controller candidate use that.
                        if (method_exists($classTo, $method)) {
                            $routeComponent['uses'] = "$classTo@$method";
                        }
                    }
                }

                return $routeComponent;
            }, $route);
        });

        return $next($request);
    }
}
