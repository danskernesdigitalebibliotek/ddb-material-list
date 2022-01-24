<?php

namespace App\Http\Controllers\v2;

use App\ListId;
use Illuminate\Http\Request;
use App\Http\Controllers\ListController as DefaultListController;

class ListController extends DefaultListController
{
    protected $idColumn = 'collection';
    protected $idFilterName = 'collection_ids';

    public function get(Request $request, string $listId): array
    {
        return [
            'id' => $listId,
            'collections' => $this->getItems($request, $listId),
        ];
    }
}
