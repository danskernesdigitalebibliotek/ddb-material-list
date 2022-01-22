<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ListType extends Enum
{
    const DEFAULT = 'default';

    public static function createFromUrlParameter(string $parameter): self
    {
        if (!$listType = self::coerce($parameter)) {
            throw new NotFoundHttpException('No such list');
        }

        return $listType;
    }
}
