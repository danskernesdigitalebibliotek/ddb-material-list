<?php

namespace App\Providers;

use Adgangsplatformen\Middleware\TokenResourceOwnerMapper;
use Adgangsplatformen\Provider\Adgangsplatformen;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use Softonic\Laravel\Middleware\Psr15Bridge\Psr15MiddlewareAdapter;

class AdgangsplatformenServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(
            AbstractProvider::class,
            function () {
                return new Adgangsplatformen([
                    'clientId' => env('APP_ADGANGSPLATFORMEN_CLIENT_ID'),
                    'clientSecret' => env('APP_ADGANGSPLATFORMEN_CLIENT_SECRET')
                ]);
            }
        );

        $this->app->singleton(
            TokenResourceOwnerMapper::class,
            function () {
                return Psr15MiddlewareAdapter::adapt(new TokenResourceOwnerMapper(
                    $this->app->get(AbstractProvider::class),
                    'resourceOwner'
                ));
            }
        );

        // Console handlers and tests making HTTP requests mock around with the
        // request service left and right. This seems to be the only way to
        // ensure that the request has the required user resolver at the right
        // time.
        $this->app->resolving('request', function (Request $request) {
            $request->setUserResolver(function () use ($request) {
                /* @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $resourceOwner */
                $resourceOwner = $request->attributes->get('resourceOwner');
                return ($resourceOwner) ? $resourceOwner->getId() : null;
            });
        });
    }
}
