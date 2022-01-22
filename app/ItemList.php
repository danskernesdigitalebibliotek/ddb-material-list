<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemList
{
    const DEFAULT_LIST_NAME = 'default';
    public $id;

    public static function createFromUrlParameter(string $name): self
    {
        $validator = self::validateId($name);
        if ($validator->fails()) {
            throw new NotFoundHttpException('No such list');
        }

        $itemList = new static();
        $itemList->id = $name;

        return $itemList;
    }

    public static function validateId(string $id): ValidationValidator
    {
        $validator = Validator::make(
            ['listId' => $id],
            ['listId' => sprintf('in:%s', self::DEFAULT_LIST_NAME)]
        );
        return $validator;
    }
}
