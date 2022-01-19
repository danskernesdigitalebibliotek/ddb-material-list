<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\ListController as DefaultListController;
use Illuminate\Http\Request;

class ListController extends DefaultListController
{
    protected $idColumn = 'collection';
    protected $idFilterName = 'collection_ids';

    public function get(Request $request, string $listId): array
    {
        [$id, $collections] = $this->getItems($request, $listId);

        return [
            'id' => $id,
            'collections' => $collections,
        ];
    }
}
