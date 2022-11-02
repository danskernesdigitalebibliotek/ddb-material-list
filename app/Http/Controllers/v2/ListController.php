<?php

namespace App\Http\Controllers\v2;

use App\ListItem;
use Illuminate\Http\Request;
use App\Http\Controllers\v1\ListController as DefaultListController;

class ListController extends DefaultListController
{
    protected $idColumn = 'collection';
    protected $idFilterName = 'collection_ids';

    public function get(Request $request, string $listId): array
    {
        $items = $this->getItems($request, $listId);
        $collectionIds = array_map(function (ListItem  $item) {
            return $item->collectionId();
        }, $items);

        return [
            'id' => $listId,
            'collections' => $collectionIds,
        ];
    }
}
