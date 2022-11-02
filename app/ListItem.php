<?php

namespace App;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListItem
{
    const DEFAULT_LIST_ID = 'default';

    public $isCollection;
    public $agency;
    public $base;
    public $id;
    public $fullId;

    public static function createFromString(string $parameter): self
    {
        $itemList = new static();

        if (!preg_match('/(work-of:)?(\d+)-(\w+):(\w+)/', urldecode($parameter), $matches)) {
            throw new UnprocessableEntityHttpException('Invalid pid: ' . $parameter);
        }

        [$itemList->fullId, $workOf, $itemList->agency, $itemList->base, $itemList->id] = $matches;
        // A parameter containing the work-of: prefix identifies a collection.
        $itemList->isCollection = (bool) $workOf;

        return $itemList;
    }

    public function collectionId() : string
    {
        return ($this->isCollection) ? $this->fullId : 'work-of:' . $this->fullId;
    }

    public function materialId() : string
    {
        return ($this->isCollection) ? sprintf('%s-%s:%s', $this->agency, $this->base, $this->id) : $this->fullId;
    }
}
