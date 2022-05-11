<?php

namespace App\Http\Middleware;

use Illuminate\Support\Str;
use App\Exceptions\AcceptHeaderWrongFormatException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Router middleware that switches the controller registered to current route
 * depending on "Accept-Version" header.
 *
 * In order to make it work you also need to specify the defined routes like this:
 *     $router->get('/some/path', 'v%version%\SomeController@someMethod');
 *
 * If the controller defined is not resolved or does not exist
 * a NotFoundHttpException (404) is thrown.
 */
class VersionSwitcher
{
    public function handle($request, $next)
    {
        // If a version has been specified in the header validate it.
        if ($headerVersion = $request->header('Accept-Version')) {
            if (!is_string($headerVersion) || !intval($headerVersion)) {
                throw new AcceptHeaderWrongFormatException(
                    'The Accept-Version header should be an integer as a string'
                );
            }
        }

        // If no version has been specified either in header or config do nothing.
        if (!$version = $headerVersion ?? config('api.version')) {
            return $next($request);
        }

        $route = $request->route();

        foreach ($route as $routeComponent) {
            // Only handle routes that has a controller defined.
            if ($uses = $routeComponent['uses'] ?? null) {
                [$controllerFrom,] = explode('@', $uses);
                $controllerTo = $this->versionizeControllerPath($controllerFrom, $version);

                if (!class_exists($controllerTo)) {
                    throw new NotFoundHttpException();
                }

                app()->alias($controllerTo, $controllerFrom);
            }
        }

        return $next($request);
    }

    protected function versionizeControllerPath(string $path, string $version)
    {
        if (!Str::contains($path, '%version%')) {
            return $path;
        }

        return Str::replace('%version%', $version, $path);
    }
}
