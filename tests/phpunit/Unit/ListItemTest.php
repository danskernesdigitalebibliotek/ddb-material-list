<?php

namespace Tests\Unit;

use App\ListItem;
use Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListItemTest extends TestCase
{
    public function testThatItCanCreateAListItemFromACorrectUrlParameter()
    {
        $listItem = ListItem::createFromUrlParameter('999-base:id');
        $this->assertInstanceOf(ListItem::class, $listItem);
        $this->assertSame([
            'agency' => '999',
            'base' => 'base',
            'id' => 'id',
            'fullId' => '999-base:id',
        ], (array) $listItem);
    }

    public function testThatListItemCreateFromUrlParameterValidatesWronglyFormattedParameter()
    {
        $urlParameter = 'john';
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Invalid pid: ' . $urlParameter);
        ListItem::createFromUrlParameter($urlParameter);
    }
}
