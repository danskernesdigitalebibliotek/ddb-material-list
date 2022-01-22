<?php

namespace App\Http\Controllers\v2;

use App\ItemList;
use Illuminate\Http\Request;
use App\Http\Controllers\ListController as DefaultListController;

class ListController extends DefaultListController
{
    protected $idColumn = 'collection';
    protected $idFilterName = 'collection_ids';

    public function get(Request $request, ItemList $list): array
    {
        return [
            'id' => $list->id,
            'collections' => $this->getItems($request, $list),
        ];
    }
}
