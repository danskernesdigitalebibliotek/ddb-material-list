<?php

namespace Tests\Unit;

use App\ListItem;
use Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListItemTest extends TestCase
{
    public function testThatItCanCreateAListItemFromACorrectString()
    {
        $listItem = ListItem::createFromString('999-base:id');
        $this->assertInstanceOf(ListItem::class, $listItem);
        $this->assertSame([
            'isCollection' => false,
            'agency' => '999',
            'base' => 'base',
            'id' => 'id',
            'fullId' => '999-base:id',
        ], (array) $listItem);
    }

    public function testThatListItemCreateFromUrlParameterValidatesWronglyFormattedString()
    {
        $urlParameter = 'john';
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Invalid pid: ' . $urlParameter);
        ListItem::createFromString($urlParameter);
    }

    public function testThatItCanDetectACollectionListItem()
    {
        $listItem = ListItem::createFromString('work-of:999-base:id');
        $this->assertInstanceOf(ListItem::class, $listItem);
        $this->assertSame([
            'isCollection' => true,
            'agency' => '999',
            'base' => 'base',
            'id' => 'id',
            'fullId' => 'work-of:999-base:id',
        ], (array) $listItem);
    }

}
