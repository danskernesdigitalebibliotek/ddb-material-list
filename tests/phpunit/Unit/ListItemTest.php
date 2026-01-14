<?php

namespace Tests\Unit;

use App\ListItem;
use Tests\TestCase;

class ListItemTest extends TestCase
{
    public function testThatItCanCreateAListItemFromACorrectString() : void
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

    public function testThatListItemCreateFromUrlParameterValidatesWronglyFormattedString() : void
    {
        $urlParameter = 'john';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid pid: ' . $urlParameter);
        ListItem::createFromString($urlParameter);
    }

    public function testThatItCanDetectACollectionListItem() : void
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
