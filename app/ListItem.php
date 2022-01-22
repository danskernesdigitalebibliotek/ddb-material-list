<?php

namespace App;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListItem
{
    public $agency;
    public $base;
    public $id;
    public $fullId;

    public static function createFromUrlParameter(string $parameter): self
    {
        $itemList = new static();

        if (!preg_match('/(\d+)-(\w+):(\w+)/', urldecode($parameter), $matches)) {
            throw new UnprocessableEntityHttpException('Invalid pid: ' . $parameter);
        }

        $itemList->fullId = urldecode($parameter);
        [, $itemList->agency, $itemList->base, $itemList->id] = $matches;

        return $itemList;
    }
}
