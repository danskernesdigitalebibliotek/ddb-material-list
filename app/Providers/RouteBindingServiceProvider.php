<?php

namespace App\Providers;

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

        $binder->bind('listId', function ($value): string {
            if ($value != ListItem::DEFAULT_LIST_ID) {
                throw new NotFoundHttpException('No such list');
            }

            return $value;
        });

        $binder->bind('item', function ($value): ListItem {
            return ListItem::createFromString($value);
        });
    }
}
