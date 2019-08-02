<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResourceOwnerGuidMapper
{
    /* @var string */
    private $attributeName;

    public function __construct(string $resourceOwnerAttributeName)
    {
        $this->attributeName = $resourceOwnerAttributeName;
    }

    public function handle(Request $request, Closure $next, $guard = null)
    {
        /* @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $resourceOwner */
        $resourceOwner = $request->attributes->get($this->attributeName);
        $request->setUserResolver(function () use ($resourceOwner) {
            return ($resourceOwner) ? $resourceOwner->getId() : null;
        });
        return $next($request);
    }
}
