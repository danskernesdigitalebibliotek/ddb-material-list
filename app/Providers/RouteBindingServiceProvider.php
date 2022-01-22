<?php

namespace App\Providers;

use App\ItemList;
use App\ListItem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;

class RouteBindingServiceProvider extends BaseServiceProvider
{
    /**
     * Boot the service provider
     */
    public function boot()
    {
        $binder = $this->binder;

        $binder->bind('list', function ($value): ItemList {
            return ItemList::createFromUrlParameter($value);
        });

        $binder->bind('item', function ($value): ListItem {
            return ListItem::createFromUrlParameter($value);
        });
    }
}
