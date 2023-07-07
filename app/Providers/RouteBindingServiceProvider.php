<?php

namespace App\Providers;

use App\ListItem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
            try {
                return ListItem::createFromString($value);
            } catch (\InvalidArgumentException $e) {
                throw new UnprocessableEntityHttpException($e->getMessage());
            }
        });
    }
}
