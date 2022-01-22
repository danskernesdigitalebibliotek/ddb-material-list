<?php

namespace App\Providers;

use App\ListItem;
use App\Enums\ListType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;

class RouteBindingServiceProvider extends BaseServiceProvider
{
    /**
     * Boot the service provider
     */
    public function boot()
    {
        $binder = $this->binder;

        $binder->bind('type', function ($value): ListType {
            return ListType::createFromUrlParameter($value);
        });

        $binder->bind('item', function ($value): ListItem {
            return ListItem::createFromUrlParameter($value);
        });
    }
}
